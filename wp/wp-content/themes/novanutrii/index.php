<!-- header -->
<?php get_header();?>

<!-- Menu Estático -->
<div id="full">
	<div class="main-menu mobile-menu">
	    <div class="container">
	        <div id="wrapper">
				<div id="colunas6">
					<div class="logo-menu">
						<a href="http://blog.nutrii.com.br/"><img src="<?php bloginfo('template_directory'); ?>/images/home/logo-menu.png" /></a>
					</div>
					<span class="line-menu bold">· · ·</span>
					<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Texto-Menu') ) : ?><?php endif; ?>
				</div>
				<div id="colunas6">
					<form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
						<input type="hidden" name="post_type" value="post" />
						<input type="text" class="search regular" value="" name="s" placeholder="Digite o que você procura e pressione enter">
						<input class="icone-lupa" type="submit" value="" />
					</form>
				</div>
				<ul class="category-link regular">
					<div id="colunas6" class="no-bottom">
						<?php wp_list_categories('&title_li=&show_option_none=&hide_empty=0&orderby=ID'); ?>
					</div>
				</ul>
			</div>
	    </div>
	</div>
</div>
<!-- Menu Estático -->

<div id="full">
	<div id="wrapper">
		<div id="mansonry-grid">
		<?php if (have_posts()): while (have_posts()) : the_post();?>
		<div class="post item">
			<div id="colunas4">
				<div class="post-image"><a class="trans opacity" href="<?php the_permalink(); ?>"><?php echo get_the_post_thumbnail( $post_id, array( 350, 350) ); ?></a></div>
			</div>
			<div id="colunas8">
				<div class="post-bar">
					<span class="info medium"><?php echo get_the_date(); ?>&nbsp; · &nbsp;<li class="trans color-red"><?php the_category(' &gt; '); ?></li>&nbsp; · &nbsp;<li class="trans color-red"><?php the_author_posts_link(); ?></li></span>
				</div>
			</div>
			<div id="colunas8" class="no-bottom">
				<a href="<?php the_permalink(); ?>" class="post-title-a"><h1 class="post-title bold trans color-orange"><?php the_title(); ?></h1></a>
			</div>
			<div id="colunas8">
				<span class="post-line bold">...</span>
			</div>
			<div id="colunas8">
				<div class="post-text regular"><?php $content = get_the_content(); $resumo = substr($content, 0, 415).'...'; echo $resumo; ?></div>
			</div>
			<div id="colunas8" class="float-right">
				<a class="post-button-a" href="<?php the_permalink(); ?>"><div class="post-button medium trans background-orange">Continuar Lendo!</div></a>
			</div>
			<div class="clear"></div>
		</div>
		<?php endwhile; else:?>
		<?php endif;?>
			<div id="colunas12" class="nav-ajax">
				<div class="nav-previous"><?php next_posts_link( '' ); ?></div>
			</div>
		</div>
	</div>
</div>

<!-- footer -->
<?php get_footer();?>