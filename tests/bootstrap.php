<?php
/**
 * The following snippets uses `PLUGIN` to prefix
 * the constants and class names. You should replace
 * it with something that matches your plugin name.
 */

// define test environment
define( 'PLUGIN_PHPUNIT', true );

// define fake ABSPATH
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', sys_get_temp_dir() );
}

// define fake PLUGIN_ABSPATH
if ( ! defined( 'PLUGIN_ABSPATH' ) ) {
	define( 'PLUGIN_ABSPATH', sys_get_temp_dir() . '/wp-content/plugins/e20r-single-use-trial/' );
}

if ( ! require_once( __DIR__ . "/../inc/autoload.php" ) ) {
	print("Error: Cannot find the Composer autoloader!   " . __DIR__ .'/../inc/autoload.php');
	exit(1);
}

// Since our plugin files are loaded with composer, we should be good to go
