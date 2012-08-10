<?php
$template = 'twocol';
add_css('pricing');

if (!$user) {
	$core->redirect('/login/');
}

$coupon = get_coupon();
?>

<h2>Upgrade</h2>

<ul class="plans">
	<?php
    $sql = "SELECT * FROM plans WHERE is_active = 1 AND cost > 0 ORDER BY cost DESC";
    $plans = $db->select($sql);
	
	foreach ($plans as $plan) :
		extract($plan);
		?>

		<li class="plan plan-<?php echo $slug; ?>">
			<h3><?php echo $name; ?></h3>
			<h4>
				<?php if ($coupon) : ?>
					<strike>$<?php echo $cost; ?>/year</strike>
					$<?php echo $cost * ($coupon['discount']/100); ?>/year
				<?php else: ?>
					$<?php echo $cost; ?>/year
				<?php endif; ?>
			</h4>
			<h5><?php echo $desc; ?></h5>
			<ul>
				<li><strong><?php echo $storage; ?> GB</strong> storage</li>
				<li><strong><?php echo $bandwidth; ?> GB</strong> monthly downloads</li>
			</ul>
			<p class="signup">
				<a href="<?php echo get_plan_paypal_url($plan, $user['id'], $coupon); ?>">Subscribe</a>
			</p>
		</li>
		
		<?php
	endforeach;
	
	?>
</ul>
