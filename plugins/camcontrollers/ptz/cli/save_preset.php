<?php


if($argc != 2) {
	echo "Usage: savepreset.php <preset_name>" . PHP_EOL;
	die();
}

$presetName = $argv[1];

$ptz = require(__DIR__ . '/../library.php');

var_dump($ptz->positionSave($presetName));
