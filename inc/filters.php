<?php
add_filter('request_file', 'request_file_download');

function request_file_download($request_file) {
    global $package, $db;
    
    if ($request_file == '') {
        $request_path = DOCS . DS . 'index.php';
    }
    elseif (is_dir(DOCS . DS . $request_file)) {
        $request_path = DOCS . DS . $request_file . DS . 'index.php';
    }
    else {
        $request_path = DOCS . DS . $request_file . '.php';
    }

    if (!file_exists($request_path)) {
        if (preg_match('@^[a-z0-9]{5}$@', $request_file)) {
            $sql = sprintf("SELECT * FROM packages WHERE alias = '%s'", $request_file);
            $package = $db->select_one($sql);

            if ($package) {
                return 'download';
            }
        }
        
    }

    return $request_file;
}

add_filter('pre_include', 'init_session');

function init_session() {
    global $user, $db;
    
    session_start();
    $user = false;
    //var_dump($_SESSION);
    if (isset($_SESSION['user_id'])) {
        $sql = sprintf("
            SELECT * FROM users
            WHERE id = '%s'"
        , $db->escape($_SESSION['user_id']));
        $user = $db->select_one($sql);
    }
    else if (isset($_COOKIE['SHARURLID'])) {
        $sql = sprintf("
            SELECT * FROM users
            WHERE token = '%s'"
        , $db->escape($_COOKIE['SHARURLID']));
        $user = $db->select_one($sql);
    }
}

function set_page_title() {
    global $page_title, $nav, $request_file;
    $pages = split('/', $request_file);
    $page = array_shift($pages);
    $current = $nav[$page];
    foreach ($pages as $page) {
        if (isset($current['children'][$page])) {
            $current = $current['children'][$page];
        }
        else {
            $page_title = '';
        }
    }
    
    $page_title = $current['title'];
}
add_filter('pre_include', 'set_page_title');
?>