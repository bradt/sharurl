<?php
$template = 'api';
$error = '';

if (isset($_GET['file_count'])) {
	if (is_int((int)$_GET['file_count'])) {
		$file_count = $_GET['file_count'];
	}
	else {
		$error = 'Parameter `file_count` must be an integer.';
	}
}
else {
	$error = 'Missing parameter `file_count`.';
}

if (!$error) {
	if (isset($_GET['size'])) {
		if (is_int((int)$_GET['size'])) {
			$size = $_GET['size'];
		}
		else {
			$error = 'Parameter `size` must be an integer.';
		}
	}
	else {
		$error = 'Missing parameter `size`.';
	}
}

if (!$error) {
	if (!isset($user['id']) && !isset($_GET['user_token'])) {
		$error = 'Please <a href="/login/">login</a> or <a href="/signup/">signup</a> before uploading.';
	}
	elseif (isset($_GET['user_token'])) {
		$sql = sprintf("
			SELECT * FROM users
			WHERE token = '%s'"
		, $db->escape($_GET['user_token']));
		$user = $db->select_one($sql);
		
		if (!$user) {
			$error = 'Invalid `user_token` parameter';
		}
	}
}

if (!$error) {
	$sql = sprintf("SELECT * FROM plans WHERE id = '%s'", $db->escape($user['plan_id']));
	$plan = $db->select_one($sql);
	
	$bytes_allotted = convert_to_bytes($plan['storage'] . ' GB');

	if ($size > $bytes_allotted) {
		$error = 'Account storage limit exceeded. The ' . $plan['name'] . ' plan is
		only entitled ' . $plan['storage'] . ' GB of storage.';
	}
}

if (!$error) {
	$sql = sprintf("SELECT SUM(size) as total_size FROM packages WHERE user_id = '%s' AND status = 'complete'", $user['id']);
	$packages = $db->select_one($sql);
	
	$diff = $bytes_allotted - ($packages['total_size'] + $size);
	
	if ($diff < 0) {
		$error = 'Account storage limit exceeded. This upload exceeds your
		maximum storage limit by ' . pretty_filesize(abs($diff)) . '. You may
		free up some storage by deleting existing files in
		<a href="/account/">My Account</a>.';
	}
}

if (!$error) {
	$token = generate_token();
	
	$now = time();
	$expires = strtotime($config['dlexpires'], $now);

	$options = array(
		'title' => '',
		'message' => '',
		'password' => '',
		'auto_download' => ''
	);
	$options = serialize($options);
	
	$data = array(
	    'packages' => array(
	        'token' => $token,
	        'file_count' => $file_count,
	        'server_id' => 1,
	        'size' => $size,
	        'expires' => $db->to_date($expires),
	        'options' => $options,
	        'user_id' => $user['id'],
	        'status' => 'init',
	        'updated' => $db->to_date($now),
	        'created' => $db->to_date($now)
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
