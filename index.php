<?php
// Fix for IIS, which doesn't set REQUEST_URI
if ( empty( $_SERVER['REQUEST_URI'] ) ) {

	// IIS Mod-Rewrite
	if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
		$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
	}
	// IIS Isapi_Rewrite
	else if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
		$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
	}
	else
	{
        // Hack for hosting.ca
        if (preg_match("/^404;/", $_SERVER['QUERY_STRING'])) {
            $url = split(';', $_SERVER['QUERY_STRING']);
            $url = parse_url($url[1]);
            $_SERVER['REQUEST_URI'] = $url['path'];
        }
        else {
            // Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
            if ( $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'] )
                $_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
            else
                $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];

            // Append the query string if it exists and isn't null
            if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
                $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
	}
}

// this sets the sytem / or \ :
if (strstr( PHP_OS, "WIN")) {
    define('DS', '\\');
}
else {
    define('DS', '/');
}

define('ROOT', realpath(dirname(__FILE__)));
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

include('config.php');

$db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$core = new Core();
$validation = new Validation();

apply_filters('libraries_loaded');

$url_parts = split('\?', $_SERVER['REQUEST_URI']);
global $request_file;
$request_file = $url_parts[0];
$request_file = strtolower(trim($request_file, '/'));
$request_file = preg_replace('@[^a-zA-Z0-9/\-_.]@', '', $request_file);
$request_file = str_replace('..', '', $request_file);
$request_file = str_replace('/', DS, $request_file);

$request_file = apply_filters('request_file', $request_file);

$params = array();
if ($request_file == '') {
    $request_path = DOCS . DS . 'index.php';
}
else {
    $tmp_path = $request_file;
    while (!file_exists(DOCS . DS . $tmp_path . '.php') && !is_dir(DOCS . DS . $tmp_path)) {
       $tmp_path = substr($tmp_path, 0, strrpos($tmp_path, '/'));
    }
    
    if ($tmp_path) {
        if (is_dir(DOCS . DS . $tmp_path)) {
            $request_path = DOCS . DS . $tmp_path . DS . 'index.php';
        }
        elseif (file_exists(DOCS . DS . $tmp_path . '.php')) {
            $request_path = DOCS . DS . $tmp_path . '.php';
        }
        
        $params = str_replace($tmp_path, '', $request_file);
        $params = trim($params, '/');
        $params = split('/', $params);
        
        $request_file = $tmp_path;
    }
    else {
        $request_path = DOCS . DS . $request_file . '.php';
    }
}

if (!file_exists($request_path)) {
    header('HTTP/1.0 404 Not Found');

    $request_path = DOCS . DS . '404.php';
    
    if (!file_exists($request_path)) {
        $request_path = CORE_DOCS . DS . '404.php';
    }
}

apply_filters('pre_include');

if (in_array($request_file, $config['nobuffer'])) {
    include($request_path);
    exit;
}
else {
    ob_start();
    include($request_path);
    $content = ob_get_clean();
}

apply_filters('post_include');

if (!isset($theme)) {
    $theme = 'default';
}

$theme_dir = THEMES . DS . $theme;

if (!file_exists($theme_dir)) {
    $theme_dir = THEMES . DS . 'default';
    
    if (!file_exists($theme_dir)) {
        $theme_dir = CORE_THEME;
    }
}

define('THEME', $theme_dir);

if (isset($template)) {
    $template = preg_replace('@[/\\\\]@', DS, $template);
    $template = trim($template, '/');
}
else {
    $template = 'index';
}

$template_path = $theme_dir . DS . $template . '.php';

if (!file_exists($template_path)) {
    $template_path = $theme_dir . DS . 'index.php';

    if (!file_exists($template_path)) {
        $template_path = CORE_THEME . DS . 'index.php';
    }
}

include($template_path);
?>