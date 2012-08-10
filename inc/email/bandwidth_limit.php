Someone tried downloading the file <?php echo get_sharurl($package['alias']); ?>, but your monthly download limit has been reached.

Current Plan: <?php echo $plan['name'], "\r\n"; ?>
Monthly Downloads: <?php echo $plan['bandwidth']; ?> GB

You may upgrade your plan at <?php echo get_siteinfo('siteurl'), '/account/upgrade/'; ?>
