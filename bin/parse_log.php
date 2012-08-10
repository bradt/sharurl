<?php
$db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$logs_dir = realpath(ROOT . DS . '..' . DS . '..' . DS . 'logs');
$log_file = $logs_dir . DS . 'sharurl.com.access_log';

$log_regex = "/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) (\".*?\") (\".*?\")$/";

$fp = @fopen($log_file, 'r');
if (!$fp) {
    die('Could not open file ' . $log_file);
}

$settings = get_settings('log_seek', 'log_last_date');
extract($settings);

// Check if first date in the file is greater than the last date we parsed (i.e. log was rotated)
$line = fgets($fp);
preg_match($log_regex, $line, $matches);
$date = strtotime($matches[4] . ':' . $matches[5] . ' ' . $matches[6]);
if ($date && $date > $log_last_date) {
    $log_seek = 0;
}

fseek($fp, $log_seek);

while (!feof($fp)) {
    $line = fgets($fp);

    if (!preg_match($log_regex, $line, $matches))
        continue;

    $log_seek += strlen($line);
    $log_last_date = $date;
    
    $date = strtotime($matches[4] . ':' . $matches[5] . ' ' . $matches[6]);
    $day = date('Y-m-d', $date);
    $file = $matches[8];
    $status = $matches[10];
    $size = $matches[11];
    
    $matches = array();
    if ($status == 200 && is_numeric($size) && preg_match('@^/fetch/(.*?)/?$@', $file, $matches)) {
        $alias = $matches[1];
        
        $sql = sprintf("
            SELECT * FROM packages
            WHERE alias = '%s'"
        , $db->escape($alias));
        $package = $db->select_one($sql);
        
        if ($package) {
            $where = sprintf("package_id = '%s' AND `day` = '%s'"
                , $db->escape($package['id']), $db->escape($day));
            
            $sql = 'SELECT * FROM downloads WHERE ' . $where;
            $download = $db->select_one($sql);

            if ($download) {
                $data = array(
                    'count' => $download['count'] + 1,
                    'bytes' => $download['bytes'] + $size
                );
                $db->update('downloads', $data, $where);
                echo mysql_error();
            }
            else {
                $data = array(
                    'package_id' => $package['id'],
                    'day' => $day,
                    'count' => 1,
                    'bytes' => $size
                );
                $db->insert_one(array('downloads' => $data));
                echo mysql_error();
            }
        }
        
        echo $alias, ' ', $size, "\n";
    }
}
fclose($fp);

update_setting('log_last_date', $log_last_date);
update_setting('log_seek', $log_seek);
?>