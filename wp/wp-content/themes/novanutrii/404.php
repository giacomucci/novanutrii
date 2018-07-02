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
		<div id="colunas12">
			<div class="erro">
				<img src="<?php bloginfo('template_directory'); ?>/images/Novanutrii-Imagem404.png" />
			</div>
		</div>
	</div>
</div>

<!-- footer -->
<?php get_footer();?>