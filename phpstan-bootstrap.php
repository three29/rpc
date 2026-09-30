<?php

/**
 * PHPStan Bootstrap File
 * Defines constants that are normally set at runtime
 */

// Application paths - these are defined by the Application bootstrap
if (!defined('APP_PATH')) {
    define('APP_PATH', __DIR__ . '/APP');
}

if (!defined('CACHE_PATH')) {
    define('CACHE_PATH', __DIR__ . '/tmp/cache');
}
