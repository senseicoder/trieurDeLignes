<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>trieurDeLignes</title>
	<meta content="text/html;charset=UTF-8" http-equiv="content-type" />
	<style type="text/css">
		.lineCurrent { font-weight: bold }
		.lineNext { color: grey; }
		.choices { padding: 1em; margin: 1em 0 }
		.button { border: 1px solid red; margin: 1em 5px ; padding: 1px;}
	</style>
</head>
<body>

<?php

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

session_start();

if(isset($_GET['reset'])) $_SESSION = array();

if( ! isset($_SESSION['aLines'])) {
	$sFile = '/home/cedric/atrier.txt';
	$_SESSION['aLines'] = array();
	foreach(file($sFile) as $sLine) {
		$sLine = trim($sLine);
		if($sLine != '') $_SESSION['aLines'][] = array('line' => $sLine, 'dest' => NULL);
	}
	$_SESSION['iLine'] = 0;
	$_SESSION['aChoices'] = array();
}

if(isset($_GET['store']) && isset($_GET['where'])) {
	$_SESSION['aLines'][$_SESSION['iLine']]['dest'] = $_GET['where'];
	$_SESSION['iLine']++;
}

if(isset($_GET['write'])) {
	$a = array();
	foreach($_SESSION['aLines'] as $aData) {
		$sDest = $aData['dest'];
		if($sDest != '') $a[$sDest][] = $aData['line'];
	}

	foreach($a as $sDest => $aList) {
		printf('<b>%s</b><br/>%s<br/><br/>', $sDest, implode('<br/>', $aList));
	}
}

if(isset($_GET['newChoice'])) {
	if( ! in_array($_GET['newChoice'], $_SESSION['aChoices'])) $_SESSION['aChoices'][] = $_GET['newChoice'];
	$_SESSION['aLines'][$_SESSION['iLine']]['dest'] = $_GET['newChoice'];
	$_SESSION['iLine']++;	
}

if(isset($_SESSION['aLines'][$_SESSION['iLine']]['line'])) {
	printf('<div>%d / %d</div>', $_SESSION['iLine'], count($_SESSION['aLines']));

	printf('<div class="lineCurrent">%s</div>', MakeLink($_SESSION['aLines'][$_SESSION['iLine']]['line']));
	for($ct = $_SESSION['iLine']+1; $ct<$_SESSION['iLine']+4; $ct++) {
		if(isset($_SESSION['aLines'][$ct])) printf('<div class="lineNext">%s</div>', $_SESSION['aLines'][$ct]['line']);
	}

	printf('<div class="choices"><form action="?" method="get">');
	foreach($_SESSION['aChoices'] as $sChoice) {
		printf('<span class="button"><a href="?store=%1$d&where=%2$s">%2$s</a></span>', $_SESSION['iLine'], $sChoice);
	}
	printf('<br clear="both"/><br><div>Nouveau&nbsp;<input type="text" name="newChoice"/>&nbsp;<input type="submit" value="add"/></div>');
	printf('</form></div>');
}
printf('<div><a href="?">Recharger</a> - <a href="?write">Sauver</a> - <a href="?reset">Reset</a></div>');

?>
</body>
</html>
