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
		$aData['titles'] = array();

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
					$aData['titles'][] = $id - 1;
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
		$idPrev = NULL;
		$iLevel = 0;
		$aData['level0'] = array();

		foreach($aData['titles'] as $idTitre => $idLine) {
			$aLine = $aData['lines'][$idLine];

			if($aLine['titrelevel'] > $iLevel) $idPrev = $aData['titles'][$idTitre - 1];
			elseif($aLine['titrelevel'] < $iLevel) {
				if($idPrev !== NULL) $idPrev = $aData['lines'][$idPrev]['parent'];
			}
			$iLevel = $aLine['titrelevel'];

			if($idPrev === NULL) $aData['level0'][] = $idLine;
			else $aData['lines'][$idPrev]['children'][] = $idLine;

			$aData['lines'][$idLine]['parent'] = $idPrev;
			$aData['lines'][$idLine]['children'] = array();
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
	private $_aDocument;

	function assertLigneTitre($i, $iLevel, $iParent, array $aChildren = array())
	{
		$this->AssertEquals(CRSTLigne::TITRE, $this->_aDocument['lines'][$i]['nature']);		
		$this->AssertEquals($iLevel, $this->_aDocument['lines'][$i]['titrelevel']);
		$this->AssertEquals($iParent, $this->_aDocument['lines'][$i]['parent']);
		$this->AssertEquals($aChildren, $this->_aDocument['lines'][$i]['children']);
	}

	function testChargement_Titres()
	{
		$a = CRstLayers::Charger(__DIR__ . '/rst/titre.rst');
		$this->_aDocument = $a;

		$this->AssertEquals('#', $a['levels'][0]);
		$this->AssertEquals('=', $a['levels'][1]);
		$this->AssertEquals('-', $a['levels'][2]);

		$this->AssertEquals(array(0), $a['level0']);

		$i = 0;
		$this->assertLigneTitre($i++, 0, NULL, array(3, 12));
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 1, 0, array(6, 9));
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 2, 3, array());
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 2, 3, array());
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 1, 0, array(15, 18, 21));
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 2, 12, array());
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 2, 12, array());
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);		
		$this->AssertEquals(CRSTLigne::VIDE, $a['lines'][$i++]['nature']);		

		$this->assertLigneTitre($i++, 2, 12, array());
		$this->AssertEquals(CRSTLigne::SUBTITRE, $a['lines'][$i++]['nature']);

		$this->AssertEquals(count($a['lines']), $i);
	}
}