<?php

$ptz = require(__DIR__ . '/../lib_cam.php');

$presets = $ptz->getPresets();
var_dump($presets);
