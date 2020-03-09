<?php
$recorder = "/usr/local/newezrecorder/etc/config/recorder.json";
$json = file_get_contents($recorder);
$json = json_decode($json, true);
var_dump($json);