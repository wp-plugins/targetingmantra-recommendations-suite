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
						'title' => __( 'Number of widgtes to be displayed', 'wc-targetingMantra' ),
						'name' => __( 'Number of widgtes to be displayed', 'wc-targetingMantra' ),
						'desc' => __( 'Enter the number of widgets to be displayed' ),
						'type' => 'text',
						'std'     => '8', // WooCommerce < 2.0
						'default' => '8', // WooCommerce >= 2.0
						'id' => 'woocommerce_widgets_limit'
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
