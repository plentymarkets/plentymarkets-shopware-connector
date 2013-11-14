<?php

class PlentymarketsImportEntityItemCategory
{
	/**
	 *
	 * @var PlentySoapObject_GetItemCategoryCatalogBase
	 */
	protected $Category;

	/**
	 *
	 * @param PlentySoapObject_GetItemCategoryCatalogBase $Category
	 */
	public function __construct($Category)
	{
		$this->Category = $Category;
	}

	/**
	 *
	 */
	public function import()
	{
		$match = Shopware()->Db()->fetchRow('
			SELECT
					plentyPath,
					shopwareId
				FROM plenty_category
				WHERE plentyId = '. (integer) $this->Category->CategoryID .'
				ORDER BY size ASC
				LIMIT 1
		');

		// If there is not match, the categoty ain't used in shopware
		if (!$match)
		{
			return PyLog()->message('Sync:Item:Attribute', 'Skipping the category »' . $this->Category->Name . '« (unused)');
		}

		// Helper
		$path = explode(';', $match['plentyPath']);
		$hit = false;

		// Get the corresponding shopware leaf
		$Category = Shopware()->Models()->find('Shopware\Models\Category\Category', $match['shopwareId']);
		$Category instanceof Shopware\Models\Category\Category;

		// If the shopware categoty wasn't found, something is terribly wrong
		if (!$Category)
		{
			return PyLog()->message('Sync:Item:Attribute', 'Skipping the category »' . $this->Category->Name . '« (not found)');
		}

		// Walk through the plentymarkets path until the right one is found
		while ($path)
		{
			if (array_pop($path) == $this->Category->CategoryID)
			{
				$hit = true;
				break;
			}

			// If this one is not the correct on, get the next higher category
			$Category = $Category->getParent();
		}

		// If no shopware categoty was found, again something is terribly wrong
		if (!$hit)
		{
			return PyLog()->message('Sync:Item:Attribute', 'Skipping the category »' . $this->Category->Name . '« (none found)');
		}

		// Update the category only if the name's changed
		if ($Category->getName() != $this->Category->Name)
		{
			PyLog()->message('Sync:Item:Attribute', 'Updating the category »' . $this->Category->Name . '«');
			$Category->setName($this->Category->Name);

			Shopware()->Models()->persist($Category);
			Shopware()->Models()->flush();
		}
	}
}
