<?php

class CRSTLigne
{
	const ROOT = 'ROOT';
	const UNKNOWN = 'UNKNOWN';
	const TITRE = 'TITRE';
	const TEXTE = 'TEXTE';
	const PUCE = 'PUCE';
	const SUBTITRE = 'SUBTITRE';
	const CITATION = 'CITATION';
	const VIDE = 'VIDE';
}

class CRstLayers
{
	protected $_aActions = array();

	function __construct()
	{
	}

	function Add(CAction $oAction)
	{
		$this->_aActions[] = $oAction;
	}

	function Run(array $aData = NULL)
	{
		if($aData === NULL) $aData = array();
		foreach($this->_aActions as $oAction) {
			$oAction->Run($aData);
		}
		return $aData;
	}

	static function Charger($sFilename)
	{
		$aData['filename'] = $sFilename;

		$o = new self;
		$o->Add(CAChargerLignes::Make());
		$o->Add(CACategoriserLignes::Make());
		$o->Add(CAAnalyseStructure::Make());
		$o->Add(CAExtraireStructure::Make());
		#repérer les erreurs de souslignage, de niveau dans les puces, de texte non pucé, etc...
		return $o->Run($aData);
	}
}