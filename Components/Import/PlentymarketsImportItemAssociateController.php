<?php
/**
 * plentymarkets shopware connector
 * Copyright © 2013 plentymarkets GmbH
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License, supplemented by an additional
 * permission, and of our proprietary license can be found
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "plentymarkets" is a registered trademark of plentymarkets GmbH.
 * "shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, titles and interests in the
 * above trademarks remain entirely with the trademark owners.
 *
 * @copyright  Copyright (c) 2013, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author     Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */

/**
 * Manages the import of item-associated data
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportItemAssociateController
{

	/**
	 * Only one entity will me imported per run
	 *
	 * @var integer
	 */
	const ACTION_DETACHED = 1;

	/**
	 * All entities will be importet within one run
	 *
	 * @var integer
	 */
	const ACTION_CHAINED = 2;

	/**
	 *
	 * @var array
	 */
	protected static $associates = array(
		'Producer',
		'Category',
		'Attribute',
		'Property'
	);

	/**
	 *
	 * @var integer
	 */
	protected $cronJobInterval;

	/**
	 * Runs the item associated import
	 */
	public function run($cronJobInterval)
	{
		//
		$this->cronJobInterval = (integer) $cronJobInterval;

		switch (PyConf()->getItemAssociateImportActionID())
		{
			case self::ACTION_CHAINED:
				$this->runChained();
				break;
			default:
			case self::ACTION_DETACHED:
				$this->runDetached();
				break;
		}
	}

	/**
	 * Imports one entity
	 */
	protected function runDetached()
	{
		PyLog()->message('Sync:Item:Associate', 'Running in detached mode');

		$associates = self::$associates;

		// get the entity of the previous import
		$previousEntity = PyConf()->getImportItemAssociateLastEnity();

		// No entity or the previous entity was the last of the chain
		if (!$previousEntity || $previousEntity == end($associates))
		{
			// start with the first one
			$entity = reset($associates);
		}

		else
		{
			while (($associate = array_shift($associates)) && $associates)
			{
				if ($associate == $previousEntity)
				{
					break;
				}
			}
			$entity = array_shift($associates);
		}

		// Increase the intervall
		$this->cronJobInterval *= count(self::$associates);

		$this->runEntity($entity);
	}

	/**
	 * Imports all entities
	 */
	protected function runChained()
	{
		PyLog()->message('Sync:Item:Associate', 'Running in chained mode');

		foreach (self::$associates as $associate)
		{
			$this->runEntity($associate);
		}
	}

	/**
	 * Runs the import of an expicit entity
	 */
	protected function runEntity($entity)
	{
		$timestamp = time();

		PyConf()->set(sprintf('ImportItem%sLastRunTimestamp', $entity), time());
		PyConf()->set(sprintf('ImportItem%sNextRunTimestamp', $entity), time() + $this->cronJobInterval);

		if (PyStatus()->maySynchronize())
		{
			PyLog()->message('Sync:Item:' . $entity, 'Starting');
			try
			{
				$controller = sprintf('PlentymarketsImportControllerItem%s', $entity);
				require_once PY_COMPONENTS . 'Import/Controller/' . $controller . '.php';

				$Controller = new $controller();
				$Controller->run((integer) PyConf()->get(sprintf('ImportItem%sLastUpdateTimestamp', $entity)));

				PyConf()->set(sprintf('ImportItem%sStatus', $entity), 1);
				PyConf()->set(sprintf('ImportItem%sLastUpdateTimestamp', $entity), $timestamp);
				PyConf()->erase(sprintf('ImportItem%sError', $entity));
			}
			catch (PlentymarketsImportException $E)
			{
				PyConf()->set(sprintf('ImportItem%sStatus', $entity), 2);
				PyConf()->set(sprintf('ImportItem%sError', $entity), $E->getMessage());
			}
			PyLog()->message('Sync:Item:' . $entity, 'Finished');
		}
		else
		{
			PyConf()->set(sprintf('ImportItem%sStatus', $entity), 0);
		}

		PyConf()->setImportItemAssociateLastEnity($entity);
	}
}
