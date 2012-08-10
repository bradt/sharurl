<?php
list($plan_slug) = $params;

if (!$plan_slug) {
	$plan_slug = 'free';
}

$coupon = get_coupon();

$sql = sprintf("SELECT * FROM plans WHERE slug = '%s'", $plan_slug);
$plan = $db->select_one($sql);

$error = '';
$errors = array();

$fill_email = '';
$fill_invite = '';

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
		
		if ($user) {
			$errors['email'] = 'That email address is already signed up. <a href="/login/" class="login-link">Click here to login.</a>';
		}
	}
	
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

	if (!isset($_POST['tos']) || !$_POST['tos']) {
		$errors['tos'] = 'You must agree to the Terms of Service.';
	}
	
	if (!empty($errors)) {
		$error = 'Please correct the errors below.';
	}

	if (!$error) {
		$sql = "SELECT * FROM plans WHERE is_active = 1 AND cost = 0";
		$free_plan = $db->select_one($sql);

		$data = array(
			'users' => array(
				'fname' => $_POST['fname'],
				'lname' => $_POST['lname'],
				'email' => $_POST['email'],
				'password' => sha1($_POST['password']),
				'invite_code' => $_POST['invite_code'],
				'coupon_code' => ($coupon) ? $coupon['code'] : '',
				'token' => generate_token(),
				'activation' => generate_alias(16),
				'reset_password' => '',
				'plan_id' => $free_plan['id'],
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'updated' => $db->now(),
				'created' => $db->now()
			)
		);
		$db->insert($data);
		$data['users']['id'] = $db->insert_id();

		$error = $db->error();

		if (!$error) {
			email_activation($data['users']);

			if ($plan['cost'] > 0) {
				$url = get_plan_paypal_url($plan, $data['users']['id'], $coupon);
				$core->redirect($url);
			}
		}
	}

	$fill_email = $_POST['email'];
	$fill_invite = $_POST['invite_code'];
}

if (!$fill_email && isset($_GET['email'])) {
	$fill_email = $_GET['email'];
}

if (!$fill_invite && isset($_COOKIE['invite'])) {
	$fill_invite = $_COOKIE['invite'];
}
?>

<h2>Signup</h2>

<?php
if (!$error && isset($_POST['submit'])) {
	?>
	<h3>Success!</h3>
	
	<p>An email has been sent to <b><?php echo htmlentities($_POST['email']); ?></b> containing your activation code.</p>
	
	<?php include('activate/form.php'); ?>
	
	<?php
}
else {
	?>
	<p class="indicates"><span class="req">*</span> Indicates required fields</p>
	
	<form action="" method="post">
		<input type="hidden" name="submit" value="1" />
		<input type="hidden" name="invite_code" value="<?php echo htmlentities($fill_invite); ?>" class="text" />
		<?php if ($error): ?>
		<p class="error-msg"><?php echo $error; ?></p>
		<?php endif; ?>
		<div class="field textbox">
			<label for="fname">First Name</label>
			<input type="text" name="fname" id="fname" value="<?php echo htmlentities($_POST['fname']); ?>" class="text" />
			<?php $validation->print_error($errors, 'fname'); ?>
		</div>
		<div class="field textbox">
			<label for="lname">Last Name</label>
			<input type="text" name="lname" id="lname" value="<?php echo htmlentities($_POST['lname']); ?>" class="text" />
			<?php $validation->print_error($errors, 'lname'); ?>
		</div>
		<div class="field textbox">
			<label for="email">Email Address<span class="req">*</span></label>
			<input type="text" name="email" id="email" value="<?php echo htmlentities($fill_email); ?>" class="text" />
			<?php $validation->print_error($errors, 'email'); ?>
		</div>
		<div class="field textbox">
			<label for="password">Password<span class="req">*</span></label>
			<input type="password" name="password" id="password" class="text" />
			<?php $validation->print_error($errors, 'password'); ?>
		</div>
		<div class="field textbox">
			<label for="confirm_password">Confirm Password<span class="req">*</span></label>
			<input type="password" name="confirm_password" id="confirm_password" class="text" />
			<?php $validation->print_error($errors, 'confirm_password'); ?>
		</div>
		<div class="field-checkbox">
			<input type="checkbox" name="tos" value="1" class="checkbox" />
			<label>By checking this box, I aknowledge that I have read and agree to the <a href="/terms/" target="_blank">Terms of Service</a>.</label> 
			<?php $validation->print_error($errors, 'tos'); ?>
		</div>
		<p class="note">
			<?php if ($plan['cost'] == 0) : ?>
			You are signing up for the <b><?php echo $plan['name']; ?> plan</b>.<br />
			<a href="/pricing/">Have you checked out the other plans?</a>
			<?php else: ?>
			<a href="#" style="float: right;" title="What is Paypal?" onclick="javascript:window.open('https://www.paypal.com/ca/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside','olcwhatispaypal','toolbar=no,location=no, directories=no, status=no, menubar=no, scrollbars=yes,resizable=yes, width=400, height=350');">
				<img src="<?php siteinfo('themeurl'); ?>/img/paypal.gif" border="0" alt="Paypal">
			</a>
			<b>Plan: <?php echo $plan['name']; ?>
			<?php if ($coupon) : ?>
			<strike>($<?php echo $plan['cost']; ?>/year)</strike>
			($<?php echo $plan['cost'] * ($coupon['discount']/100); ?>/year)
			<?php else : ?>
			($<?php echo $plan['cost']; ?>/year)
			<?php endif; ?>
			</b><br />
			<span style="font-size: 0.9em; line-height: 1.2em;">You will be redirected to <b>Paypal</b> after clicking the button below.</span>
			<?php endif; ?>
		</p>
		<input type="image" name="signup" src="<?php siteinfo('themeurl'); ?>/img/blank.gif" class="button button-signup" />
	</form>
	<?php
}
?>