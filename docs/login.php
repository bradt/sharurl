<?php
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
		$sql = sprintf("SELECT * FROM users WHERE email = '%s'", $db->escape($_POST['email']));
		$user = $db->select_one($sql);
		
		if (!$user) {
			$errors['email'] = 'That email address is not yet signed up. <a href="/signup/?email=' . urlencode($_POST['email']) . '">Click here to signup.</a>';
		}
	}
	
	if (!isset($_POST['password']) || !$_POST['password']) {
		$errors['password'] = 'Please provide a password.';
	}
	elseif ($user && $user['password'] != sha1($_POST['password'])) {
		$errors['password'] = 'The password entered is incorrect.';
	}

	if (!empty($errors)) {
		$user = null;
		$error = 'Please correct the errors below.';
	}

	if ($user['activation']) {
		$error = 'Your account has not been activated. Please check
		your email for activation instructions. <a href="/activate/">
		Click here for the activation page.</a>';
	}

	if (!$error) {
		login_user($user['id']);
		
		if (isset($_POST['redirect']) && $_POST['redirect']) {
			$core->redirect($_POST['redirect']);
		}
		else {
			$core->redirect('/');
		}
	}
}

if ($error || !isset($_POST['submit'])) {
	?>

	<h2>Login</h2>

	<p class="indicates"><span class="req">*</span> Indicates required fields</p>
	
	<form action="/login/" method="post">
		<input type="hidden" name="submit" value="1" />
		<input type="hidden" name="redirect" value="<?php echo htmlentities($_POST['redirect']); ?>" />
		<?php if ($error): ?>
		<p class="error-msg"><?php echo $error; ?></p>
		<?php endif; ?>
		<div class="field textbox">
			<label for="email">Email Address<span class="req">*</span></label>
			<input type="text" name="email" id="email" value="<?php echo htmlentities($_POST['email']); ?>" class="text" />
			<?php $validation->print_error($errors, 'email'); ?>
		</div>
		<div class="field textbox">
			<label for="password">Password<span class="req">*</span></label>
			<input type="password" name="password" id="password" class="text" />
			<?php $validation->print_error($errors, 'password'); ?>
		</div>
		<input type="image" name="login" src="<?php siteinfo('themeurl'); ?>/img/blank.gif" class="button button-login" />
		<p class="signup-link">
			Don't have an account? <a href="/signup/">Signup!</a><br />
			Forgot your password? <a href="/reset-password/<?php echo ($_POST['email']) ? '?email=' . urlencode($_POST['email']) : ''; ?>">Reset it now.</a>
		</p>
	</form>
	
	<?php
}
?>