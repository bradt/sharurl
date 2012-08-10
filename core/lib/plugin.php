<?php
function add_filter($tag, $function, $priority = 10, $num_args = 1) {
    global $filters;
    $filters[$tag][$priority][$function] = array('function' => $function, 'num_args' => $num_args);
    return true;
}

function apply_filters($tag, $string = '') {
    global $filters;
    
    if (!isset($filters[$tag])) {
        return $string;
    }
    
    $args = func_get_args();
    
    foreach ($filters[$tag] as $priority => $priority_filters) {
        foreach ($priority_filters as $filter) {
            if (!is_null($filter['function'])) {
                $args[1] = $string;
                $string = call_user_func_array($filter['function'], array_slice($args, 1, (int) $filter['num_args']));
            }
        }
    }
    
    return $string;
}

function remove_filter($tag, $function, $priority = 10, $num_args = 1) {
    global $filters;
    
    $return = isset($filters[$tag][$priority][$function]);
    
    unset($filters[$tag][$priority][$function]);
    
    return $return;
}
?>