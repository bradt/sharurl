<?php
unset($_SESSION['user_id']);
setcookie('SHARURLID', '', time(), '/');

if (isset($_GET['redirect']) && $_GET['redirect']) {
    $core->redirect($_GET['redirect']);
}
elseif ($_SERVER['HTTP_REFERER']) {
    $core->redirect($_SERVER['HTTP_REFERER']);
}
else {
    $core->redirect('/');
}
?>