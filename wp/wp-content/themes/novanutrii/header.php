<!DOCTYPE HTML>
<html lang="pt-br">
<head>
<script type="text/javascript"> 
var $buoop = {}; 
$buoop.ol = window.onload; 
window.onload=function(){ 
 try {if ($buoop.ol) $buoop.ol();}catch (e) {} 
 var e = document.createElement("script"); 
 e.setAttribute("type", "text/javascript"); 
 e.setAttribute("src", "//browser-update.org/update.js"); 
 document.body.appendChild(e); 
} 
</script>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<link href="<?php bloginfo('template_directory'); ?>/style.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/ico" href="<?php bloginfo('template_directory'); ?>/favicon.ico" />
<title>Blog Novanutrii</title>
<script src="//use.typekit.net/ydr5gea.js"></script>
<script>try{Typekit.load();}catch(e){}</script>
<script src="<?php bloginfo('template_directory'); ?>/js/jquery.js"></script>
<script src="<?php bloginfo('template_directory'); ?>/js/javascript.js"></script>
<script src="<?php bloginfo('template_directory'); ?>/js/modernizr.custom.js"></script>
<script src="<?php bloginfo('template_directory'); ?>/js/jquery-1.9.1.min.js"></script>
<?php wp_head(); ?>
<?php flush(); ?>
</head>
<body>

<div id="full">
	<div id="wrapper">
		<div id="colunas12" class="no-bottom">
			<div class="top-bar">
				<div class="redes-sociais">
					<a href="<?php bloginfo('rss2_url'); ?>" target="_blank" alt="" title="" class="redes-sociais-logo opacity trans"><img src="<?php bloginfo('template_directory'); ?>/images/home/rss.png" /></a>
					<a href="https://www.facebook.com/novanutrii" target="_blank" alt="" title="" class="redes-sociais-logo opacity trans"><img src="<?php bloginfo('template_directory'); ?>/images/home/facebook.png" /></a>
					<a href="https://br.linkedin.com/company/nova-nutrii" target="_blank" alt="" title="" class="redes-sociais-logo opacity trans"><img src="<?php bloginfo('template_directory'); ?>/images/home/linkedin.png" /></a>
					<a href="https://plus.google.com/112475829131528725379/about" target="_blank" alt="" title="" class="redes-sociais-logo opacity trans"><img src="<?php bloginfo('template_directory'); ?>/images/home/googleplus.png" /></a>
				</div>
				<a class="loja-a" href="http://www.nutrii.com.br/"><p class="trans color-red loja bold">Voltar para Loja Virtual</p></a>
			</div>
		</div>
	</div>
</div>

<div id="full">
	<div class="header">
		<div class="header-content bg-menu">
			<div id="wrapper">
						<div class="logo"><a href="http://blog.nutrii.com.br/"><img src="<?php bloginfo('template_directory'); ?>/images/home/logo.png" /></a></div>
						<div class="menu-icon"><a id="show" href="javascript:void(0)" title="Menu"><img src="<?php bloginfo('template_directory'); ?>/images/home/menu-icon.png" /></a></div>
				<div id="menu">
					<div id="wrapper">
						<div id="colunas12" class="no-bottom">
							<div class="menu-icon-close"><a id="hide" href="javascript:void(0)" title="Menu"><img src="<?php bloginfo('template_directory'); ?>/images/home/menu-close.png" /></a></div>
						</div>
						<div id="colunas6">
							<div class="logo-menu">
								<a href="http://blog.nutrii.com.br/"><img src="<?php bloginfo('template_directory'); ?>/images/home/logo-menu.png" /></a>
							</div>
							<span class="line-menu bold">· · ·</span>
							<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Texto-Menu') ) : ?><?php endif; ?>
						</div>
						<div id="colunas6" class="no-bottom">
							<form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
								<input type="hidden" name="post_type" value="post" />
								<input type="text" class="search regular" value="" name="s" placeholder="Digite o que você procura e pressione enter">
								<input class="icone-lupa" type="submit" value="" />
							</form>
						</div>
						<ul class="category-link regular">
							<div id="colunas6">
								<?php wp_list_categories('&title_li=&show_option_none=&hide_empty=0&orderby=ID'); ?>
							</div>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

