<?php

require_once PY_COMPONENTS . 'Utils/DataIntegrity/PlentymarketsDataIntegrityCheckInterface.php';

class PlentymarketsDataIntegrityCheckItemVariationOptionLost implements PlentymarketsDataIntegrityCheckInterface
{

	public function getName()
	{
		return 'ItemVariationOptionLost';
	}

	public function isValid()
	{
		return count($this->getInvalidData(0, 1)) == 0;
	}

	public function getInvalidData($start, $offset)
	{
		// SELECT article_id, option_id FROM s_article_configurator_option_relations  cor
		//WHERE option_id NOT IN (SELECT id FROM s_article_configurator_options);
		return Shopware()->Db()->query('
			SELECT
					SQL_CALC_FOUND_ROWS a.name, ad.ordernumber, ad.additionaltext, article_id detailsId, a.id itemId, option_id optionId
				FROM s_article_configurator_option_relations cor
				LEFT JOIN s_articles_details ad ON ad.id = cor.article_id
				LEFT JOIN s_articles a ON a.id = ad.articleID
				WHERE option_id NOT IN (SELECT id FROM s_article_configurator_options)
				ORDER BY ad.ordernumber DESC, article_id
				LIMIT ' . $start . ', ' . $offset . '
		')->fetchAll();
	}

	public function deleteInvalidData($start, $offset)
	{
		foreach ($this->getInvalidData($start, $offset) as $data)
		{
			// Item detail still available
			if (!empty($data['ordernumber']))
			{
				try
				{
					$Detail = Shopware()->Models()->find('\Shopware\Models\Article\Detail', $data['detailsId']);
					Shopware()->Models()->remove($Detail);
				}
				catch (Exception $E)
				{
				}
			}

			// delete only the relation
			else
			{
				Shopware()->Db()->query('
					DELETE FROM s_article_configurator_option_relations
						WHERE
							article_id = ? AND
							option_id = ?
						LIMIT 1
				', array(
					$data['detailsId'],
					$data['optionId']
				));
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
				'name' => 'additionaltext',
				'description' => 'additionaltext',
				'type' => 'string'
			),
			array(
				'name' => 'detailsId',
				'description' => 'detailsId',
				'type' => 'int'
			),
			array(
				'name' => 'itemId',
				'description' => 'itemId',
				'type' => 'int'
			),
			array(
				'name' => 'optionId',
				'description' => 'optionId',
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
