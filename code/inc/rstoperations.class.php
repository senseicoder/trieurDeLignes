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

	function CalculerPuceLevel($sLine)
	{
		if(preg_match('/^([ ][ ])/', $sLine, $aMatch)) {
			return strlen($aMatch[1]) / 2;
		}
		return 0;
	}

	function Run(array & $aData)
	{
		$aData['levels'] = array();
		$iLevel = 0;

		foreach($aData['lines'] as $id => & $aLine) {
			switch($aLine['nature']) {
				case CRSTLigne::TITRE : 
				case CRSTLigne::TEXTE : 
				case CRSTLigne::VIDE :
					break; 

				case CRSTLigne::PUCE : 
					$aData['lines'][$id]['pucelevel'] = $this->CalculerPuceLevel($aLine['raw']);
					$aData['lines'][$id]['level'] = $iLevel + 1 + $aData['lines'][$id]['pucelevel'];
					$aData['lines'][$id]['parent'] = NULL;
					$aData['lines'][$id]['children'] = array();
					break;

				case CRSTLigne::SUBTITRE : 
					$aLine['char'] = $aLine['raw'][0];

					$aData['lines'][$id - 1]['nature'] = CRSTLigne::TITRE;
					if( ! in_array($aLine['char'], $aData['levels'])) {
						$aData['levels'][] = $aLine['char'];
					}

					$iLevel = array_search($aLine['char'], $aData['levels']);
					$aData['lines'][$id - 1]['level'] = $iLevel;
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
		$idPrev = $idPrevPotentiel = NULL;
		$iLevel = 0;
		$aData['level0'] = array();

		foreach($aData['lines'] as $id => & $aLine) {
			switch($aLine['nature']) {
				case CRSTLigne::TEXTE : 
				case CRSTLigne::VIDE :
				case CRSTLigne::SUBTITRE : 
					break;

				case CRSTLigne::PUCE : 
				case CRSTLigne::TITRE : 
					if($aLine['level'] > $iLevel) $idPrev = $idPrevPotentiel;
					elseif($aLine['level'] < $iLevel) {
						if($idPrev !== NULL) $idPrev = $aData['lines'][$idPrev]['parent'];
					}
					$iLevel = $aLine['level'];

					if($idPrev === NULL) $aData['level0'][] = $id;
					else $aData['lines'][$idPrev]['children'][] = $id;

					$aLine['parent'] = $idPrev;
					$aLine['children'] = array();
					$idPrevPotentiel = $id;
					break;

				default: throw new Exception('ligne non traitée : ' . $aLine['raw']);
			}
		}
	}
}