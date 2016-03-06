<style type="text/css">
	.lineCurrent { font-weight: bold }
	.lineNext { color: grey; }
	.choices { padding: 1em; margin: 1em 0 }
	.button { border: 1px solid red; margin: 0 5px ; padding: 3px;}
</style>

<?php

session_start();

if(isset($_GET['reset'])) $_SESSION = array();

if( ! isset($_SESSION['aLines'])) {
	$sFile = __DIR__ . '/../NexusNotes.txt';
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
		#TODO
	}
}

if(isset($_GET['newChoice'])) {
	if( ! in_array($_GET['newChoice'], $_SESSION['aChoices'])) $_SESSION['aChoices'][] = $_GET['newChoice'];
	$_SESSION['aLines'][$_SESSION['iLine']]['dest'] = $_GET['newChoice'];
	$_SESSION['iLine']++;	
}

printf('<div class="lineCurrent">%s</div>', $_SESSION['aLines'][$_SESSION['iLine']]['line']);
for($ct = $_SESSION['iLine']+1; $ct<$_SESSION['iLine']+4; $ct++) {
	if(isset($_SESSION['aLines'][$ct])) printf('<div class="lineNext">%s</div>', $_SESSION['aLines'][$ct]['line']);
}

printf('<div class="choices"><form action="index.php" method="get">');
foreach($_SESSION['aChoices'] as $sChoice) {
	printf('<span class="button"><a href="?store=%1$d&where=%2$s">%2$s</a></span>', $_SESSION['iLine'], $sChoice);
}
printf('<br clear="both"/><br><div>Nouveau&nbsp;<input type="text" name="newChoice"/>&nbsp;<input type="button" value="add"/></div>');
printf('</form></div>');

printf('<div><a href="?">Recharger</a> - <a href="?write">Sauver</a> - <a href="?reset">Reset</a></div>');