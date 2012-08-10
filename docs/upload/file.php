<?php
$template = 'api';
$error = '';

ini_set("html_errors", "0");

if (isset($_POST['token'])) {
	$token = $_POST['token'];
}
else {
	$error = 'Missing variable `token`.';
}

if (!$error) {
	$sql = sprintf("SELECT * FROM packages WHERE token = '%s'", $db->escape($token));
	$package = $db->select_one($sql);
	
	if (!$package) {
		$error = 'Could not find token `' . $token . '`.';
	}
}

if (!$error) {
	if (!isset($_FILES["Filedata"]) || (!is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0)) {
		$error = 'There was an error uploading your file (' . $_FILES["Filedata"]["error"] . ').';
	}

	$file = $_FILES["Filedata"];
}

if (!$error) {
	$upload_dir = get_siteinfo('uploads');
	$upload_dir = $upload_dir . DS . $package['token'];
	
	if (!is_dir($upload_dir)) {
		mkdir($upload_dir);
	}
	
	if (!move_uploaded_file($file['tmp_name'], $upload_dir . DS . $file['name'])) {
        $error = 'Could not move uploaded file.';
    }
}

if (!$error) {
	$data = array(
		'files' => array(
			'filename' => $file['name'],
			'type' => $file['type'],
			'size' => $file['size'],
			'package_id' => $package['id'],
			'created' => $db->now()
		)
	);
	$db->insert($data);
}

render_api($error);
?>