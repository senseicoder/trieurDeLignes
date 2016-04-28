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
		$o->Add(CARendreAffichable::Make());
		#repérer les erreurs de souslignage, de niveau dans les puces, de texte non pucé, etc...
		$o->Run($aData);

		return $o;
	}

	function Ecrire($sFilename)
	{
		$f = fopen($sFilename, 'w');
		foreach($this->_aData['lines'] as $idLine => $aLine) {
			switch($aLine['nature']) {
				case CRSTLigne::PUCE : 
					$sLine = sprintf('%s* %s', str_repeat(' ', $aLine['pucelevel']), $aLine['value']);
					break;
				default : 
					$sLine = $aLine['value'];
			}
			echo $sLine;
			fwrite($f, $sLine . "\n");
		}
		fclose($f);
	}

	function Suppression($idLigne)
	{
		unset($this->_aData['lines'][$idLigne]);
	}

	function GetLastIdChildren($idPere)
	{
		return $idPere + 4; //TODO FAKE
	}

	function Ajout($idPere, $sNature, $sValue)
	{
		#TODO ne gère que les puces
		#TODO pucelevel, level, parent
		#TODO gestion fin de ligne ? 
		$aNew = array(array(
			'raw' => '* ' . $sValue, #TODO FAKE
			'nature' => $sNature, 
			'pucelevel' => $this->_aData['lines'][$idPere]['level'] + 1, 
			'level' => $this->_aData['lines'][$idPere]['level'] + 1, 
			'parent' => $idPere,
			'children' => array(), 
			'value' => $sValue));
		array_splice($this->_aData['lines'], $this->GetLastIdChildren($idPere) + 1, 0, $aNew);	
	}
}