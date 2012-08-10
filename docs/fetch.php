<?php
$template = 'api';

set_time_limit(0);

list($alias) = $params;

$sql = sprintf("
    SELECT * FROM packages
    WHERE alias = '%s'"
, $db->escape($alias));
$package = $db->select_one($sql);

if (!$package) {
    $core->redirect('/404/');
}

if (has_exceeded_bandwidth($package['user_id'])) {
    $core->redirect('/' . $alias);
}

$package_dir = get_siteinfo('uploads') . DS . $package['token'];

if (is_dir($package_dir)) {
    chdir($package_dir);
    
    $files = glob('*');
    if (count($files) > 1) {
        $filename =  $package['alias'] . '.zip';
        $filepath =  get_siteinfo('downloads') . DS . $filename;
    
        if (!file_exists($filepath)) {
            require_once(ROOT . DS . 'lib' . DS . 'pclzip.lib.php');
            $archive = new PclZip($filename);
            if ($archive->create($files, PCLZIP_OPT_NO_COMPRESSION) == 0) {
                die("Error : ".$archive->errorInfo(true));
            }
            rename($filename, $filepath);
            
            foreach ($files as $file) {
                unlink($file);
            }
            
            rmdir($package_dir);
        }
    }
    else {
        $filepath =  get_siteinfo('downloads') . DS . $package['alias'];
        
        if (!is_dir($filepath)) {
            mkdir($filepath);
        }
        
        $filename = $files[0];
        $filepath = $filepath . DS . $filename;
        
        if (!file_exists($filepath)) {
            rename($filename, $filepath);
            rmdir($package_dir);
        }
    }
}

$filepath =  get_siteinfo('downloads') . DS . $package['alias'];

if (is_dir($filepath)) {
    $files = glob($filepath . DS . '*');
    $filename = basename($files[0]);
    $filepath = $filepath . DS . $filename;
    header('Content-Type: application/octet-stream');
}
elseif (file_exists($filepath . '.zip')) {
    $filename =  $package['alias'] . '.zip';
    $filepath = $filepath . '.zip';
    header('Content-Type: application/zip');
}
else {
    $core->redirect('/404/');
}

header('Content-Length: ' . filesize($filepath));
header('Content-Disposition: attachment; filename=' . urlencode($filename));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Transfer-Encoding: binary');
ob_clean();
flush();

$bytes = @readfile($filepath);
exit;
?>
