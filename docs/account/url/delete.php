<?php
$template = 'twocol';

list($package_id, $confirmed) = $params;

if (!$user) {
	$core->redirect('/login/');
}

$sql = sprintf("
    SELECT * FROM packages
    WHERE id = '%s'"
, $db->escape($package_id));
$package = $db->select_one($sql);

if (!$package) :
	?>
	
	<h2>Error</h2>
	<p>The SharURL id was not found.</p>
	
	<?php
elseif ($confirmed) :

	$upload = get_siteinfo('uploads') . DS . $package['token'];
	if (is_dir($upload)) {
		$files = glob($upload . DS . '*');
		foreach ($files as $file) @unlink($file);
		@rmdir($upload);
	}
	
	$download =  get_siteinfo('downloads') . DS . $package['alias'];
	if (is_dir($download)) {
		$files = glob($download . DS . '*');
		foreach ($files as $file) @unlink($file);

		@rmdir($download);
	}
	else {
		@unlink($download);
	}

	$data = array(
		'status' => 'deleted',
		'updated' => $db->now()
	);
	$where = sprintf("`id` = '%s'", $package['id']);
	$db->update('packages', $data, $where);
	
	$_SESSION['deleted'] = serialize($package);
	$core->redirect('/account/');
	
else :
	?>
	
	<h2>Confirm Delete</h2>
	<p>Are you sure you want to delete this SharURL?</p>
	<p><a href="<?php echo ltrim($_SERVER['REQUEST_URI'], '/'); ?>/confirmed">Yes</a>&nbsp;&nbsp;&nbsp;<a href="/account/">No</a></p>
	
	<?php
endif;
?>
