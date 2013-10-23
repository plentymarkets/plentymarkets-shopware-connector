<?php

require_once PY_COMPONENTS . 'Utils/DataIntegrity/PlentymarketsDataIntegrityCheckInterface.php';


class PlentymarketsDataIntegrityCheckItemMainDetail implements PlentymarketsDataIntegrityCheckInterface
{

	public function getName()
	{
		return 'ItemMainDetail';
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
