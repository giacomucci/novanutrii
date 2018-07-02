<!-- header -->
<?php get_header();?>

<div id="full">
	<div id="wrapper">
		<?php if (have_posts()): while (have_posts()) : the_post();?>
			<div class="inner-post">
				<div id="colunas12">
					<div class="inner-post-image"><?php echo get_the_post_thumbnail( $post_id, 'full' ); ?></div>
				</div>
				<div id="colunas12" class="no-bottom">
					<h1 class="inner-post-title bold"><?php the_title(); ?></h1>
				</div>
				<div id="colunas12" class="no-bottom">
					<span class="inner-post-info regular"><?php echo get_the_date(); ?>&nbsp; · &nbsp;<li class="trans color-orange"><?php the_category(' &gt; '); ?></li>&nbsp; · &nbsp;<li class="trans color-orange"><?php the_author_posts_link(); ?></li></span>
				</div>
				<div id="colunas12">
					<div class="inner-post-text image-post regular"><?php the_content(); ?></div>
				</div>
				<div id="colunas12">
					<div class="tags-title bold">Tags:</div>
					<div class="tags regular"><?php echo get_the_tag_list('<p>',', ','</p>'); ?></div>
				</div>
				<div id="colunas12">
					<?php comments_template(); ?>
				</div>
			</div>
		<?php endwhile; else:?>
		<?php endif;?>
	</div>
</div>

<div id="full" class="veja-bg">
	<div id="wrapper">
		<div id="colunas12" class="no-bottom">
			<h2 class="veja medium">Veja Também</h2>
		</div>
		<?php
		$related = get_posts( array( 'category__in' => wp_get_post_categories($post->ID), 'numberposts' => 5, 'post__not_in' => array($post->ID) ) );
		if( $related ) foreach( $related as $post ) {
		setup_postdata($post); ?>
		<div id="colunas3">
			<div class="veja-image"><a class="veja-title-a trans opacity" href="<?php the_permalink(); ?>"><?php echo get_the_post_thumbnail( $post_id, array( 350, 350) ); ?></div></a>
			<a class="veja-title-a" href="<?php the_permalink(); ?>"><h1 class="veja-title regular trans color-red"><?php the_title(); ?></h1></a>
		</div>
		<?php } wp_reset_postdata(); ?>
	</div>
</div>

<!-- footer -->
<?php get_footer();?>