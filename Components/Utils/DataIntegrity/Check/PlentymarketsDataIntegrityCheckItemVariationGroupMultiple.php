<?php

require_once PY_COMPONENTS . 'Utils/DataIntegrity/PlentymarketsDataIntegrityCheckInterface.php';

class PlentymarketsDataIntegrityCheckItemVariationGroupMultiple implements PlentymarketsDataIntegrityCheckInterface
{

	public function getName()
	{
		return 'ItemVariationGroupMultiple';
	}

	public function isValid()
	{
		return count($this->getInvalidData(0, 1)) == 0;
	}

	public function getInvalidData($start, $offset)
	{
		return Shopware()->Db()->query('
			SELECT SQL_CALC_FOUND_ROWS a.name, ad.ordernumber, article_id detailsId, count(*) - 1 diff, co.group_id groupId, a.id itemId, co.name `option`, cg.name `group`
				FROM s_article_configurator_option_relations  cor
				LEFT JOIN s_article_configurator_options co ON co.id = cor.option_id
				LEFT JOIN s_article_configurator_groups cg ON cg.id = co.group_id
				LEFT JOIN s_articles_details ad ON ad.id = cor.article_id
				LEFT JOIN s_articles a ON a.id = ad.articleID
				GROUP BY article_id, co.group_id
				HAVING count(*) > 1
				ORDER BY ad.ordernumber DESC, article_id
				LIMIT ' . $start . ', ' . $offset . '
		')->fetchAll();
	}


	public function getFields()
	{
		return array(
			array(
				'name' => 'name',
				'description' => 'name',
				'type' => 'string'
			),
			array(
				'name' => 'ordernumber',
				'description' => 'ordernumber',
				'type' => 'string'
			),
			array(
				'name' => 'detailsId',
				'description' => 'detailsId',
				'type' => 'int'
			),
			array(
				'name' => 'diff',
				'description' => 'diff',
				'type' => 'int'
			),
			array(
				'name' => 'groupId',
				'description' => 'groupId',
				'type' => 'int'
			),
			array(
				'name' => 'itemId',
				'description' => 'itemId',
				'type' => 'int'
			),
			array(
				'name' => 'option',
				'description' => 'option',
				'type' => 'string'
			),
			array(
				'name' => 'group',
				'description' => 'group',
				'type' => 'string'
			),
		);
	}


	public function getTotal()
	{
		return Shopware()->Db()->query('
			SELECT FOUND_ROWS()
		')->fetchColumn(0);
	}
}
