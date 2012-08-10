<?php
$template = 'api';
$errors = array();

if (!isset($_POST['email']) || !$_POST['email']) {
	$errors['email'] = 'Please provide an email address.';
}
elseif ($validation->email($_POST['email'])) {
	$errors['email'] = 'Please provide a valid email address.';
}

if (!isset($_POST['password']) || !$_POST['password']) {
	$errors['password'] = 'Please provide a password.';
}
elseif (strpos($_POST['password'], ' ') != -1) {
	$errors['password'] = 'Please provide a password without spaces.';
}
elseif (strlen($_POST['password']) < 8 || strlen($_POST['password']) > 32) {
	$errors['password'] = 'Please provide a password between 8 and 32 characters in length.';
}
elseif ($_POST['password'] != $_POST['confirm_password']) {
	$errors['confirm_password'] = 'Sorry, your passwords do not match. Please try again.';
}

if (!$error) {
	$email = $_POST['email'];
	$password = $_POST['password'];
	
	$data = array(
	    'users' => array(
			'email' => $email,
			'password' => $password,
	        'updated' => $db->now(),
	        'created' => $db->now()
	    )
	);
	$db->insert($data);
    
    $error = $db->error();
}

if (!$error) {	
    $output = array(
        'token' => $token
    );
}
else {
    $output = array();
}

render_api($error, $output);
?>
