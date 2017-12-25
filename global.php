<?php
  //permet d'ecrire les erreur dans le fichier log
function WriteLog($filename, $txt){
	date_default_timezone_set('UTC');
	$thisDate = date("Y-m-dTH:i:s");
	$Addline = $thisDate . " - " . $txt . PHP_EOL;
	file_put_contents($filename, $Addline, FILE_APPEND);
	return true;
}

?>