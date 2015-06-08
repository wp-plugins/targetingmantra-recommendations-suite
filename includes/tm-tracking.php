<?php
/**
 * Class to set the tracking pixels in the page.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Targetingmantra_Tracking' ) ) {
    class Targetingmantra_Tracking {
    	
    	private $_helper;
    	
    	public function __construct($helper) {
    		$this->_helper = $helper;
			add_action('wp_head',array( $this, 'generateViewEventApiCall' ));
			add_action('woocommerce_add_to_cart',array( $this, 'generateAddToCartEventApiCall'),10,6);
			add_action('woocommerce_thankyou', array($this,'generatePurchaseConfirmationApiCall'), 10, 1);
    	}
		
		/**
		 * set the parameters for thank you page(post purchase confirmation).
		 * @param int $orderId
		 */
		public function generatePurchaseConfirmationApiCall($orderId) {
			$trackingParamsData = '';
			$order = new WC_Order($orderId);
			$items = $order->get_items();
			$orderItemIds = '';
			foreach ( $items as $item ) {
				$productQty = $item['qty'];
				$productId = $item['product_id'];
				for($qty=1; $qty<=$productQty; $qty++) {
					$orderItemIds = $orderItemIds.$productId.',';
				}
			}
			$orderItemIds = substr( $orderItemIds, 0, - 1 );
			$this->insertParam( $trackingParamsData, 'pid', $orderItemIds);
			$this->insertParam( $trackingParamsData, 'eid', 2);
			$this->generateRecordEventApiCall($trackingParamsData);
		}
		 
		/**
		 * set the parameters for add to cart event.
		 *
		 */
		public function generateAddToCartEventApiCall($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
			$trackingParamsData = '';
			$this->insertParam ( $trackingParamsData,'pid', $product_id );
			$this->insertParam ( $trackingParamsData,'eid', 4 );
			$this->generateRecordEventApiCall($trackingParamsData);
		}
		 
		/**
		 * get the view parameters based on the page type.
		 * 
		 */
		public function generateViewEventApiCall() {
			$trackingParamsData = '';
			$pageInfo = $this->getPageInfo();
			switch($pageInfo) {
				case 'homePage':
					$this->setHomeViewParams($trackingParamsData);
					$this->generateRecordEventApiCall($trackingParamsData);
					break;
				case 'categoryPage':
					$this->setCategoryViewParams($trackingParamsData);
					$this->generateRecordEventApiCall($trackingParamsData);
					break;
				case 'productPage':
					$this->setProductViewParams($trackingParamsData);
					$this->generateRecordEventApiCall($trackingParamsData);
					break;
				default: break;
			}
		}
		

		/**
		 * insert the tracking pixel.
		 * set the customer id and mid required for recordEvent api
		 * @param object $trackingParamsData
		 */
		private function generateRecordEventApiCall($trackingParamsData) {
			$userId = $this->_helper->getUserId();
			$mid = $this->_helper->getMid();
			if($userId !== 0 ) {
				$this->insertParam( $trackingParamsData, 'cid', $userId);
			}
			$this->insertParam($trackingParamsData,'mid',$mid);
			echo '<img src="//na.api.targetingmantra.com/RecordEvent?'.$trackingParamsData.'" width="1" height="1"></img>';
		}
		
		/**
		 * Get the current page type.
		 * @return string
		 */
		private function getPageInfo() {
			$pageInfo;
			if (is_shop() || is_home() || is_front_page()) {
				$pageInfo = 'homePage';
			} elseif (is_product()) {
				$pageInfo = 'productPage';
			} elseif (is_product_category()) {
				$pageInfo = 'categoryPage';
			}
			return $pageInfo;
		}
		
		/**
		 * set the paremeters for view event on product page.
		 * @param string $trackingParamsData
		 */
		private function setProductViewParams(&$trackingParamsData) {
			$productId = $this->_helper->getProductId();
			$product = wc_get_product( $productId );
			$price = $this->_helper->get_regularPrice( $product );
			$specialPrice = $this->_helper->get_salePrice( $product );
			if($specialPrice) {
				$price = $specialPrice;
			}
			$isInStock = $this->_helper->isInStock($product);
			$this->insertParam ( $trackingParamsData,'prc', $price );
			$this->insertParam ( $trackingParamsData,'stk', $isInStock );
			$this->insertParam ( $trackingParamsData,'pid', $productId );
			$this->insertParam ( $trackingParamsData,'eid', 1 );
		}
		
		/**
		 * set the parameters for view event on home page.
		 * @param string $trackingParamsData
		 */
		private function setHomeViewParams(&$trackingParamsData) {
			$this->insertParam ( $trackingParamsData,'pid', 'homepage' );
			$this->insertParam ( $trackingParamsData,'eid', 1 );
		}
		
		/**
		 * set the parameters for view event on category page
		 * @param string $trackingParamsData
		 */
		private function setCategoryViewParams(&$trackingParamsData) {
			$cat_name = single_cat_title( '', false );
			$term = get_term_by('name', $cat_name, 'product_cat');
			$cat_id = $term->term_id;
			$this->insertParam ( $trackingParamsData,'pid', 'c' . $cat_id );
			$this->insertParam ( $trackingParamsData,'eid', 1 );
		}
		
		/**
		 * append the parameter key value pair to existing parameters.
		 * @param string $string
		 * @param string $key
		 * @param string $value
		 */
		private function insertParam(&$string,$key, $value) {
			$string = $string.$key . '=' . $value . '&';
		}
    }
}
