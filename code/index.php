<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>accueil - Integralsport.com, le forum du tir à l'arc</title>
	<link rel="stylesheet" type="text/css" href="http://cedricg.ouvaton.org//inc/ecran.css" />
	<script type="text/javascript" src="http://cedricg.ouvaton.org//inc/code.js"></script>
	<meta content="text/html;charset=UTF-8" http-equiv="content-type" />
	</head>
<body><?php

require_once __DIR__ . '/inc/rstlayers.class.php';

class CDocuments
{
	const NAT_RST = 'NAT_RST';
	const NAT_DOSS_RST = 'NAT_DOSS_RST';

	function __construct()
	{
		$this->aDocuments = array(
			'/home/cedric/Sync/Central/20380119_EnCours.rst' => self::NAT_RST,
			'/home/cedric/Sync/Central/Perso.txt' => self::NAT_RST, 
			'/home/cedric/Sync/Ecritures/Manuels/' => self::NAT_DOSS_RST,
		);
	}

	function Lister()
	{
		$a = array();
		foreach($this->aDocuments as $sPath => $sNature) {
			switch($sNature) {
				case self::NAT_RST : 
					if(is_file($sPath)) $a[$sPath] = $sNature; 
					break;

				case self::NAT_DOSS_RST : 
					foreach(glob($sPath . '/*.rst', GLOB_ERR) as $sPathFile) {
						$sPathFile = realpath($sPathFile);
						$a[$sPathFile] = self::NAT_RST;
					}
					break;

				default: throw new Exception('nature non gérée : ' . $sNature);
			}
		}
		return $a;
	}

	function DisplayLineLink($id, $sPath, $sValue)
	{
		$sUrl = sprintf('?doc=%s&title=%d', urlencode($sPath), $id);
		printf('<li><a href="%s">%s</a></li>', $sUrl, $sValue);
	}

	function DisplayLineRadio($id, $sPath, $sValue)
	{
		printf('<li><input type="radio" name="destSameDoc" value="%s"/>%s</li>', $id, $sValue);
	}

	function DisplayLevel($sPath, array $aStructure, array $aChildren, $fDisplayLine)
	{
		if( ! empty($aChildren)) {
			echo '<ul>';
			foreach($aChildren as $id) {
				$aData = $aStructure['lines'][$id];
				if($aData['nature'] === CRSTLigne::TITRE) {
					$this->$fDisplayLine($id, $sPath, $aData['value']);
					$this->DisplayLevel($sPath, $aStructure, $aData['children'], $fDisplayLine);
				}
			}
			echo '</ul>';
		}
	}

	function DisplayDestinations($sFile, $a)
	{
		echo '<fieldset><legend>Destination</legend><input type="submit" value="Copier" name="cmdcopy"/>'
			. '<input type="submit" value="Supprimer" name="cmddelete"/>'
			. '<input type="submit" value="Déplacer" name="cmdmove"/><br>';
		echo "<b>Autres documents, chapitre sources</b><ul>";
		foreach($this->Lister() as $sPath => $sNature) {
			printf('<li><input type="radio" name="destination" value="%s"/>&nbsp;%s</li>', md5($sPath), basename($sPath));
		}
		echo "</ul>";

		echo "<b>Même document</b>";
		$this->DisplayLevel($sFile, $a, $a['level0'], 'DisplayLineRadio');
		echo "</fieldset>";
	}

	function DisplayIn(array $a, array $aData)
	{
		if( ! empty($aData['children'])) {
			echo '<ul>';
			foreach($aData['children'] as $id) {
				if($a['lines'][$id]['nature'] !== CRSTLigne::TITRE) {
					printf('<li><input name="%s" type="checkbox"/>%s</li>', $id, $a['lines'][$id]['value']);
					$this->DisplayIn($a, $a['lines'][$id]);
				}
			}
			echo '</ul>';
		}
	}
}

$oDocList = new CDocuments();
echo '<ul>';
foreach($oDocList->Lister() as $sPath => $sNature) {
	$sUrl = sprintf('?doc=%s', urlencode($sPath));
	printf('<li><a href="%s">%s</a></li>', $sUrl, basename($sPath));
}
echo '</ul>';

if(isset($_GET['doc'])) {
	$sFile = $_GET['doc'];
	if(is_file($sFile)) {
		$oDoc = CRstLayers::Charger($sFile);
		$a = $oDoc->Get();
		$oDocList->DisplayLevel($sFile, $a, $a['level0'], 'DisplayLineLink');

		if(isset($_GET['title'])) {
			echo '<form action="?" method="post">';
			$idTitle = $_GET['title'];
			var_dump($a['lines'][$idTitle]);
			$oDocList->DisplayIn($a, $a['lines'][$idTitle]);
			echo $oDocList->DisplayDestinations($sFile, $a) . '</form>';
		}
	}
}
?></body></html>