<?php

class Config {
    private static $config;

    public static function load() {
        if (self::$config === null) {
            self::$config = parse_ini_file(__DIR__ . '/../../.env');
        }
    }

    public static function get($key, $default = null) {
        self::load();
        return self::$config[$key] ?? $default;
    }
    public static function getMultiple($keys) {
        self::load();
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = self::$config[$key] ?? null;
        }
        return $result;
    }
}