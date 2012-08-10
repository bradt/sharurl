<?php get_header(); ?>

<div id="body" class="with-sidebar">

	<?php get_sidebar(); ?>	
    
    <div class="content-wrapper">
        <div class="content-top"></div>
        <div class="content">
			
			<h2>Blog</h2>
		
			<?php if (have_posts()) : ?>
		
				<?php while (have_posts()) : the_post(); ?>
		
					<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
						<div class="title-date">
							<h3><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
							<small><?php the_time('F jS, Y') ?> <!-- by <?php the_author() ?> --></small>
						</div>
		
						<div class="entry">
							<?php the_content(); ?>
						</div>
		
						<p class="postmetadata">
							Posted by <a href="http://bradt.ca/">Brad</a> |
							<?php the_tags('Tags: ', ', ', ''); ?>
							<?php
							/*
							Posted in <?php the_category(', ') ?>
							*/
							?>
							<?php edit_post_link('Edit', ' | ', ''); ?>
							<?php if (!is_single()) : ?> | <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); endif; ?>
						</p>
					</div>
		
				<?php endwhile; ?>
		
				<?php if (is_single()) : ?>

					<?php comments_template(); ?>
				
				<?php else : ?>
				
					<div class="navigation">
						<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
						<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
					</div>
				
				<?php endif; ?>
		
			<?php else : ?>
		
				<h2 class="center">Not Found</h2>
				<p class="center">Sorry, but you are looking for something that isn't here.</p>
				<?php get_search_form(); ?>
		
			<?php endif; ?>

        </div>
        <div class="content-bottom"></div>
    </div>

</div>

<?php get_footer(); ?>
