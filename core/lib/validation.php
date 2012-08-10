<?php
class Validation {
    function __construct() {
    }

    function email($str) {
		return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
    }
    
    function print_error($errors, $key) {
        if (isset($errors[$key])) {
            echo '<p class="error">', $errors[$key], '</p>';
        }
    }
}
?>