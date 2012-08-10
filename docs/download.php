<?php
if ($package['status'] == 'deleted') :
    ?>

    <h2>Deleted by Owner</h2>
    <p>Sorry, the file <?php echo get_sharurl($package['alias']); ?> was deleted by its owner.</p>

    <?php
elseif (has_exceeded_bandwidth($package['user_id'])) :
    email_bandwidth_limit($package);
    ?>
    
    <h2>Monthly Download Limit Reached</h2>
    <p>
        Sorry, the owner of this file has reached their monthly download allowance.
        We've sent them an email letting them know someone is trying to download.
    </p>
    
    <?php    
else :
    
    $dl_link = get_download_url($package['alias']);
    
    add_head('<meta http-equiv="refresh" content="0;url=' . $dl_link . '"/>');
    ?>

    <h2>Thanks for using SharURL!</h2>
    <p>Your download should automatically begin in a few seconds, but if not
    try clicking the link below:</p>
    <p class="download-url"><a href="<?php echo $dl_link; ?>"><?php echo $dl_link; ?></a></p>
    <div class="download-invite">
        <h3>Want to share your files?</h3>
        <p>
            SharURL is a quick and easy way to share your files with friends and family.<br />
            <b><a href="/pricing/">Signup now!</a></b>
        </p>
    </div>
    <?php
endif;
?>