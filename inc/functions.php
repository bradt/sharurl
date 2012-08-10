<?php
function has_exceeded_bandwidth($user_id) {
    global $db;
    
    $sql = sprintf("SELECT * FROM users WHERE id = '%s'", $db->escape($user_id));
    $user = $db->select_one($sql);
    
    $sql = sprintf("SELECT * FROM plans WHERE id = '%s'", $db->escape($user['plan_id']));
    $plan = $db->select_one($sql);
    
    $used = get_bandwidth_used($user['id']);
    $allotted = convert_to_bytes($plan['bandwidth'] . ' GB');
    
    return ($used >= $allotted);    
}

function get_bandwidth_used($user_id) {
    global $db;
    
	$first_day = date('Y-m-01');
	$last_day = date('Y-m-' . date('t'));
	
	$sql = sprintf("
		SELECT SUM(d.bytes) FROM downloads d
			INNER JOIN packages p ON p.id = d.package_id
		WHERE p.user_id = '%s'
			AND d.`day` BETWEEN '%s' AND '%s'
	", $db->escape($user_id), $first_day, $last_day);
	return $db->select_var($sql);    
}

function do_ipn_error($memo = '') {
    save_ipn_post($memo);
    
    $to = $user['email'];
    $subject = 'Paypal IPN Error: ' . $memo;
    $message = print_r($_POST, true);
    
    $headers = "MIME-Version: 1.0\n";
    $headers .= 'From: ' . get_siteinfo('name') . ' <' . get_siteinfo('admin_email') . ">\n";
    $headers .= "Content-Type: text/plain; charset=\"" . get_siteinfo('charset') . "\"\n";

    mail($to, $subject, $message, $headers);

    exit;
}

function save_ipn_post($memo = '') {
    global $db;
    
    $columns = array('item_name', 'item_number',
        'payment_status', 'mc_gross', 'mc_currency', 'txn_id',
        'txn_type', 'receiver_email', 'payer_email', 'custom');
    
    $data = array();
    foreach ($columns as $col) {
        $data[$col] = $_POST[$col];
    }
    
    $data['memo'] = $memo;
    $data['raw_data'] = json_encode($_POST);
    $data['created'] = $db->now();
    
    $db->insert(array('ipn' => $data));
}

function get_coupon() {
    global $db;
    
    $coupon = false;
    if (isset($_COOKIE['coupon'])) {
        $sql = sprintf("SELECT * FROM coupons WHERE code = '%s'", $db->escape($_COOKIE['coupon']));
        $coupon = $db->select_one($sql);
        
        if (!$coupon || time() > strtotime($coupon['expires'])) {
            unset($_COOKIE['coupon']);
        }
    }
    
    return $coupon;
}

function get_plan_paypal_url($plan, $user_id, $coupon = false) {
    $amount = $plan['cost'];
    $item_name = get_siteinfo('name') . ' - ' . $plan['name'] . ' Plan';
    $custom = 'plan_id=' . $plan['id'] . '&user_id=' . $user_id;

    if ($coupon) {
        $amount = $amount * ($coupon['discount']/100);
    }
    
    return get_paypal_url($item_name, $custom, $amount);
}

function get_paypal_url($item_name, $custom, $amount) {
    $args = array(
        'cmd' => '_xclick-subscriptions',
        'business' => get_setting('paypal_business'),
        'item_name' => $item_name,
        'currency_code' => 'USD',
        'custom' => $custom,
        'no_shipping' => '1',
        'a3' => $amount,
        'p3' => '1',
        't3' => 'Y',
        'src' => '1',
        'sra' => '1',
        'return' => get_siteinfo('siteurl') . '/payment/success/',
        'notify_url' => get_siteinfo('siteurl') . '/payment/ipn/',
    );

    return 'http://' . get_setting('paypal_hostname') . '/cgi-bin/webscr?' . http_build_query($args);
}

function get_setting($key) {
    $settings = get_settings($key);
    if (count($settings) > 0) {
        return array_shift($settings);
    }
    return '';
}

function get_settings() {
    global $db;
    
    $args = func_get_args();
    
    $keys = array();
    foreach ($args as $arg) {
        $keys[] = $db->escape($arg);
    }

    $sql = "SELECT * FROM settings
        WHERE `key` IN ('" . join("','", $keys) . "')";
    $settings = $db->select($sql);
    
    $_settings = array();
    foreach ($settings as $setting) {
        $key = $setting['key'];
        $_settings[$key] = $setting['value'];
    }
    
    return $_settings;
}

function update_setting($key, $value) {
    global $db;
    
    $data = array(
        'value' => $value,
        'updated' => $db->now()
    );
    $where = sprintf("`key` = '%s'", $key);
    $db->update('settings', $data, $where);
}

function generate_alias($size = 5) {
    mt_srand();
    $possible_characters = "abcdefghijkmnopqrstuvwxyz234567890";
    
    $string = '';
    while(strlen($string) < $size) {
        $string .= substr($possible_characters, rand() % strlen($possible_characters),1);
    }
    
    return $string;
}

function generate_token($size = 0) {
    $token = sha1(uniqid(rand(), true));
    
    if ($size) {
        $token = substr($token, 0, $size);
    }
    
    return $token;
}

function get_sharurl($alias) {
    return get_siteinfo('siteurl') . '/' . $alias;
}

function get_sharurl_link($alias) {
    $url = get_sharurl($alias);
    return sprintf('<a href="%s">%s</a>', $url, $url);
}

function get_download_url($alias) {
    return get_siteinfo('dlurl') . '/fetch/' . $alias;
}

function pretty_filesize($size)
{
    $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
}

function convert_filesize($unit,$from,$to)
{
    $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

    list($pos1) = array_keys($sizes,strtoupper($from));
    list($pos2) = array_keys($sizes,strtoupper($to));

    $up = $pos1 < $pos2 ? true : false;

    for($i = $pos1; $i != $pos2; ($up ? $i++ : $i--))
    {
        if($up) { $unit = $unit / 1024; }
        else { $unit = $unit * 1024; }
    }

    return $unit;
}

function convert_to_bytes($size) {
    if (preg_match('@^([0-9]+) ?(KB|MB|GB|TB|PB)$@', $size, $matches)) {
        return convert_filesize($matches[1], $matches[2], 'B');
    }
    else {
        return false;
    }
}

function render_api($error, $output = array()) {
    if ($error) {
        $output['success'] = '0';
        $output['error_msg'] = $error;
    }
    else {
        $output['success'] = '1';
    }
    
    echo json_encode($output);
}

function login_setcookie($token) {
    $two_years = time()+2*365*24*60*60;
    setcookie('SHARURLID', $token, $two_years, '/');
}

function login_user($id) {
    global $db;
    
    $token = generate_token();

    $data = array(
        'token' => $token,
        'updated' => $db->now()
    );
    $where = sprintf("`id` = '%s'", $id);
    $db->update('users', $data, $where);

    login_setcookie($token);
}

function email_bandwidth_limit($package) {
    global $db;

    $sql = sprintf("SELECT * FROM users WHERE id = '%s'", $db->escape($package['user_id']));
    $user = $db->select_one($sql);

    $sql = sprintf("SELECT * FROM plans WHERE id = '%s'", $db->escape($user['plan_id']));
    $plan = $db->select_one($sql);
    
    ob_start();
    include(ROOT . '/inc/email/bandwidth_limit.php');
    $message = ob_get_clean();

    $to = $user['email'];
    $subject = 'Your monthly download limit has been reached';
    $message = wordwrap($message, 80, "\n");
    
    $headers = "MIME-Version: 1.0\n";
    $headers .= 'From: ' . get_siteinfo('name') . ' <' . get_siteinfo('admin_email') . ">\n";
    $headers .= "Content-Type: text/plain; charset=\"" . get_siteinfo('charset') . "\"";

    mail($to, $subject, $message, $headers);
}

function email_plan_expired($plan, $user) {
    ob_start();
    include(ROOT . '/inc/email/plan_expired.php');
    $message = ob_get_clean();

    $to = $user['email'];
    $subject = 'Your plan has expired';
    $message = wordwrap($message, 80, "\n");
    
    $headers = "MIME-Version: 1.0\n";
    $headers .= 'From: ' . get_siteinfo('name') . ' <' . get_siteinfo('admin_email') . ">\n";
    $headers .= "Content-Type: text/plain; charset=\"" . get_siteinfo('charset') . "\"\n";

    mail($to, $subject, $message, $headers);
}

function email_activation($user) {
    ob_start();
    include(ROOT . '/inc/email/activate.php');
    $message = ob_get_clean();

    $to = $user['email'];
    $subject = 'Activate your new account';
    $message = wordwrap($message, 80, "\n");
    
    $headers = "MIME-Version: 1.0\n";
    $headers .= 'From: ' . get_siteinfo('name') . ' <' . get_siteinfo('admin_email') . ">\n";
    $headers .= "Content-Type: text/plain; charset=\"" . get_siteinfo('charset') . "\"\n";

    mail($to, $subject, $message, $headers);
}

function email_reset_password($user) {
    ob_start();
    include(ROOT . '/inc/email/reset_password.php');
    $message = ob_get_clean();

    $to = $user['email'];
    $subject = 'Reset your password';
    $message = wordwrap($message, 80, "\n");
    
    $headers = "MIME-Version: 1.0\n";
    $headers .= 'From: ' . get_siteinfo('name') . ' <' . get_siteinfo('admin_email') . ">\n";
    $headers .= "Content-Type: text/plain; charset=\"" . get_siteinfo('charset') . "\"\n";

    mail($to, $subject, $message, $headers);
}


/**
 * Determines the difference between two timestamps.
 *
 * The difference is returned in a human readable format such as "1 hour",
 * "5 mins", "2 days".
 *
 * @since 1.5.0
 *
 * @param int $from Unix timestamp from which the difference begins.
 * @param int $to Optional. Unix timestamp to end the time difference. Default becomes time() if not set.
 * @return string Human readable time difference.
 */
function human_time_diff( $from, $to = '' ) {
	if ( empty($to) )
		$to = time();
	$diff = (int) abs($to - $from);
	if ($diff <= 3600) {
		$mins = round($diff / 60);
		if ($mins <= 1) {
			$mins = 1;
		}
		$since = sprintf(_n('%s min', '%s mins', $mins), $mins);
	} else if (($diff <= 86400) && ($diff > 3600)) {
		$hours = round($diff / 3600);
		if ($hours <= 1) {
			$hours = 1;
		}
		$since = sprintf(_n('%s hour', '%s hours', $hours), $hours);
	} elseif ($diff >= 86400) {
		$days = round($diff / 86400);
		if ($days <= 1) {
			$days = 1;
		}
		$since = sprintf(_n('%s day', '%s days', $days), $days);
	}
	return $since;
}

function _n($single, $plural, $number) {
    if ($number > 1)
        return sprintf($plural, $number);
    else
        return sprintf($single, $number);
}
?>
