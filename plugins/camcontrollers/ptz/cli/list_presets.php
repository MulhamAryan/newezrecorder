<?php

require(__DIR__ . '/../library.php');
$CamController = new PtzController();
$presets = $CamController->getPresets();
var_dump($presets);
