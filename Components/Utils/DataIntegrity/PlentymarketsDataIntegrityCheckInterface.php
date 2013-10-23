<?php

interface PlentymarketsDataIntegrityCheckInterface
{
	public function getName();
	public function getInvalidData($start, $offset);
	public function getTotal();
	public function getFields();

	public function isValid();

}
