<?php
function get_content() {
    global $content;
    echo $content;
}

function get_header() {
    global $site_append_head, $nav;
    include(THEME . DS . 'header.php');
}

function get_footer() {
    include(THEME . DS . 'footer.php');
}

function get_sidebar() {
    global $nav;
    include(THEME . DS . 'sidebar.php');
}

function get_siteinfo($key) {
    global $config;
    if (isset($config[$key])) {
        return $config[$key];
    }
    else {
        return '';        
    }
}

function page_title($seperator = '&laquo;') {
    global $page_title;
    
    if ($page_title != '') {
        echo $page_title . ' ' . $seperator . ' ';
    }
}

function siteinfo($key) {
    echo get_siteinfo($key);
}

function language_attributes() {
     echo 'xml:lang="en" lang="en"';
}

function page_head() {
    echo $site_append_head;
}

function add_head($content) {
    global $site_append_head;
    $site_append_head .= $content;
}

function add_css($name) {
    add_head('<link type="text/css" rel="stylesheet" href="' . get_siteinfo('themeurl') . '/css/' . $name . '.css" media="screen" />');
}
?>