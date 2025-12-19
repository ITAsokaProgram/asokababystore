<?php
require_once __DIR__ . "/src/utils/Logger.php";

$logger = new AppLogger('a_test.log');

$logger->info("TEST");

?>