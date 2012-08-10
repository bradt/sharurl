<?php
list($code) = $params;

$_user = false;

if (!$code) :
    $error = '';
    
    if (isset($_POST['submit'])) {
        $sql = sprintf("SELECT * FROM users WHERE email = '%s'", $db->escape($_POST['email']));
        $_user = $db->select_one($sql);
        
        if (!$_user) {
            $error = 'Sorry, there is no account registered with that email address.';
        }
        else {
            $data = array(
                'reset_password' => generate_alias(16),
                'updated' => $db->now()
            );
            $where = sprintf("`id` = '%s'", $_user['id']);
            $db->update('users', $data, $where);
            
            $sql = sprintf("SELECT * FROM users WHERE id = '%s'", $db->escape($_user['id']));
            $_user = $db->select_one($sql);
    
            email_reset_password($_user);
        }
    }
    ?>
    
    <h2>Reset Password</h2>

    <?php if (isset($_POST['submit']) && !$error) : ?>
    
        <p>An email has been sent to "<?php echo htmlentities($_POST['email']); ?>" with instructions to reset your password.</p>
    
    <?php else : ?>

        <?php if (isset($_POST['submit'])) : ?>
            <p class="error-msg"><?php echo $error; ?></p>
        <?php endif; ?>
    
        <form action="" method="post">
            <input type="hidden" name="submit" value="1" />
            <div class="field textbox">
                <label for="email">Email Address</label>
                <input type="text" name="email" id="email" value="<?php echo htmlentities($_GET['email']); ?>" class="text" />
            </div>
            <input type="image" name="submitbtn" src="<?php siteinfo('themeurl'); ?>/img/blank.gif" class="button button-submit" />
        </form>

        <?php
    endif;
    
else :
    $sql = sprintf("SELECT * FROM users WHERE reset_password = '%s'", $db->escape($code));
    $_user = $db->select_one($sql);

    if (!$_user) :
        ?>
        
        <h2>Reset Password Error</h2>
        
        <p>Sorry, but that reset code does not exist.</p>
        
        <?php
    else :
        $errors = array();

        if (isset($_POST['submit'])) {
            if (!isset($_POST['password']) || !$_POST['password']) {
                $errors['password'] = 'Please provide a password.';
            }
            elseif (strpos($_POST['password'], ' ') !== false) {
                $errors['password'] = 'Please provide a password without spaces.';
            }
            elseif (strlen($_POST['password']) < 4 || strlen($_POST['password']) > 32) {
                $errors['password'] = 'Please provide a password between 4 and 32 characters in length.';
            }
            elseif ($_POST['password'] != $_POST['confirm_password']) {
                $errors['confirm_password'] = 'Sorry, your passwords do not match. Please try again.';
            }

            if (empty($errors)) {
                $data = array(
                    'password' => sha1($_POST['password']),
                    'reset_password' => '',
                    'updated' => $db->now()
                );
                $where = sprintf("`id` = '%s'", $_user['id']);
                $db->update('users', $data, $where);
                
                login_user($_user['id']);
                
                $user = $_user;
            }
        }
        ?>
    
        <h2>Reset Password</h2>

        <?php if (isset($_POST['submit']) && empty($errors)) : ?>

            <p>Congratulations, your password has been reset and you have been logged-in.</p>
            
            <p><a href="/">Start sharing files now &raquo;</a></p>

        <?php else : ?>        
    
            <form action="" method="post">
                <input type="hidden" name="submit" value="1" />
                <?php if (isset($_POST['submit'])): ?>
                <p class="error-msg">Please correct the errors below.</p>
                <?php endif; ?>
                <div class="field textbox">
                    <label for="password">Change Password</label>
                    <input type="password" name="password" id="password" class="text" />
                    <?php $validation->print_error($errors, 'password'); ?>
                </div>
                <div class="field textbox">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="text" />
                    <?php $validation->print_error($errors, 'confirm_password'); ?>
                </div>
                <input type="image" name="save" src="<?php siteinfo('themeurl'); ?>/img/blank.gif" class="button button-save" />
            </form>        
    
            <?php
        endif;
        
    endif;
    
endif;
?>