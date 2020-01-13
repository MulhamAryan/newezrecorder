<?php
$ptz = require(__DIR__ . '/../library.php');
$scene = $argv[1];
var_dump($ptz->positionMove($scene));
?>