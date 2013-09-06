<?php

class PlentymarketsImportItemHelper
{

	/**
	 *
	 * @var integer
	 */
	protected static $numbersCreated = 0;
	
	/**
	 *
	 * @param integer $number
	 * @return boolean
	 */
	public static function itemNumberExists($number)
	{
		$detail = Shopware()->Models()
		->getRepository('Shopware\Models\Article\Detail')
		->findOneBy(array(
			'number' => $number
		));
	
		return !empty($detail);
	}
	
	/**
	 *
	 * @return string
	 */
	public static function getItemNumber()
	{
		$prefix = Shopware()->Config()->backendAutoOrderNumberPrefix;
	
		$sql = "SELECT number FROM s_order_number WHERE name = 'articleordernumber'";
		$number = Shopware()->Db()->fetchOne($sql);
		$number += self::$numbersCreated;
	
		do
		{
			++$number;
			++self::$numbersCreated;
	
			$sql = "SELECT id FROM s_articles_details WHERE ordernumber LIKE ?";
			$hit = Shopware()->Db()->fetchOne($sql, $prefix . $number);
		}
		while ($hit);
	
		Shopware()->Db()->query("UPDATE s_order_number SET number = ? WHERE name = 'articleordernumber'", array(
		$number
		));
	
		return $prefix . $number;
	}
	
	/**
	 *
	 * @param string $number
	 * @return string
	 */
	public static function getUsableNumber($number)
	{
		if (!empty($number) && !self::itemNumberExists($number))
		{
			return $number;
		}
		return self::getItemNumber();
	}
}

?>