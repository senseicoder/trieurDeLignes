<?php

require_once __DIR__ . '/rstoperations.class.php';

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
	protected $_aData = array();

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
		$this->_aData = $aData;

		foreach($this->_aActions as $oAction) {
			$oAction->Run($this->_aData);
		}
		return $this->_aData;
	}

	function Get()
	{
		return $this->_aData;
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
		$o->Run($aData);

		return $o;
	}

	function Ecrire($sFilename)
	{
		$f = fopen($sFilename, 'w');
		foreach($this->_aData['lines'] as $idLine => $aLine) {
			fwrite($f, $aLine['raw']);
		}
		fclose($f);
	}

	function Suppression($idLigne)
	{
		unset($this->_aData['lines'][$idLigne]);
	}

	function Ajout($idPere, $sNature, $sRaw)
	{
		
	}
}