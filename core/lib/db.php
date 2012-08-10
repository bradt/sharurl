<?php
class DB {
    private $link;
    private $error;
    
    function __construct($host, $user, $pass, $dbname = '') {
        $this->error = '';
        $this->link = mysql_connect($host, $user, $pass);
        if (!$this->link) {
            $error = 'Could not connect to the database:' . mysql_error();
        }
        
        if ($dbname) {
            $this->select_db($dbname);
        }
    }
    
    function select_db($name) {
        return mysql_select_db($name, $this->link);
    }
    
    function insert($tables) {
        $return = array();
        foreach($tables as $table => $fields) {
            $values = array_values($fields);
            $values = array_map(array($this, 'escape'), $values);
            $fields = array_keys($fields);
            
            $sql = sprintf(
                "INSERT INTO `%s` (`%s`) VALUES ('%s')"
            , $this->escape($table), implode('`, `', $fields), implode("', '", $values));
            
            $return[$table] = mysql_query($sql, $this->link);
        }
        
        return $return;
    }
    
    function insert_one($tables) {
        $result = $this->insert($tables);

        if (!empty($result)) {
            list($result) = array_values($result);
            
            if ($result) {
                return $this->insert_id();
            }
        }
        
        return false;
    }
    
    function insert_id() {
        return mysql_insert_id($this->link);
    }
    
    function update($table, $fields, $where) {
        $fields_values = '';
        foreach($fields as $field => $value) {
            $fields_values .= sprintf("`%s` = '%s',", $this->escape($field), $this->escape($value));
        }
        $fields_values = substr($fields_values, 0, -1);

        $sql = sprintf('UPDATE %s SET %s WHERE %s', $table, $fields_values, $where);
        return mysql_query($sql, $this->link);
    }
    
    function select($sql) {
        $result = mysql_query($sql, $this->link);
        $return = array();
        if (mysql_errno($this->link) > 0) {
            echo $errors = mysql_error($this->link);
        }
        else {
            while ($row = mysql_fetch_assoc($result)) {
                $return[] = $row;
            }
        }
        return $return;
    }
    
    function select_one($sql) {
        $return = $this->select($sql);
        if (is_array($return) && !empty($return)) {
            return $return[0];
        }
        else {
            return false;
        }
    }
    
    function select_var($sql) {
        $var = $this->select_one($sql);
        return array_shift($var);
    }
    
    function error() {
        return mysql_error($this->link);
    }
    
    function escape($str) {
        return mysql_real_escape_string($str, $this->link);
    }
    
    function to_date($timestamp) {
        return gmdate('Y-m-d H:i:s', $timestamp);
    }
    
    function now() {
        return $this->to_date(time());
    }
    
    function timestamp($date) {
        return strtotime($date . ' GMT');
    }
}
?>