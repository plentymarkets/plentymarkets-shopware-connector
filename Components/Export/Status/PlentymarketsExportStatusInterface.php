<?php

interface PlentymarketsExportStatusInterface
{
	public function isFinished();
	public function isBlocking();
	public function isOptional();
	
	public function mayAnnounce();
	public function mayReset();
	public function mayErase();
	
	public function needsDependency();
	
	public function getName();
	public function getStatus();
	public function getStart();
	public function getFinished();
	public function getError();
}
