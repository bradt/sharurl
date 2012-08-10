<?php get_header(); ?>

<div id="body" class="with-sidebar">

    <?php get_sidebar(); ?>
    
    <div class="content-wrapper">
        <div class="content-top"></div>
        <div class="content">
        
            <?php get_content(); ?>
            
        </div>
        <div class="content-bottom"></div>
    </div>

</div>

<?php get_footer(); ?>
