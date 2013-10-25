<?php

require_once PY_COMPONENTS . 'Import/PlentymarketsImportItemHelper.php';
require_once PY_COMPONENTS . 'Utils/DataIntegrity/PlentymarketsDataIntegrityCheckInterface.php';


class PlentymarketsDataIntegrityCheckItemOrphaned implements PlentymarketsDataIntegrityCheckInterface
{

	public function getName()
	{
		return 'ItemOrphaned';
	}

	public function isValid()
	{
		return count($this->getInvalidData(0, 1)) == 0;
	}

	public function getInvalidData($start, $offset)
	{
		return Shopware()->Db()->query('
			SELECT
					SQL_CALC_FOUND_ROWS id detailId, articleID itemId, ordernumber, additionaltext
					FROM s_articles_details
					WHERE articleID NOT IN (SELECT id FROM s_articles)
				ORDER BY ordernumber, articleID
				LIMIT ' . $start . ', ' . $offset . '
		')->fetchAll();
	}

	public function deleteInvalidData($start, $offset)
	{
		foreach ($this->getInvalidData($start, $offset) as $data)
		{
			try
			{
				$Item = Shopware()->Models()->find('\Shopware\Models\Article\Detail', $data['detailId']);
				Shopware()->Models()->remove($Item);
			}
			catch (Exception $E)
			{
				PlentymarketsLogger::getInstance()->error(__LINE__ . __METHOD__, $E->getMessage());
			}
		}
		Shopware()->Models()->flush();
	}

	public function getFields()
	{
		return array(
			array(
				'name' => 'itemId',
				'description' => 'Artikel ID',
				'type' => 'int'
			),
			array(
				'name' => 'detailId',
				'description' => 'detailId',
				'type' => 'int'
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
		);
	}

	public function getTotal()
	{
		return Shopware()->Db()->query('
			SELECT FOUND_ROWS()
		')->fetchColumn(0);
	}
}
