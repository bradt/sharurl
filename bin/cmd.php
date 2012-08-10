<?php
if (!isset($argv) || count($argv) < 2) {
    die('Usage: ' . $argv[0] . " <cmd_name> <arg1> <arg2> ...\n");
}

// this sets the sytem / or \ :
if (strstr( PHP_OS, "WIN")) {
    define('DS', '\\');
}
else {
    define('DS', '/');
}

define('ROOT', realpath(dirname(__FILE__) . '/../'));
define('BIN', ROOT . DS . 'bin');
define('DOCS', ROOT . DS . 'docs');
define('THEMES', ROOT . DS . 'themes');
define('CORE', ROOT . DS . 'core');
define('LIB', CORE . DS . 'lib');
define('CORE_THEME', CORE . DS . 'theme');
define('CORE_DOCS', CORE . DS . 'docs');

if (!is_dir(CORE)) {
    echo "Sorry, could not locate the '" . CORE . "' directory.";
    exit;
}

include(LIB . DS . 'core.php');
include(LIB . DS . 'plugin.php');
include(LIB . DS . 'template.php');
include(LIB . DS . 'db.php');
include(LIB . DS . 'validation.php');

include(ROOT . DS . 'config.php');

$filename = $argv[1];
array_shift($argv);
array_shift($argv);

include(BIN . DS . $filename . '.php');
?>