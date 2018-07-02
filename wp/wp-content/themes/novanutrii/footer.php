<div id="full" class="bg-newsletter">
	<div id="wrapper">
		<div class="newsletter">
			<div id="colunas3" class="no-bottom">
				<p class="bold news-text">assine a nossa newsletter!</p>
			</div>
			<div id="colunas9" class="no-bottom">
				<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Newsletter') ) : ?><?php endif; ?>
			</div>
		</div>
	</div>
</div>

<div id="full">
	<div id="wrapper">
		<div class="footer">
			<div id="colunas6" class="no-bottom"><p class="regular copyright">© 2014 Novanutrii.  Todos os direitos reservados.</p></div>
			<div id="colunas6" class="no-bottom"><a href="http://www.mucciestudio.com.br/" target="_blank" alt="" title=""><span class="mucci-logo"><img class="trans opacity" src="<?php bloginfo('template_directory'); ?>/images/home/mucci.png" /></span></a></div>
		</div>
	</div>
</div>

<script src="<?php bloginfo('template_directory'); ?>/js/act.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="<?php bloginfo('template_directory'); ?>/js/cbpHorizontalMenu.min.js"></script>
<script>
var $container = $('#mansonry-grid');
$container.imagesLoaded( function() {
  $container.masonry({
	  itemSelector: '.item'
  });
});
</script>
<?php wp_footer(); ?>
</body>
</html>