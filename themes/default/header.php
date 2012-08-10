<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
    <meta http-equiv="Content-Type" content="text/html; charset=<?php siteinfo('charset'); ?>" />
	<meta name="generator" content="Really Simple Framework" />
    
    <title><?php page_title(); ?><?php siteinfo('name'); ?></title>
    
	<link type="text/css" rel="stylesheet" href="<?php siteinfo('themeurl'); ?>/css/default.css" media="screen" />
    <!--[if lt IE 7]>
	<link rel="stylesheet" type="text/css" href="<?php siteinfo('themeurl'); ?>/css/default-ie6.css" media="screen" />
    <![endif]-->

	<link rel="shortcut icon" href="<?php siteinfo('themeurl'); ?>/favicon.ico" />

    <script type="text/javascript" src="<?php siteinfo('themeurl'); ?>/js/swfupload.js"></script>
    <script type="text/javascript" src="<?php siteinfo('themeurl'); ?>/js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="<?php siteinfo('themeurl'); ?>/js/main.js"></script>

    <script type="text/javascript" src="<?php siteinfo('themeurl'); ?>/js/cufon-yui.js"></script>
    <script type="text/javascript" src="<?php siteinfo('themeurl'); ?>/js/myfont_900.font.js"></script>
	<script type="text/javascript">
		Cufon.replace('#body h2, #body h3');
	</script>

    <?php echo $site_append_head; page_head() ?>
</head>

<body>

<div id="wrapper">
<div id="wrapper2">
<div id="main">

<div id="header">
    <h1><a href="/">SharURL</a></h1>
    <ul class="nav">
        <?php
		global $request_file;
        $i = 0;
		$current = split('/', $request_file);
		$current = !empty($current) ? $current[0] : '';
		$newnav = $nav;
		unset($newnav['account']);
        foreach ($newnav as $key => $item) :
            $link = get_siteinfo('siteurl') . '/';
            $link .= ($item['link']) ? $item['link'] . '/' : '';
            $i++;
            ?>
            <li class="<?php echo ($current == $item['link']) ? 'current' : ''; echo (count($nav) == $i) ? ' last' : ''; ?>">
                <a href="<?php echo $link; ?>"><?php echo $item['title']; ?></a></li>
            <?php
        endforeach;
        ?>
    </ul>
	<div class="account">
		<?php global $user; if ($user): ?>
		<p  class="menu"><a href="/account/"<?php echo ($current == 'account') ? ' class="current"' : ''; ?>>My Account</a> | <a href="/logout/">Logout</a><br /><span class="user">Logged in as <?php echo htmlentities($user['email']); ?></span></p>
		<?php else: ?>
		<p class="signup">Don't have an account? <a href="/pricing/" class="signup-link">Signup!</a></p>
		<form action="/login/" method="post">
			<input type="hidden" name="submit" value="1" />
			<input type="hidden" name="redirect" value="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" />
			<input type="image" name="login" src="<?php siteinfo('themeurl'); ?>/img/blank.gif" class="button button-login" tabindex="3" />
			<input type="password" name="password" class="text password" title="Password" tabindex="2" />
			<input type="text" name="password-placeholder" class="text password grayed" value="Password" style="display: none;" tabindex="2" />
			<input type="text" name="email" class="text email" title="Email Address" tabindex="1" />
		</form>
		<?php endif; ?>
	</div>
</div>
