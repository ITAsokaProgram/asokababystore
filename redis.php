<?php
$env =  parse_ini_file('/var/www/asokababystore.com/config.env');
$redis = new Redis();
$redis->connect($env['REDIS_CONNECTION'], 6379);
if (!empty($env['REDIS_PASSWORD'])) {
    $redis->auth($env['REDIS_PASSWORD']);
}
