<?php
add_css('home');
$template = 'home';
$page_title = 'Upload and send large files';

$is_mac = strpos($_SERVER['HTTP_USER_AGENT'], 'OS X') > -1;
?>

<form action="/upload/" method="post" enctype="multipart/form-data" class="file-upload"<?php echo ($is_mac) ? ' style="background-image: url(/themes/default/img/home/banner-mac.jpg);"' : ''; ?>>

    <p class="quote">
        It's kind of like TinyURL&trade; meets file sharing.  I love it! - Anthony, Seattle, USA
    </p>
    
    <ol class="steps">
        <li class="step step-1">Select files from your computer that you want to share by clicking the button below.</li>
        <li class="step step-2">Watch your files being uploaded, packaged, and your SharURL created.</li>
        <li class="step step-3">Share your SharURL with friends and family!</li>
    </ol>
    
    <div class="upload-button">
        <div id="upload-button"></div>
    </div>
        
    <div class="field" style="display: none;">
        <p>Click <b>Browse</b> to select a file to share...</p>
        <input type="file" name="Filedata" id="file" />
        <p><input type="submit" name="upload-file" value="Create my SharURL" /></p>
    </div>
        
</form>
