<?php
include __DIR__ . "/../global_config.inc";

echo "Starting sync loop..." . PHP_EOL;
$logger = new RecorderLogger();
$res = $logger->run();
exit($res);

