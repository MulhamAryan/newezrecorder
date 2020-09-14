<?php

if($argc != 2) {
	echo "Usage: removepreset.php <preset_name>" . PHP_EOL;
	die();
}

$presetName = $argv[1];

require(__DIR__ . '/../library.php');

//Logger::$print_logs = true;

$CamController = new PtzController();

var_dump($CamController->positionDelete($presetName));
