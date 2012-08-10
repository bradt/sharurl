<?php get_header(); ?>

<div id="body" class="with-sidebar page">

	<?php get_sidebar(); ?>	
    
    <div class="content-wrapper">
        <div class="content-top"></div>
        <div class="content">
			
			<h2>Blog</h2>
		
			<?php if (have_posts()) : ?>
		
				<?php while (have_posts()) : the_post(); ?>
		
					<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
						<h3><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
		
						<div class="entry">
							<?php the_content(); ?>
						</div>
					</div>
		
				<?php endwhile; ?>
		
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
