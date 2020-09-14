<?php

if($argc != 2) {
	echo "Usage: removepreset.php <preset_name>" . PHP_EOL;
	die();
}

$presetName = $argv[1];

$ptz = require(__DIR__ . '/../library.php');

//Logger::$print_logs = true;

var_dump($ptz->positionDelete($presetName));
