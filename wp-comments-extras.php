<?php
/**
 * Plugin Name: WP Comments Extras
 * Plugin URI: https://github.com/Sidsector9/wp-comments-extras/
 * Description: This plugin adds voting feature to comments
 * Version: 1.0.0
 * Author: Siddharth Thevaril
 * Author URI: profiles.wordpress.org/nomnom99/
 * Text Domain: wce
 *
 * @package WCE
 */

/**
 * Loads all classes.
 *
 * @param string $class Class name.
 */
function wce_include_classes( $class ) {
	require_once 'includes/admin/' . $class . '.php';
}
spl_autoload_register( 'wce_include_classes' );

$wce_init = new WP_Comments_Extras();
$wce_admin_init = new WP_Comments_Extras_Admin_Settings();

