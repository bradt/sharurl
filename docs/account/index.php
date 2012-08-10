<?php
$template = 'twocol';
add_css('account');

if (!$user) {
	$core->redirect('/login/');
}
?>
<h2>Files</h2>
<?php
$sql = sprintf("
	SELECT * FROM packages WHERE user_id = '%s'AND status IN ('complete', 'deleted')
	ORDER BY created DESC, expires DESC", $user['id']);
$packages = $db->select($sql);

$bytes = 0;
foreach ($packages as $package) {
	if ($package['status'] == 'complete')
		$bytes += $package['size'];
}

$sql = sprintf("SELECT * FROM plans WHERE id = '%s'", $db->escape($user['plan_id']));
$plan = $db->select_one($sql);

$used = convert_filesize($bytes, 'B', 'GB');
?>

<p>
<b><?php echo $plan['name']; ?> Plan</b> <span style="font-size: 0.8em;">(<a href="upgrade/">Upgrade now</a>)</span><br />
Storage In Use: <b><?php echo number_format($used,2) ?> GB of <?php echo number_format($plan['storage'],2); ?> GB</b><br />

<?php
$bytes = get_bandwidth_used($user['id']);
$used = number_format(convert_filesize($bytes, 'B', 'GB'), 2);
?>

Downloads This Month: <b><?php echo $used ?> GB of <?php echo number_format($plan['bandwidth'],2); ?> GB</b>
</p>

<?php
if (empty($packages)):
	?>
	
	<p>You haven't uploaded any files yet. <a href="/">Start sharing files now &raquo;</a></p>
	
	<?php
else :
	if (isset($_SESSION['deleted'])) :
		$package = unserialize($_SESSION['deleted']);
		?>
		<p class="notice">Successfully deleted <?php echo get_sharurl($package['alias']); ?></p>
		<?php
		unset($_SESSION['deleted']);
	endif;
	?>

	<table class="urls" width="100%" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th class="created">Created</th>
			<th class="url">SharURL / Files</th>
			<th class="size">Size</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$i = 0;
		$domain = str_replace('http://', '', get_siteinfo('siteurl'));
		foreach($packages as $package) :
			$url_show = $domain . '/' . $package['alias'];
			$url = get_sharurl($package['alias']);
			$options = unserialize($package['options']);
			if (isset($options['title']) && $options['title']) {
				$title = $options['title'];
			}
			else {
				$title = '';
			}
			
			$sql = sprintf("
				SELECT * FROM files WHERE package_id = '%s'
				ORDER BY filename", $package['id']);
			$files = $db->select($sql);
			
			$row_class = array();
			if ($package['status'] == 'deleted')
				$row_class[] = 'deleted';
			if ($i % 2 == 0)
				$row_class[] = 'stripe';
			?>
			<tr<?php echo (!empty($row_class)) ? ' class="' . join(' ', $row_class) . '"' : ''; ?>>
				<td valign="top" class="created">
					<?php echo date('M j\<\b\r\/\>Y', strtotime($package['created'])); ?>
				</td>
				<td class="details">
					<?php echo ($title) ? $title . '<br />' : ''; ?>
					<a href="<?php echo $url; ?>" class="url"><?php echo $url_show; ?></a><br />
					<ul class="files">
					<?php
					$i = 1;
					foreach ($files as $file) {
						$style = ($i > 3) ? ' style="display: none;"' : '';
						$comma = ($i != 1) ? ', ' : '';
						printf('<li%s>%s %s</li>', $style, $comma, $file['filename']);
						$i++;
					}
					?>
					</ul>
				</td>
				<td class="size">
					<?php echo pretty_filesize($package['size']); ?>
				</td>
				<td class="delete">
					<?php if ($package['status'] != 'deleted') : ?>
					<a href="/account/url/delete/<?php echo $package['id']; ?>">Delete</a>
					<?php endif; ?>
				</td>
			</tr>
			<?php
			$i++;
		endforeach;
		?>
	</tbody>
	</table>
	
	<?php
endif;
?>
