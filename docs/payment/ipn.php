<?php
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
}
//post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen("ssl://" . get_setting('paypal_hostname'), 443, $errno, $errstr, 30);

if (!$fp) {
    do_ipn_error('Could not connect to Paypal for verification: ' . $errno . ' ' . $errstr);
}

fputs($fp, $header . $req);
$res = '';
while (!feof($fp)){
    $res .= fgets($fp);
}
fclose($fp);
$parts = preg_split("*\r\n\r\n*", $res);


if ($parts[1] != "VERIFIED") {
    do_ipn_error('Not Verified: ' . $parts[1]);
}

if ($_POST['receiver_email'] != get_setting('paypal_business')) {
    do_ipn_error('Wrong business.');
}

if ($_POST['payment_status'] != 'Completed') {
    do_ipn_error('Payment status must be complete.');
}

parse_str($_POST['custom']);

if (!$plan_id || !$user_id) {
    do_ipn_error('Missing custom variable data.');
}

$sql = sprintf("SELECT * FROM users WHERE id = '%s'", $db->escape($user_id));
$user = $db->select_one($sql);

if (!$user) {
    do_ipn_error('User not found.');
}

$sql = sprintf("SELECT * FROM plans WHERE id = '%s'", $db->escape($plan_id));
$plan = $db->select_one($sql);

if (!$plan) {
    do_ipn_error('Plan not found.');
}

$correct_amount = number_format($plan['cost'], 2, '.', '');
if ($_POST['mc_gross'] != $correct_amount) {
    do_ipn_error('Incorrect amount for ' . $plan['name'] . ' plan.');
}

if ($_POST['mc_currency'] != 'USD') {
    do_ipn_error('Incorrect currency.');
}

$sql = "SELECT * FROM plans WHERE is_active = 1 AND cost = 0";
$free_plan = $db->select_one($sql);

// User already has active subscription?
if ($user['plan_id'] != $free_plan['id'] && time() < strtotime($user['plan_expires'])) {
    $expires = strtotime($user['plan_expires']);
}
else {
    $expires = time();
}

$expires = strtotime('+1 year', $expires);

$data = array(
    'plan_id' => $plan['id'],
    'plan_expires' => $db->to_date($expires)
);
$where = sprintf("`id` = '%s'", $db->escape($user['id']));
$db->update('users', $data, $where);

save_ipn_post('Success');
$ipn_id = $db->insert_id();

$data = array(
    'payments' => array(
        'user_id' => $user['id'],
        'plan_id' => $plan['id'],
        'ipn_id' => $ipn_id
    )
);
$db->insert($data);
?>