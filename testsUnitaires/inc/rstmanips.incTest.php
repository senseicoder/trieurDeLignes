<?php

abstract class CAction
{
        abstract function Run(array & $aData);
}

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

class CAChargerLignes extends CAction
{
	static function Make()
	{
		$sClass = __CLASS__;
		return new $sClass();
	}

	function Run(array & $aData)
	{
		foreach(file($aData['filename']) as $sLine) {
			$aData['lines'][] = array('raw' => $sLine);
		}
	}
}

class CACategoriserLignes extends CAction
{
	static function Make()
	{
		$sClass = __CLASS__;
		return new $sClass();
	}

	static function Analyser($sLine)
	{
		if( ! empty($sLine)) {
			$c = $sLine[0];
			if($c !== ' ' && preg_match('/^[' . $c . ']{3,}[ \t]*$/', $sLine)) {
				return CRSTLigne::SUBTITRE;
			}
		}

		if(preg_match('/^[ \t]*[\n]?$/', $sLine)) return CRSTLigne::VIDE; 
		if(preg_match('/^([ ]*)[*][ ]/', $sLine)) return CRSTLigne::PUCE; 

		return CRSTLigne::TEXTE;
	}

	function Run(array & $aData)
	{
		foreach($aData['lines'] as & $aLine) {
			$aLine['nature'] = self::Analyser($aLine['raw']);
		}
	}
}

class CAAnalyseStructure extends CAction
{
	static function Make()
	{
		$sClass = __CLASS__;
		return new $sClass();
	}

	function Run(array & $aData)
	{
		$aData['levels'] = array();

		foreach($aData['lines'] as $id => & $aLine) {
			switch($aLine['nature']) {
				case CRSTLigne::TITRE : 
					break;

				case CRSTLigne::TEXTE : 
					break;

				case CRSTLigne::VIDE :
					break; 

				case CRSTLigne::PUCE : 
					break;

				case CRSTLigne::SUBTITRE : 
					$aLine['char'] = $aLine['raw'][0];

					$aData['lines'][$id - 1]['nature'] = CRSTLigne::TITRE;
					if(! in_array($aLine['char'], $aData['levels'])) {
						$aData['levels'][] = $aLine['char'];
					}

					$aData['lines'][$id - 1]['titrelevel'] = array_search($aLine['char'], $aData['levels']);
					break;

				default: throw new Exception('ligne non traitée : ' . $aLine['raw']);
			}
		}
	}
}

class CAExtraireStructure extends CAction
{
	static function Make()
	{
		$sClass = __CLASS__;
		return new $sClass();
	}

	function Run(array & $aData)
	{
		$aData['structure'] = array();
		$aParent = & $aData['structure'];
		$iLevel = 0;

		foreach($aData['lines'] as $id => & $aLine) {
			switch($aLine['nature']) {
				case CRSTLigne::TITRE : 
					$aParent['children'][] = $aLine;
					printf('%s, %d<br>', $aLine['raw'], $aLine['titrelevel']);
					break;

				case CRSTLigne::SUBTITRE : break;
				case CRSTLigne::VIDE : break;
				default: throw new Exception('ligne non traitée : ' . $aLine['raw']);
			}
		}
	}
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

class testCACategoriserLignes extends PHPUnit_Framework_TestCase
{
	function testAnalyseSubTitre()
	{
		$this->AssertEquals(CRSTLigne::SUBTITRE, CACategoriserLignes::Analyser('####'));
		$this->AssertEquals(CRSTLigne::SUBTITRE, CACategoriserLignes::Analyser('==='));
		$this->AssertEquals(CRSTLigne::SUBTITRE, CACategoriserLignes::Analyser('------'));
		$this->AssertEquals(CRSTLigne::SUBTITRE, CACategoriserLignes::Analyser('~~~~~~~~~~~'));
		$this->AssertEquals(CRSTLigne::SUBTITRE, CACategoriserLignes::Analyser('------  '));

		$this->AssertNotEquals(CRSTLigne::SUBTITRE, CACategoriserLignes::Analyser(' ------'));
		$this->AssertNotEquals(CRSTLigne::SUBTITRE, CACategoriserLignes::Analyser('--'));
	}

	function testAnalysePuce()
	{
		$this->AssertEquals(CRSTLigne::PUCE, CACategoriserLignes::Analyser('* puce'));
		$this->AssertEquals(CRSTLigne::PUCE, CACategoriserLignes::Analyser('  * puce'));
		$this->AssertEquals(CRSTLigne::PUCE, CACategoriserLignes::Analyser('    * puce'));
		$this->AssertEquals(CRSTLigne::PUCE, CACategoriserLignes::Analyser(' * puce'));

		$this->AssertNotEquals(CRSTLigne::PUCE, CACategoriserLignes::Analyser('*puce'));
	}

	function testVide()
	{
		$this->AssertEquals(CRSTLigne::VIDE, CACategoriserLignes::Analyser(''));	
		$this->AssertEquals(CRSTLigne::VIDE, CACategoriserLignes::Analyser("\n"));	
		$this->AssertEquals(CRSTLigne::VIDE, CACategoriserLignes::Analyser('	'));	
		$this->AssertEquals(CRSTLigne::VIDE, CACategoriserLignes::Analyser('   '));	
	}
}

class testCRstLayers extends PHPUnit_Framework_TestCase
{
	function testChargement_Titres()
	{
		$a = CRstLayers::Charger(__DIR__ . '/rst/titre.rst');

		var_dump($a['structure']);

		$this->AssertEquals('#', $a['levels'][0]);
		$this->AssertEquals('=', $a['levels'][1]);
		$this->AssertEquals('-', $a['levels'][2]);

		$i = 0;
		$this->AssertEquals(CRSTLigne::TITRE, $a['lines'][$i]['nature']);		
		$this->AssertEquals(0, $a['lines'][$i]['titrelevel']);
		$i++;
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->AssertEquals(CRSTLigne::TITRE, $a['lines'][$i]['nature']);		
		$this->AssertEquals(1, $a['lines'][$i]['titrelevel']);
		$i++;
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->AssertEquals(CRSTLigne::TITRE, $a['lines'][$i]['nature']);		
		$this->AssertEquals(2, $a['lines'][$i]['titrelevel']);
		$i++;
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->AssertEquals(CRSTLigne::TITRE, $a['lines'][$i]['nature']);		
		$this->AssertEquals(2, $a['lines'][$i]['titrelevel']);
		$i++;
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->AssertEquals(CRSTLigne::TITRE, $a['lines'][$i]['nature']);		
		$this->AssertEquals(1, $a['lines'][$i]['titrelevel']);
		$i++;
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->AssertEquals(CRSTLigne::TITRE, $a['lines'][$i]['nature']);		
		$this->AssertEquals(2, $a['lines'][$i]['titrelevel']);
		$i++;
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->AssertEquals(CRSTLigne::TITRE, $a['lines'][$i]['nature']);		
		$this->AssertEquals(2, $a['lines'][$i]['titrelevel']);
		$i++;
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->AssertEquals(CRSTLigne::TITRE, $a['lines'][$i]['nature']);		
		$this->AssertEquals(2, $a['lines'][$i]['titrelevel']);
		$i++;
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);

		$this->AssertEquals(count($a['lines']), $i);
	}
}