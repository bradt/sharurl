<?php
automatic_feed_links();

define('ROOT', realpath(ABSPATH . '../../'));

global $nav;
include(ROOT . '/inc/nav.php');
define('DS', '/');

function get_siteinfo($key) {
	include(ROOT . '/inc/config.php');

    if (isset($config[$key])) {
        return $config[$key];
    }
    else {
        return '';        
    }
}

function siteinfo($key) {
    echo get_siteinfo($key);
}
?>