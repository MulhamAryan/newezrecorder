<?php


if($argc != 2) {
	echo "Usage: savepreset.php <preset_name>" . PHP_EOL;
	die();
}

$presetName = $argv[1];

require(__DIR__ . '/../library.php');

$CamController->positionSave($presetName);
