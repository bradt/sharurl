<?php
if ($user) {
	$core->redirect('/account/upgrade/');
}

add_css('pricing');

$coupon = get_coupon();
?>

<h2>Pricing</h2>

<div class="intro">
	<p class="trial">
		Sign up in only 20 seconds
	</p>
</div>

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
				<a href="/signup/<?php echo $slug; ?>">Signup</a>
			</p>
		</li>
		
		<?php
	endforeach;
	
	?>
</ul>

<?php
$sql = "SELECT * FROM plans WHERE is_active = 1 AND cost = 0";
$plan = $db->select_one($sql);
extract($plan);
?>

<h3>Looking for a FREE Account?</h3>
<p>
	Free accounts are limited to <?php echo $storage; ?> GB of
	storage space and <?php echo $bandwidth; ?> GB of monthly
	downloads. <strong><a href="/signup/<?php echo $slug; ?>">Sign Up</a></strong>
</p>

<h3>30-Day Money Back Guarantee</h3>
<p>
	If for any reason you wish to be refunded within 30-days from the date
	of purchase, we will issue a full refund.
</p>

<h3>More Upgrades Coming Soon</h3>
<p>
	More account upgrades like custom domains (i.e. yourdomain.com/fj332d) are
	in the works. Please subscribe to <a
	href="/blog/mailing-list/">our mailing list</a> or <a
	href="/blog/">our blog</a> to find out when new features are added.
</p>
