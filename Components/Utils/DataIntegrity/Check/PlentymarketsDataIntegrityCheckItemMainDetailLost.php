<?php

require_once PY_COMPONENTS . 'Import/PlentymarketsImportItemHelper.php';
require_once PY_COMPONENTS . 'Utils/DataIntegrity/PlentymarketsDataIntegrityCheckInterface.php';


class PlentymarketsDataIntegrityCheckItemMainDetailLost implements PlentymarketsDataIntegrityCheckInterface
{

	public function getName()
	{
		return 'ItemMainDetailLost';
	}

	public function isValid()
	{
		return count($this->getInvalidData(0, 1)) == 0;
	}

	public function getInvalidData($start, $offset)
	{
		return Shopware()->Db()->query('
			SELECT SQL_CALC_FOUND_ROWS a.id itemId, a.name, main_detail_id mainDetailId FROM s_articles a
				WHERE main_detail_id IS NULL OR main_detail_id NOT IN (SELECT id FROM s_articles_details)
				ORDER BY a.id
				LIMIT ' . $start . ', ' . $offset . '
		')->fetchAll();
	}

	public function deleteInvalidData($start, $offset)
	{
		foreach ($this->getInvalidData($start, $offset) as $data)
		{
			try
			{
				$Item = Shopware()->Models()->find('\Shopware\Models\Article\Article', $data['itemId']);
				Shopware()->Models()->remove($Item);
			}
			catch (Exception $E)
			{
				PlentymarketsLogger::getInstance()->error(__LINE__ . __METHOD__, $E->getMessage());
				try
				{
					// Try to delete through the API
					$Resource = Shopware\Components\Api\Manager::getResource('Article');
					$Resource->delete($data['itemId']);
				}
				catch (Exception $E)
				{
					PlentymarketsLogger::getInstance()->error(__LINE__ . __METHOD__, $E->getMessage());
				}
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
				'name' => 'name',
				'description' => 'Bezeichnung',
				'type' => 'string'
			),
			array(
				'name' => 'mainDetailId',
				'description' => 'Detail ID',
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
