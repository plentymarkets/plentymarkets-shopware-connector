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
			SELECT SQL_CALC_FOUND_ROWS a.name, ad.ordernumber, article_id detailsId, count(*) - 1 diff, co.group_id groupId, a.id itemId, GROUP_CONCAT(cor.option_id SEPARATOR "|") optionIds, GROUP_CONCAT(co.name SEPARATOR ", ") `option`, cg.name `group`
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

	public function deleteInvalidData($start, $offset)
	{
		foreach ($this->getInvalidData($start, $offset) as $data)
		{
			try
			{
				$Item = Shopware()->Models()->find('\Shopware\Models\Article\Detail', $data['detailsId']);
				Shopware()->Models()->remove($Item);
			}
			catch (Exception $E)
			{
				PlentymarketsLogger::getInstance()->error(__LINE__ . __METHOD__, $E->getMessage());
				foreach (explode('|', $data['optionIds']) as $optionId)
				{
					Shopware()->Db()->query('
						DELETE FROM s_article_configurator_option_relations
							WHERE
								article_id = ? AND
								option_id = ?
							LIMIT 1
					', array(
						$data['detailsId'],
						$optionId
					));
				}
			}
		}
		Shopware()->Models()->flush();
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
				'name' => 'option',
				'description' => 'option',
				'type' => 'string'
			),
			array(
				'name' => 'group',
				'description' => 'group',
				'type' => 'string'
			),
			array(
				'name' => 'detailsId',
				'description' => 'detailsId',
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
		);
	}


	public function getTotal()
	{
		return Shopware()->Db()->query('
			SELECT FOUND_ROWS()
		')->fetchColumn(0);
	}
}
