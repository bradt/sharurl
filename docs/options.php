<?php
$template = 'ajax';

$error = '';
$errors = array();

if (isset($_POST['token'])) {
	$token = $_POST['token'];
}
else {
	$error = 'Missing variable `token`.';
}

if (!$error) {
    $sql = sprintf("
        SELECT * FROM packages
        WHERE token = '%s'"
    , $db->escape($token));
    $package = $db->select_one($sql);

    if (!$package) {
        $error = 'Could not retrieve record for `token` ' . $token;
    }
}

if (!$error && isset($_POST['submit'])) {
	if (!isset($_POST['expires']) || !$_POST['expires']) {
		$errors['expires'] = 'Please provide an expiry date.';
	}
    
	if (isset($_POST['protect']) && $_POST['protect'] && (!isset($_POST['password']) || !$_POST['password'])) {
		$errors['password'] = 'Please provide a password.';
	}
	
	if (!empty($errors)) {
		$error = 'Please correct the errors below.';
	}

    $options = array(
        'title' => $_POST['title'],
        'message' => $_POST['message'],
        'password' => $_POST['password'],
        'auto_download' => $_POST['auto_download']
    );
    $options = serialize($options);

    $protect = $_POST['protect'];

    $created = $db->timestamp($package['created']);

    switch ($_POST['expires']) {
        case '1m':
            $expires = strtotime('+1 Month', $created);
            break;
        case '3m':
            $expires = strtotime('+3 Months', $created);
            break;
        case '6m':
            $expires = strtotime('+6 Months', $created);
            break;
        default:
            $expires = strtotime('+2 Weeks', $created);
    }

	if (!$error) {
		$data = array(
            'expires' => $db->to_date($expires),
            'options' => $options,
            'updated' => $db->now()
		);
		$where = sprintf("`token` = '%s'", $db->escape($token));
		$db->update('packages', $data, $where);
		
		$error = $db->error();
	}

    $options = unserialize($options);
}
elseif (!$error) {
    $created = $db->timestamp($package['created']);
    $expires = $db->timestamp($package['expires']);
    
    if ($package['options']) {
        $options = unserialize($package['options']);
    }
    
    if ($options['password']) {
        $protect = true;
    }
    else {
        $protect = false;
    }
}
?>

<div class="box-content">
    <h2>Personalize your download page</h2>
    <form action="/options/" method="post">
        <input type="hidden" name="token" value="<?php echo $token; ?>" />
        <input type="hidden" name="submit" value="1" />
        <?php if ($error): ?>
        <p class="error-msg"><?php echo $error; ?></p>
        <?php endif; ?>
        <div class="field textbox">
            <label for="title">Title</label>
            <input type="text" name="title" value="<?php echo htmlentities($options['title']); ?>" id="title" class="text" />
        </div>
        <div class="field textarea">
            <label for="message">Message</label>
            <textarea name="message" class="message" id="message"><?php echo htmlentities($options['message']); ?></textarea>
            <p class="note">Caution: The message you enter above will appear
            on the download page and will be more or less public.
            <b>It will not be sent via email</b>.</p>
        </div>
        <div class="field checkbox">
            <input type="checkbox" name="protect" value="1" id="protect" class="checkbox"<?php echo ($protect) ? 'checked="checked"' : ''; ?> />
            <label for="protect">Password protect your file</label>
        </div>
        <div class="field password-protect"<?php echo (!$protect) ? 'style="display: none;"' : ''; ?>>
            <input type="text" name="password" class="text" value="<?php echo htmlentities($options['password']); ?>" />
            <?php $validation->print_error($errors, 'password'); ?>
        </div>
        <div class="field checkbox">
            <input type="checkbox" value="1" name="auto_download" id="auto-download" class="checkbox"<?php echo ($options['auto_download']) ? 'checked="checked"' : ''; ?> />
            <label for="auto-download">Automatically download the file when the download page loads</label>
        </div>
        <div class="field radio-list">
            <label>Expires in...</label>
            <ul>
                <li>
                    <input type="radio" name="expires" value="2w" id="expires-2-weeks"<? echo ($expires == strtotime('+2 weeks', $created)) ? ' checked="checked"' : ''; ?> />
                    <label for="expires-2-weeks">2 Weeks</label>
                </li>
                <li>
                    <input type="radio" name="expires" value="1m" id="expires-1-month"<? echo ($expires == strtotime('+1 month', $created)) ? ' checked="checked"' : ''; ?> />
                    <label for="expires-1-month">1 Month</label>
                </li>
                <li>
                    <input type="radio" name="expires" value="3m" id="expires-3-months"<? echo ($expires == strtotime('+3 months', $created)) ? ' checked="checked"' : ''; ?> />
                    <label for="expires-3-months">3 Months</label>
                </li>
                <li>
                    <input type="radio" name="expires" value="6m" id="expires-6-months"<? echo ($expires == strtotime('+6 months', $created)) ? ' checked="checked"' : ''; ?> />
                    <label for="expires-6-months">6 Months</label>
                </li>
            </ul>
            <?php $validation->print_error($errors, 'expires'); ?>
        </div>
        <input type="image" name="signup" src="<?php siteinfo('themeurl'); ?>/img/blank.gif" class="button button-save" />
        <?php if (!$error && isset($_POST['submit'])): ?>
        <p class="success">Your changes were saved.</p>
        <?php endif; ?>
    </form>
</div>
