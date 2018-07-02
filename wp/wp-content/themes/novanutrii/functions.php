<?php 
/**
 * @package WordPress
 * @subpackage Novanutrii
 */

/*Adiciona Widget*/
if ( function_exists('register_sidebar') )
register_sidebar(array('name'=>'Texto-Menu',
'before_title' => '',
'after_title' => '',
'before_widget' => '',
'after_widget' => '',
));

/*Adiciona Widget*/
if ( function_exists('register_sidebar') )
register_sidebar(array('name'=>'Newsletter',
'before_title' => '',
'after_title' => '',
'before_widget' => '',
'after_widget' => '',
));

/*Editor de texto (somente html)*/
add_filter ( 'user_can_richedit' , create_function ( '$a' , 'return false;' ) , 50 );


/*Fotos com tamanho completo sem link*/
function remove_media_link( $form_fields, $post ) {

        unset( $form_fields['url'] );

              return $form_fields;

}
add_filter( 'attachment_fields_to_edit', 'remove_media_link', 10, 2 );
// check if the post has a Post Thumbnail assigned to it.
if ( has_post_thumbnail() ) {
	the_post_thumbnail('full');
} 
the_content();


/*Adiciona Menus Personalizados*/
add_theme_support('menus');

	register_nav_menus( array(
		'menu' => __( 'Menu', 'adaptive' )
	) );


/*Remove o wlw e o rsd*/
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');


/*Post image*/
if ( function_exists( 'add_theme_support' ) ) { 
add_theme_support( 'post-thumbnails' );
set_post_thumbnail_size( 350, 350, true ); // default Post Thumbnail dimensions (cropped)
}


/*Limite de excerpt*/
function custom_excerpt_length( $length ) {
	return 100;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );


//Login Stylesheet Login
function my_login_stylesheet() { ?>
    <link rel="stylesheet" id="custom_wp_admin_css"  href="http://blog.nutrii.com.br/wp-content/themes/novanutrii/includes/login/login-page.css" type="text/css" media="all" />
<?php }
add_action( 'login_enqueue_scripts', 'my_login_stylesheet' );

?>