<?php
$template = 'ajax';

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
	$token = $_POST['token'];
	
	if (!$package['alias']) {
		$alias = generate_alias();
		
		$data = array(
			'alias' => $alias,
			'status' => 'complete',
			'updated' => $db->now()
		);

		$where = sprintf("`token` = '%s'", $db->escape($token));
		$db->update('packages', $data, $where);
	}
	else {
		$alias = $package['alias'];
	}
	
	$url = get_siteinfo('siteurl') . '/' . $alias;
}

if (!$error) {
	echo $url;
}
?>