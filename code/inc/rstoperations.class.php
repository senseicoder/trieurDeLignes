<?php

abstract class CAction
{
	abstract function Run(array & $aData);
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
					$aData['lines'][$id]['pucelevel'] = 0;
					$aData['lines'][$id]['parent'] = NULL;
					$aData['lines'][$id]['children'] = array();
					break;

				case CRSTLigne::SUBTITRE : 
					$aLine['char'] = $aLine['raw'][0];

					$aData['lines'][$id - 1]['nature'] = CRSTLigne::TITRE;
					if( ! in_array($aLine['char'], $aData['levels'])) {
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