<?php

/**
 * Class to retrieve user info of the store.
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Targetingmantra_UserData' ) ) {
	class Targetingmantra_UserData {

		private $_helper;
		private $_mid;
		private $_customerData = array();

		public function __construct($helper) {
			$this->_helper = $helper;
			// callback function to get the catalog data.
			add_action( 'wp_ajax_nopriv_userData_callback_action', array($this, 'getuserData') );
			add_action( 'wp_ajax_userData_callback_action', array($this, 'getuserData') );
		}

		public function getuserData() {
			$response = new WP_Ajax_Response;
			if($this->_helper->checkAuth()) {
				// number - number of users to be returned.
				// offset - number of users to be skipped while returning array. 
				// Calclutaion of offset - We start entering pageNumber from 1, so subtract 1 while caluclating offset.
				$args = array(
						'number' => $this->_helper->getPageLimit(),
						'offset' => ($this->_helper->getPageLimit()*($this->_helper->getPage()-1))
				);
				$users = get_users($args);
				foreach($users as $user) {
					$user_data = $this->extract_user_data( $user );
					$response->add( array(
							'supplemental' => $user_data
					) );
				}
			}
			$response->send();
			exit();
		}

		private function extract_user_data( $user ) {
			$user_data = array(
					'user_id' => $user->ID,
					'display_name' => $user->display_name,
					'first_name' => $user->first_name,
					'last_name' => $user->last_name,
					'email_id' => $user->user_email
			);
			return $user_data;
		}
	}
}
