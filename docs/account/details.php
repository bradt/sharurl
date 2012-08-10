<?php
$template = 'twocol';

if (!$user) {
	$core->redirect('/login/');
}

$error = '';
$errors = array();

if (!$error && isset($_POST['submit'])) {
	if (!isset($_POST['email']) || !$_POST['email']) {
		$errors['email'] = 'Please provide an email address.';
	}
	elseif (!$validation->email($_POST['email'])) {
		$errors['email'] = 'Please provide a valid email address.';
	}
	else {
		$sql = sprintf("SELECT * FROM users WHERE email = '%s' AND id <> '%s'", $db->escape($_POST['email']), $user['id']);
		$_user = $db->select_one($sql);
		
		if ($_user) {
			$errors['email'] = 'That email address is already signed up under another account.';
		}
	}

	if (!isset($errors['email']) && $user['email'] != $_POST['email']) {
		$data = array(
			'email' => $_POST['email'],
			'activation' => generate_alias(16),
			'updated' => $db->now(),
		);
		$where = sprintf("`id` = '%s'", $db->escape($user['id']));
		$db->update('users', $data, $where);
		
		$user = array_merge($user, $data);

		$error = $db->error();

		if (!$error) {
			email_activation($user);
		}
	}
	
	if (!isset($_POST['password']) || !$_POST['password']) {
		// it's fine, changing the password is optional
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

	if (!isset($errors['password']) && !isset($errors['confirm_password']) && $_POST['password']) {
		$data = array(
			'password' => sha1($_POST['password']),
			'updated' => $db->now(),
		);
		$where = sprintf("`id` = '%s'", $db->escape($user['id']));
		$db->update('users', $data, $where);

		$error = $db->error();
	}
		
	if (!empty($errors)) {
		$error = 'Please correct the errors below.';
	}
	
	$sql = sprintf("SELECT * FROM users WHERE id = '%s'", $db->escape($user['id']));
	$user = $db->select_one($sql);
}
?>

<h2>My Details</h2>

<?php if (!$error && isset($_POST['submit'])) : ?>
<p class="success-msg">Your details were successfully saved.</p>
<?php endif; ?>

<form action="" method="post">
	<input type="hidden" name="submit" value="1" />
	<?php if ($error): ?>
	<p class="error-msg"><?php echo $error; ?></p>
	<?php endif; ?>
	<div class="field textbox">
		<label for="email">Email Address</label>
		<input type="text" name="email" id="email" value="<?php echo htmlentities($user['email']); ?>" class="text" />
		<p>You will need to confirm your new email address.</p>
		<?php $validation->print_error($errors, 'email'); ?>
	</div>
	<br /><br />
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