<form method="get" action="/activate/">
    <div class="field textbox">
        <label for="email">Activation Code</label>
        <input type="text" name="code" id="code" class="text" />
    </div>
    <input type="image" name="activate" src="<?php siteinfo('themeurl'); ?>/img/blank.gif" class="button button-submit" />
</form>
