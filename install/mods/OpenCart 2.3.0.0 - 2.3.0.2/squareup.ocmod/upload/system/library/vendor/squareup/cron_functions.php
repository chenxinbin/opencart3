<?php

function squareup_validate() {
    if (!getenv("SQUARE_CRON")) {
        die("Not in Command Line." . PHP_EOL);
    }
}

function squareup_chdir($current_dir) {
    $root_dir = dirname(dirname(dirname(dirname($current_dir))));

    chdir($root_dir);

    return $root_dir;
}

function squareup_define_version($index_file) {
    if (defined('VERSION')) {
        return true;
    }

    if (!file_exists($index_file) || !is_readable($index_file)) {
        die("index.php file not found.");
    }

    $content = @file_get_contents($index_file);

    $matches = array();

    preg_match("/VERSION\'\s*,\s*\'(.*?)\'/", $content, $matches);

    if (!empty($matches[1])) {
        define('VERSION', $matches[1]);
        return true;
    }

    return false;
}

function squareup_define_route() {
    require_once DIR_CONFIG . 'vendor/squareup.php';

    define('SQUAREUP_ROUTE', $_['squareup_route'] . '/recurring');
}

function squareup_init($current_dir) {
    // Validate environment
    squareup_validate();

    // Set up default server vars
    $_SERVER["HTTP_HOST"] = getenv("CUSTOM_SERVER_NAME");
    $_SERVER["SERVER_NAME"] = getenv("CUSTOM_SERVER_NAME");
    $_SERVER["SERVER_PORT"] = getenv("CUSTOM_SERVER_PORT");

    putenv("SERVER_NAME=" . $_SERVER["SERVER_NAME"]);

    // Change root dir
    $root_dir = squareup_chdir($current_dir);

    // Define version constant based on value in index.php
    if (!squareup_define_version($root_dir . '/index.php')) {
        die("VERSION constant could not be defined.");
    }

    // Load OpenCart configuration
    if (is_file($root_dir . '/config.php')) {
        require_once($root_dir . '/config.php');
    } else {
        die("config.php file not found.");
    }

    // Define default route - used in config/vendor/squareup_cron.php
    squareup_define_route();

    // Startup
    require_once(DIR_SYSTEM . 'startup.php');

    start('vendor/squareup_cron');
}