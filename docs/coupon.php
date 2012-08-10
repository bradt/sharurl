<?php

list($code) = $params;

$sql = sprintf("SELECT * FROM coupons WHERE code = '%s'", $db->escape($code));
$coupon = $db->select_one($sql);

$expired = false;
if (time() > strtotime($coupon['expires'])) {
    $expired = true;
}
elseif ($coupon) {
    $sql = sprintf("SELECT COUNT(*) as count FROM users WHERE coupon_code = '%s'", $db->escape($code));
    $used = $db->select_one($sql);

    $coupons_left = $coupon['limit'] - $used['count'];

    if ($coupons_left > 0) {
        $two_years = time()+2*365*24*60*60;
        setcookie('coupon', $code, $two_years, '/');
        
        $core->redirect('/pricing/');
    }
}
?>

<h2>Coupon Expired</h2>

<?php if ($expired) : ?>
<p>Sorry, this coupon has expired.</p>
<?php elseif (isset($coupons_left) && $coupons_left <= 0) : ?>
<p>Sorry, all the coupons have already been used.</p>
<?php else : ?>
<p>Sorry, the coupon code could not be found.</p>
<?php endif; ?>
