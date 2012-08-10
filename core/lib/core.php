<?php
class Core {
    function redirect($url, $code = 0) {
        header('Location: ' . $url, false, $code);
        exit;
    }
}
?>