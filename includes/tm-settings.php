<?php

/**
 * Settings class of targeting Mantra.
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'TM_Settings' ) ) {

	class TM_Settings extends WC_Settings_Page {

		/**
	 	 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'targeting_mantra';
			$this->label = __( 'TargetingMantra', 'wc-targetingMantra' );

			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		}


		/**
	 	 * Get settings array
	 	 *
	 	 * @return array
	 	 */
		public function get_settings() {
			$account_text = "Your Secret Key.You can find this on your TargetingMantra Getting Started Page.";
			$notice_text = '';
			if (get_option('woocommerce_tm_secret_key') == '') {
				$notice_text = "<div class='error'>We see that you don't have your secret keys yet. Please <strong><a href='http://targetingmantra.com/users/sign_up?source=woocommerce' target='_blank'>sign up</a></strong> for your TargetingMantra account to get your keys!</div>";
			}

			return apply_filters( 'woocommerce_targetingMantra_settings', array(
				array(
						'type' => 'title', 
						'desc' => $notice_text,
						'id' => 'general_options'		
				),
				array(
						'title'		=> __( 'Secret Key', 'wc-targetingMantra' ),
						'name'		=> __( 'Secret Key', 'wc-targetingMantra' ),
						'desc'		=> __( $account_text , 'wc-targetingMantra' ),
						'id'		=> 'woocommerce_tm_secret_key',
						'type'		=> 'text',
				),
				array(
						'title' => __( 'Enable Targeting Mantra Widgets', 'wc-targetingMantra' ),
						'name' => __( 'Enable Targeting Mantra Widgets', 'wc-targetingMantra' ),
						'desc' => __( 'Tick the checkbox to show TargetingMantra widgets' ),
						'type' => 'checkbox',
						'id' => 'woocommerce_tm_widgets_enable'		
				),
				array(
						'title' => __( 'Number of items to be displayed in a widget', 'wc-targetingMantra' ),
						'name' => __( 'Number of items to be displayed in a widget', 'wc-targetingMantra' ),
						'desc' => __( 'Enter the number of items to be displayed' ),
						'type' => 'text',
						'std'     => '8', // WooCommerce < 2.0
						'default' => '8', // WooCommerce >= 2.0
						'id' => 'woocommerce_widgets_limit'
				),
				array(
						'title' => __( 'Action name for displaying widgets on shop page', 'wc-targetingMantra' ),
						'name' => __( 'Action name for displaying widgets on shop page', 'wc-targetingMantra' ),
						'type'        => 'select',
						'options'     => array(
								'woocommerce_before_main_content'=>('woocommerce_before_main_content'),
								'woocommerce_after_main_content'=>('woocommerce_after_main_content'),
								'woocommerce_before_shop_loop'=>('woocommerce_before_shop_loop'),
								'woocommerce_after_shop_loop'=>('woocommerce_after_shop_loop'),
								'woocommerce_archive_description'=>('woocommerce_archive_description'),
								'woocommerce_sidebar'=>('woocommerce_sidebar'),
								'none'=>('none')
						 ),
						'id' => 'woocommerce_tm_homepage_widgets'
				),
				array(
						'title' => __( 'Action name for displaying widgets on category page', 'wc-targetingMantra' ),
						'name' => __( 'Action name for displaying widgets on category page', 'wc-targetingMantra' ),
						'type'        => 'select',
						'options'     => array(
								'woocommerce_before_main_content'=>('woocommerce_before_main_content'),
								'woocommerce_after_main_content'=>('woocommerce_after_main_content'),
								'woocommerce_before_shop_loop'=>('woocommerce_before_shop_loop'),
								'woocommerce_after_shop_loop'=>('woocommerce_after_shop_loop'),
								'woocommerce_archive_description'=>('woocommerce_archive_description'),
								'woocommerce_sidebar'=>('woocommerce_sidebar'),
								'none'=>('none')
						),
						'id' => 'woocommerce_tm_category_widgets'
				),
				array(
						'title' => __( 'Action name for displaying widgets on product page', 'wc-targetingMantra' ),
						'name' => __( 'Action name for displaying widgets on product page', 'wc-targetingMantra' ),
						'type'        => 'select',
						'options'     => array(
								'woocommerce_after_main_content'=>('woocommerce_after_main_content'),
								'woocommerce_before_main_content'=>('woocommerce_before_main_content'),
								'woocommerce_after_single_product'=>('woocommerce_after_single_product'),
								'woocommerce_before_single_product'=>('woocommerce_before_single_product'),
								'woocommerce_after_single_product_summary'=>('woocommerce_after_single_product_summary'),
								'woocommerce_before_single_product_summary'=>('woocommerce_before_single_product_summary'),
								'woocommerce_sidebar'=>('woocommerce_sidebar'),
								'woocommerce_single_product_summary'=>('woocommerce_single_product_summary'),
								'none'=>('none')
						),
						'id' => 'woocommerce_tm_product_widgets'
				),
				array(
						'title' => __( 'Action name for displaying widgets on cart page', 'wc-targetingMantra' ),
						'name' => __( 'Action name for displaying widgets on cart page', 'wc-targetingMantra' ),
						'type'        => 'select',
						'options'     => array(
								'woocommerce_after_cart'=>('woocommerce_after_cart'),
								'woocommerce_after_cart_contents'=>('woocommerce_after_cart_contents'),
								'woocommerce_after_cart_table'=>('woocommerce_after_cart_table'),
								'woocommerce_after_cart_totals'=>('woocommerce_after_cart_totals'),
								'woocommerce_after_shop_loop_item'=>('woocommerce_after_shop_loop_item'),
								'woocommerce_after_shop_loop_item_title'=>('woocommerce_after_shop_loop_item_title'),
								'woocommerce_before_shop_loop_item'=>('woocommerce_before_shop_loop_item'),
								'woocommerce_before_shop_loop_item_title'=>('woocommerce_before_shop_loop_item_title'),
								'woocommerce_before_cart'=>('woocommerce_before_cart'),
								'woocommerce_before_cart_contents'=>('woocommerce_before_cart_contents'),
								'woocommerce_before_cart_table'=>('woocommerce_before_cart_table'),
								'woocommerce_before_cart_totals'=>('woocommerce_before_cart_totals'),
								'none'=>('none')
						),
						'id' => 'woocommerce_tm_cart_widgets'
				),
				array(
						'title' => __( 'Action name for displaying widgets on thankyou page', 'wc-targetingMantra' ),
						'name' => __( 'Action name for displaying widgets on thankyou page', 'wc-targetingMantra' ),
						'type'        => 'select',
						'options'     => array(
								'woocommerce_thankyou'=>('woocommerce_thankyou'),
								'woocommerce_order_details_after_order_table'=>('woocommerce_order_details_after_order_table'),
								'none'=>('none')
						),
						'id' => 'woocommerce_tm_thankyou_widgets'
					),
				array( 'type' => 'sectionend', 'id' => 'general_options')
			) ); 
		}

		/**
	 	* Save settings
	 	*/
		public function save() {
			$settings = $this->get_settings();
			WC_Admin_Settings::save_fields( $settings );
		}
	}
}

return new TM_Settings;
