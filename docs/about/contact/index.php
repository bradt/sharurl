<?php
$template = 'twocol';

include(ROOT . '/inc/contactform.php');
$form = new ContactForm();

if (isset($_GET['ajax'])) {
	$template = 'ajax';
}
?>

<h2>Contact</h2>

<form action="" method="post">

	<?php include('form.php'); ?>
	
</form>
