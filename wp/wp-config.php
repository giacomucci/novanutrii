<?php
/** 
 * As configurações básicas do WordPress.
 *
 * Esse arquivo contém as seguintes configurações: configurações de MySQL, Prefixo de Tabelas,
 * Chaves secretas, Idioma do WordPress, e ABSPATH. Você pode encontrar mais informações
 * visitando {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. Você pode obter as configurações de MySQL de seu servidor de hospedagem.
 *
 * Esse arquivo é usado pelo script ed criação wp-config.php durante a
 * instalação. Você não precisa usar o site, você pode apenas salvar esse arquivo
 * como "wp-config.php" e preencher os valores.
 *
 * @package WordPress
 */

// ** Configurações do MySQL - Você pode pegar essas informações com o serviço de hospedagem ** //
/** O nome do banco de dados do WordPress */
define('DB_NAME', 'wordpress-db');

/** Usuário do banco de dados MySQL */
define('DB_USER', 'wordpress-user');

/** Senha do banco de dados MySQL */
define('DB_PASSWORD', 'Tec080bra');

/** nome do host do MySQL */
define('DB_HOST', 'localhost');

/** Conjunto de caracteres do banco de dados a ser usado na criação das tabelas. */
define('DB_CHARSET', 'utf8');

/** O tipo de collate do banco de dados. Não altere isso se tiver dúvidas. */
define('DB_COLLATE', '');

/**#@+
 * Chaves únicas de autenticação e salts.
 *
 * Altere cada chave para um frase única!
 * Você pode gerá-las usando o {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * Você pode alterá-las a qualquer momento para desvalidar quaisquer cookies existentes. Isto irá forçar todos os usuários a fazerem login novamente.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ')WQ|<dP;@/ b. hUj-V_%zIH+#<AS~NLbH(OA|UZS+U+LZ;{|WAn!OrX!ZXut[1u');
define('SECURE_AUTH_KEY',  '[H0LrS =[y#/*cH,N+eZu*^}A+F*hr~v+;,Zb|8Pw%om`cK.lW`?X%B-?-,9&]>u');
define('LOGGED_IN_KEY',    'Voua&[Py32,}h@NKs_rn]$&IU<`l#$7UFwq<XM)]1T>@0 |uvALbc{6Wvk{fXP!X');
define('NONCE_KEY',        '[h>,3).Q+u_ctu-IH{S)%VoEu:1Rxie}O;2E-lTNb{RBrYv;9Gb]!5A++p@Q2<.0');
define('AUTH_SALT',        ']+dTb]=U!,Iz/,.*,pD-fat|jl{lots>%Ii&>7U dh9{[f#X?N_qYWcap_Zj.yUb');
define('SECURE_AUTH_SALT', '%Y5bQk=`8P2HV+tFcZOx*Q-%[$TknT?n`PR$pp:Fyli1oT_-{C?!|<;Df[A.*d*6');
define('LOGGED_IN_SALT',   'B(#.rONWt3SwuB+9g]k0I&{J|AVIU+og~!@`z}Q{1a!r2-F/9uuaTUB?8p|,!vh4');
define('NONCE_SALT',       'lqU#?&C<))7%}6t|:A8z-uCzX^&sr&5f~ZP#Y>S+(qBaQ6q6N}Tw8pZG^?((QWC+');

/**#@-*/

/**
 * Prefixo da tabela do banco de dados do WordPress.
 *
 * Você pode ter várias instalações em um único banco de dados se você der para cada um um único
 * prefixo. Somente números, letras e sublinhados!
 */
$table_prefix  = 'wp_';


/**
 * Para desenvolvedores: Modo debugging WordPress.
 *
 * altere isto para true para ativar a exibição de avisos durante o desenvolvimento.
 * é altamente recomendável que os desenvolvedores de plugins e temas usem o WP_DEBUG
 * em seus ambientes de desenvolvimento.
 */
define('WP_DEBUG', false);

/* Isto é tudo, pode parar de editar! :) */

/** Caminho absoluto para o diretório WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
	
/** Configura as variáveis do WordPress e arquivos inclusos. */
require_once(ABSPATH . 'wp-settings.php');
