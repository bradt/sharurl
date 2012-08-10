<?php
// Check for expired plans and notify users
$sql = "SELECT * FROM plans WHERE is_active = 1 AND cost = 0";
$free_plan = $db->select_one($sql);

$sql = sprintf("SELECT * FROM users WHERE plan_id != '%s'", $free_plan['id']);
$users = $db->select($sql);

foreach ($users as $user) {
    // if expired
    $two_days = 60*60*24*2; // Give them an extra couple of days to pay before disabling
    if (time() > strtotime($user['plan_expires']) + $two_days) {
        $sql = sprintf("SELECT * FROM plans WHERE id = '%s'", $user['plan_id']);
        $plan = $db->select_one($sql);
        
        email_plan_expired($plan, $user);
        
		$data = array(
            'plan_id' => $free_plan['id'],
            'updated' => $db->now()
		);
		$where = sprintf("`id` = '%s'", $db->escape($user['id']));
		$db->update('users', $data, $where);
    }
}
?>
