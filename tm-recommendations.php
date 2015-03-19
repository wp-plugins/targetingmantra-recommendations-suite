<?php
/**
 Plugin Name: TargetingMantra Recommendations Suite
 Plugin URI: http://www.woothemes.com/products/targetingMantra-recommendations/
 Description: Get recommendations for your store - Cross-sell, Up-sell & get higher conversion from targetingMantra's 17 widgets
 Author: TargetingMantra
 Author URI: http://targetingmantra.com/
 Version: 1.0

 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	function tm_activation_hook() {
		update_option( 'woocommerce_tm_install_notice', false );
		update_option( 'woocommerce_tm_widgets_enable', false );
		update_option( 'woocommerce_tm_pixel_enable', false );
		update_option( 'woocommerce_tm_secret_key', '');
	}
	register_activation_hook( __FILE__, 'tm_activation_hook' );
	
	require_once( 'includes/tm-helper.php' );
	require_once( 'includes/tm-catalog.php' );
	require_once( 'includes/tm-tracking.php' );
	require_once( 'includes/tm-widgets.php' );
	
	$helper = new TM_Helper();
	if($helper->isSecretKeySet()) {
		new Targetingmantra_Catalog($helper);
		if($helper->isPixelIntegrationEnabled()) {
			new Targetingmantra_Tracking($helper);
			if ($helper->isWidgetsEnabled()) {
				new Targetingmantra_Widgets($helper);
			}
		}
	}
}
