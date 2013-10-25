<?php
require_once PY_COMPONENTS . 'Utils/DataIntegrity/Check/PlentymarketsDataIntegrityCheckItemMainDetailLost.php';
require_once PY_COMPONENTS . 'Utils/DataIntegrity/Check/PlentymarketsDataIntegrityCheckItemOrphaned.php';
require_once PY_COMPONENTS . 'Utils/DataIntegrity/Check/PlentymarketsDataIntegrityCheckItemVariationGroupMultiple.php';
require_once PY_COMPONENTS . 'Utils/DataIntegrity/Check/PlentymarketsDataIntegrityCheckItemVariationOptionLost.php';

class PlentymarketsDataIntegrityController
{

	/**
	 *
	 * @var PlentymarketsDataIntegrityController
	 */
	protected static $Instance;

	/**
	 *
	 * @var array[PlentymarketsDataIntegrityCheckInterface]
	 */
	protected $Checks = array();

	/**
	 * Adds all the checks to the controller
	 */
	protected function __construct()
	{
		$this->add(new PlentymarketsDataIntegrityCheckItemMainDetailLost());
		$this->add(new PlentymarketsDataIntegrityCheckItemOrphaned());
		$this->add(new PlentymarketsDataIntegrityCheckItemVariationGroupMultiple());
		$this->add(new PlentymarketsDataIntegrityCheckItemVariationOptionLost());
	}

	/**
	 *
	 * @return PlentymarketsDataIntegrityController
	 */
	public static function getInstance()
	{
		if (!self::$Instance instanceof self)
		{
			self::$Instance = new self();
		}
		return self::$Instance;
	}

	/**
	 *
	 * @param PlentymarketsDataIntegrityCheckInterface $Check
	 */
	public function add(PlentymarketsDataIntegrityCheckInterface $Check)
	{
		$this->Checks[$Check->getName()] = $Check;
	}

	public function getInvalidChecks()
	{
		$names = array();
		foreach ($this->Checks as $Check)
		{
			if (!$Check->isValid())
			{
				$names[] = $Check;
			}
		}
		return $names;
	}

	public function isValid()
	{
		foreach ($this->Checks as $Check)
		{
			if (!$Check->isValid())
			{
				return false;
			}
		}
		return true;
	}

	/**
	 *
	 * @param string $name
	 * @return PlentymarketsDataIntegrityCheckInterface
	 */
	public function getCheck($name)
	{
		return $this->Checks[$name];
	}
}
