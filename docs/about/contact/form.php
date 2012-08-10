<?php if ($form->success) : ?>

<h3>Success!</h3>

<p>Your email has been delivered. You can expect a reply within 24 hours.</p>

<?php else : ?>

<p class="indicates"><span class="req">*</span> Indicates required fields</p>

<?php if (!empty($form->errors)) : ?>
<p class="error-msg">Please correct the errors below.</p>
<?php endif; ?>

<?php $form->display(); ?>

<script>
$('.field-isbot').hide();
$('#isbot_No').attr('checked', 'true');
</script>

<input id="contactsubmit" class="button button-submit" type="image" value="Submit" src="<?php siteinfo('themeurl'); ?>/img/blank.gif" name="Submit"/>

<?php endif; ?>
