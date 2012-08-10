<?php

if (isset($_GET['code'])) {
    $code = $_GET['code'];
}
else {
    list($code) = $params;
}

$_user = false;

if (!$code) :
    ?>
    
    <h2>Activate Account</h2>
    
    <?php include('form.php'); ?>
    
    <?php
else :
    $sql = sprintf("SELECT * FROM users WHERE activation = '%s'", $db->escape($code));
    $_user = $db->select_one($sql);

    if (!$_user) :
        ?>
        
        <h2>Activation Error</h2>
        
        <p>Sorry, but that activation code does not exist. Your account could not be activated.</p>
        
        <?php
    else :
        $data = array(
            'activation' => '',
            'updated' => $db->now()
        );
        $where = sprintf("`id` = '%s'", $_user['id']);
        $db->update('users', $data, $where);
        
        login_user($_user['id']);
        
        $user = $_user;
        ?>
    
        <h2>Account Activated</h2>
    
        <p>Congratulations, your account has been activated and you have been logged-in.</p>
        
        <p><a href="/">Start sharing files now &raquo;</a></p>
    
        <?php
    endif;
endif;
?>