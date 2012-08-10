<div id="sidebar">
<?php
global $request_file;
$pages = split('/', $request_file);
$page = !empty($pages) ? $pages[0] : '';
if (isset($nav[$page]['children'])) :
    ?>
    <div class="widget widget-nav">
        <div class="widget-wrapper">
            <ul class="nav">
            <?php
            foreach ($nav[$page]['children'] as $child) {
                $url = '/' . $page . '/';
                if ($child['link']) {
                    $url .= $child['link'] . '/';
                }
                
                if ($pages[0] == $child['link'] || $pages[1] == $child['link']) {
                    $current = ' class="current"';
                }
                else {
                    $current = '';
                }
                
                printf('<li%s><a href="%s">%s</a></li>', $current, $url, $child['title']);
            }
            ?>
            </ul>
        </div>
    </div>
<?php endif; ?>
</div>