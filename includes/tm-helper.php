<?php

/**
 * Helper class of targeting Mantra.Loads settings class and returns the mid code
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists('TM_Helper')) {
	
	class TM_Helper {

		public function __construct() {
			 
			 // admin specific function
			 if( is_admin() ) {
			 	
			 	// Load settings class
			 	add_filter( 'woocommerce_get_settings_pages', array( $this, 'load_settings_class' ), 10, 1 );
			 	
			 	// Show notice on first install
			 	add_action('admin_notices', array( $this, 'tm_install_notice' ), 10 );
			 }
			 
			 // callback  function to check if secret code is entered in settings page.
			 add_action( 'wp_ajax_nopriv_is_secret_key_set_action', array($this, 'getSecretKeyResponse') );
			 add_action( 'wp_ajax_is_secret_key_set_action', array($this, 'getSecretKeyResponse') );
		}
		
		/**
		 * Load the settings class
		 * @param  array $settings
		 * @return array
		 */
		public function load_settings_class( $settings ) {
			$settings[] = include 'tm-settings.php';
			return $settings;
		}
		
		/**
		 * Show install message when plugin is activated
		 * @return void
		 */
		public function tm_install_notice() {
			if (get_option('woocommerce_tm_install_notice') == false) {
				$admin_url = admin_url();
				echo '<div class="update-nag fade"><p><strong>' .__( "Thanks for activating TargetingMantra plugin. Pease head over to the <a href='" . $admin_url . "admin.php?page=wc-settings&tab=targeting_mantra'>settings</a> page to configure the plugin.", 'wc-targetingMantra' ) . '</strong></p></div>';
				update_option( 'woocommerce_tm_install_notice', true );
			}
		}
		
		/**
		 * Get the response if the correct secret key is entered in settings page.
		 */
		public function getSecretKeyResponse() {
			$isAuth = $this->getCheckAuth();
			if($isAuth) {
				$this->enablePixelIntegration();
			} 
			$response = array( 
            	'data'=> $isAuth
            );
		
			wp_send_json($response);
		}
		
		/**
		 * Check if the secret key is entered in the settings tab.
		 *
		 * @return boolean
		 */
		public function isSecretKeySet() {
			if(get_option('woocommerce_tm_secret_key') == '') {
				return 0;
			} else {
				return 1;
			}
		}
		
		/**
		 * Check authentication of request
		 *
		 * @return boolean
		 */
		public function getCheckAuth()
		{
			$authKey = $this->getSecretKey ();
			$secretKey = $_GET['code'];
			if ($secretKey == '' or $secretKey != $authKey) {
				return 0;
			}
			return 1;
		}
		
		/**
		 * Check authentication of request
		 *
		 * @return boolean
		 */
		public function checkAuth()
		{
			$authKey = $this->getSecretKey ();
			$secretKey = $_POST['code'];
			if ($secretKey == '' or $secretKey != $authKey) {
				return 0;
			}
			return 1;
		}
		
		/**
		 * Get page limit
		 * 
		 * @return int
		 */
		public function getPageLimit() {
			return $_POST['pageLimit'];
		}
		
		/**
		 * Get page number
		 *
		 * @return int
		 */
		public function getPage()
		{
			return $_POST['pageNum'];
		}
		
		/**
		 * Get the mid of the store.
		 * @return string
		 */
		public function getMid() {
			$apiKey = get_option('woocommerce_tm_secret_key');
			$mid = substr ( $apiKey, 0, 6 );
			return $mid;
		}
		
		/**
		 * Check if the option to show targeting mantra widgets is enabled.
		 */
		public function isWidgetsEnabled() {
			return ('yes' === get_option('woocommerce_tm_widgets_enable'));
		}
		
		/**
		 * Check if the option to show targeting mantra pixel integration is enabled.
		 */
		public function isPixelIntegrationEnabled() {
			return get_option('woocommerce_tm_pixel_enable');
		}
		
		/**
		 * Get the user id of logged in customer
		 * @return integer
		 */
		public function getUserId() {
			$userId = get_current_user_id();
			return $userId;
		}
		
		/**
		 * get the product id of current page.
		 * @return integer
		 */
		public function getProductId() {
			$productId = get_the_ID();
			return $productId;
		}
		
		/**
		 * get if the product is in stock.
		 * @return integer
		 */
		public function isInStock($product) {
			$isInstock = $product->is_in_stock();
			if ($isInstock == "") {
				$isInstock = 0;
			}
			return $isInstock;
		}
		
		/**
		 * Get the regular price of the product.
		 * @param object $product
		 * @return number
		 */
		public function get_regularPrice($product) {
			if($product->product_type == 'variable') {
				return floatval( $product->get_variation_regular_price());
			}
			return floatval( $product->regular_price );
		}
		
		/**
		 * Get the sale price of the product.If not set,return the regular price.
		 * @param object $product
		 * @return number
		 */
		public function get_salePrice($product) {
			if($product->product_type == 'variable') {
				$price = floatval( $product->get_variation_sale_price() );
			} else {
				$price = floatval( $product->sale_price );
			}
			if (!$price) {
				$price = $this->get_regularPrice($product);
			}
			return $price;
		}
		
		/**
		 * get the child catId of current category id
		 * @return array
		 */
		public function getChildProducts($catId) {
			$subCatId = $catId;
			$args = array(
				'hierarchical' => 1,
				'show_option_none' => '',
				'hide_empty' => 0,
				'parent' => $catId,
				'taxonomy' => 'product_cat'
			);
			$subcats = get_categories($args);
			foreach ($subcats as $sc) {
				$subCatId = $subCatId.','.$this->getChildProducts($sc->term_id);			
			}
			return $subCatId;
		}
		
		/**
		 * Sets the option to enable targeting mantra pixel integration.
		 */
		private function enablePixelIntegration() {
			update_option( 'woocommerce_tm_pixel_enable', true );
		}
		
		/**
		 * Get the secret key of the store.
		 * @return string
		 */
		private function getSecretKey()
		{
			$apiKey = get_option('woocommerce_tm_secret_key');
			$secretKey = substr ( $apiKey, 6 );
			return $secretKey;
		}
	}
}
