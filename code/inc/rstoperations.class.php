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
			$aData['lines'][] = array('raw' => str_replace("\n", '', $sLine));
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
			if(in_array($c, array('=', '#', '~', '-')) && preg_match('/^[' . $c . ']{3,}[ \t]*$/', $sLine)) {
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
					if($idPrevPotentiel === NULL) $aData['level0'][] = $id;
					else $aData['lines'][$idPrevPotentiel]['children'][] = $id;
					break;

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

class CARendreAffichable extends CAction
{
	static function Make()
	{
		$sClass = __CLASS__;
		return new $sClass;
	}

	function MakeLink($s)
	{
		$match_href = '|(https?://([\d\w\.-]+\.[\w\.]{2,6})[^\s\]\[\<\>]*/?)|i';
		$match_hash = '|\B#([\d\w_]+)|i';
		$match_plus = '|\B\+([\d\w_]+)|i';
		$replace_url = '<a href="$1" target="_blank">$1</a>';
		$replace_tag = '<a href="http://url.com/pluslink/$1">$0</a>';		

		$s = preg_replace($match_href, $replace_url, $s);
		$s = preg_replace($match_hash, $replace_tag, $s);
		$s = preg_replace($match_plus, $replace_tag, $s);

		return $s;
	}

	function Run(array & $aData)
	{
		foreach($aData['lines'] as $id => & $aLine) {
			switch($aLine['nature']) {
				case CRSTLigne::TEXTE : 
					$aLine['value'] = self::MakeLink($aLine['raw']);
					break;

				case CRSTLigne::TITRE : 
				case CRSTLigne::VIDE :
				case CRSTLigne::SUBTITRE : 
					$aLine['value'] = $aLine['raw'];
					break;

				case CRSTLigne::PUCE : 
					$aLine['value'] = self::MakeLink(preg_replace('/^[ ]*[*] /', '', $aLine['raw']));
					break;

				default: throw new Exception('ligne non traitée : ' . $aLine['raw']);
			}
		}
	}
}