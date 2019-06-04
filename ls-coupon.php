<?php
/*
Plugin Name: 小肥羊优惠券
Description: 提供小肥羊优惠券后台管理和RESTful API数据接口
Version: 0.1.0
Author: Uice Lu
Author URI: https://cecilia.uice.lu
License: GPLv2 or later
Text Domain: ls-coupon
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'LS_Coupon_VERSION', '0.1.0' );
define( 'LS_Coupon__MINIMUM_WP_VERSION', '4.8' );
define( 'LS_Coupon__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, array( 'LS_Coupon', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'LS_Coupon', 'plugin_deactivation' ) );

require_once( LS_Coupon__PLUGIN_DIR . 'class.ls-coupon.php' );
require_once( LS_Coupon__PLUGIN_DIR . 'class.ls-coupon-rest-api.php' );

add_action( 'rest_api_init', array( 'LS_Coupon_REST_API', 'init' ) );

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once( LS_Coupon__PLUGIN_DIR . 'class.ls-coupon-admin.php' );
	add_action( 'init', array( 'LS_Coupon_Admin', 'init' ) );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( LS_Coupon__PLUGIN_DIR . 'class.ls-coupon-cli.php' );
}
