<?php
/**
 * Class to get the TargetingMantra Widgets.
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists( 'Targetingmantra_Widgets' )) {
	
	class Targetingmantra_Widgets {
		
		/**
		 * This hash array contains mappings between page identifiers
		 * to tm page id for widgets generation
		 *
		 * @var hashArray
		 */
		private $_pageIDs = array (
				"homePage" => "hp",
				"productPage" => "pp",
				"categoryPage" => "cp",
				"cartPage" => "ct",
				"thankyouPage" => "tp"
		);
		
		/**
		 * This hash array contains list of widgets availaible for
		 * each page types
		 *
		 * @var hashArray
		 */
		private $_widgetsEnabledMap = array (
				"homePage" => array (
						"hp-hban",
						"hp-dbar",
						"hp-na",
						"hp-rp",
						"hp-bs",
						"hp-rhf",
						"hp-rvi"
				),
				"productPage" => array (
						"pp-vsims",
						"pp-psims",
						"pp-csims",
						"pp-rhf",
						"pp-rvi"
				),
				"categoryPage" => array (
						"cp-bs",
						"cp-na",
						"cp-cr"
				),
				"cartPage" => array (
						"ct-csc"
				),
				"thankyouPage" => array (
						"tp-ppr",
						"tp-pr"
				)
		);
		
		private $_mid;
		private $_helper;
		private $_widgetParametersData = array();
		
		public function __construct($helper) {
			$this->_mid = $helper->getMid();
			$this->_helper = $helper;
			add_action('wp_head',array( $this, 'loadTargetingMantraScript' ));
			add_action('wp_head',array($this, 'setParams'));
		}
		
		/**
		 * Loads Targeting Mantra javascript
		 */
		public function loadTargetingMantraScript() {
			?>
			<script type="text/javascript">
			//<![CDATA[
				document.write(unescape("%3Cscript type=\"text/javascript\" src=\"http://d1gsqroy9pf3oi.cloudfront.net/javascripts/tm-javascript.min.js\" charset=\"utf-8\" %3E%3C/script%3E"));
			//]]>
			</script>
			<?php
		}

		/**
		 * set the widget parameters based on the page type.
		 */
		public function setParams() {
			$widgetParametersData = '';
			$userId = $this->_helper->getUserId();
			$pageId = $this->getPageId();
			if($userId != 0) {
				$this->insertParam ( 'cid', $userId );
			}
			$this->insertParam ( 'pg', $pageId );
			$this->insertParam( 'limit', get_option('woocommerce_widgets_limit'));
			$pageInfo = $this->getPageInfo();
			switch ($pageInfo) {
				case 'homePage' :
					add_action('woocommerce_after_main_content',array($this, 'generateWidgets'));
					break;
				case 'categoryPage':
					add_action('woocommerce_after_main_content',array($this, 'setCategoryWidgetParams'),90);
					break;
				case 'productPage':
					add_action('woocommerce_after_main_content',array($this, 'setProductWidgetParams'),90);
					break;
				case 'cartPage':
					add_action('woocommerce_after_cart',array( $this, 'setCartWidgetParams' ));
					break;
				case 'thankyouPage':
					add_action('woocommerce_thankyou', array($this,'setCheckoutWidgetParams'), 10, 1);
					break;
			}
		}
		
		/**
		 * insert the targetingMantra divs on the page and create call to javascript function to get the widget.
		 */
		public function generateWidgets() {
			$this->insertEmptyDiv();
			?>
			<script type="text/javascript">
			//<![CDATA[
			TMJS.init( <?php echo $this->getMid() ?>,'na');
			TMJS.generateWidgets(<?php echo json_encode($this->_widgetParametersData)?> );
			//]]>
			</script>
			<?php 
		}
		

		/**
		 * create the widget call for product page after main content is loaded
		 */
		public function setProductWidgetParams() {
			$productId = $this->_helper->getProductId();
			$this->insertParam ( 'pid', $productId );
			$this->generateWidgets();
		}
		
		/**
		 * create the widget call for category page after main content is loaded
		 */
		public function setCategoryWidgetParams() {
			$cat_name = single_cat_title( '', false );
			$term = get_term_by('name', $cat_name, 'product_cat');
			$cat_id = $term->term_id;
			$this->insertParam ( 'catid', $cat_id );
			$this->generateWidgets();
		}
		
		/**
		 * create the widget call for cart page after cart content is loaded
		 */
		public function setCartWidgetParams() {
			$cartItemIdsString = '';
			foreach ( WC()->cart->get_cart() as $cart_items ) {
				$productQty = $cart_items['quantity'];
				$productId = $cart_items['product_id'];
				for( $qty=1; $qty<=$productQty; $qty++ ) {
					$cartItemIdsString = $cartItemIdsString.$productId.',';
				}
			}
			$cartItemIdsString = substr( $cartItemIdsString, 0, - 1 );
			$this->insertParam ( "es", $cartItemIdsString );
			$this->generateWidgets();
		}
		
		/**
		 * create the widget call for thankyou page.
		 */
		public function setCheckoutWidgetParams($orderId) {
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
			$this->insertParam ( 'es', $orderItemIds );
			$this->generateWidgets();
		}
		
		/**
		 * Get the current page type.
		 * @return string
		 */
		private function getPageInfo() {
			$pageInfo;
			if (is_shop() || is_home()) {
				$pageInfo = 'homePage';
			} elseif (is_product()) {
				$pageInfo = 'productPage';
			} elseif (is_product_category()) {
				$pageInfo = 'categoryPage';
			} elseif (is_cart()) {
				$pageInfo = 'cartPage';
			} elseif (is_order_received_page()) {
				$pageInfo = 'thankyouPage';  // page after receiving order
			} 
			return $pageInfo;
		}
		
		/**
		 * Get the page id of the current page.
		 * @return string|hashArray
		 */
		private function getPageId() {
			$currentPageInfo = $this->getPageInfo();
			if (! array_key_exists ( $currentPageInfo, $this->_widgetsEnabledMap ))
				return "ot";
			return $this->_pageIDs [$currentPageInfo];
		}
		
		/**
		 * append the parameter key value pair to existing parameters.
		 * @param string $key
		 * @param string $value
		 */
		private function insertParam($key, $value) {
			$this->_widgetParametersData[$key] =$value;
		}
		
		/**
		 * insert targetingMantra divs based on page type
		 */
		private function insertEmptyDiv() {
			if ($this->isPageWidgetsEnabled ()) {
				$widgetsEnabled = $this->getWidgetTypes ();
				$pageId = $this->getPageId ();
				foreach ( $widgetsEnabled as $widgetIndex => $widgetType ) {
					echo "<div id=tm-$widgetType></div>";
				}
			}
		}
		
		/**
		 * Returns type of widgets enabled for current page
		 *
		 * @return string|hashArray
		 */
		public function getWidgetTypes()
		{
			$currentPageInfo = $this->getPageInfo ();
			if (! array_key_exists ( $currentPageInfo, $this->_widgetsEnabledMap ))
				return "";
			return $this->_widgetsEnabledMap [$currentPageInfo];
		}
		
		/**
		 * Checks if widgets were enabled for current page
		 *
		 * @return boolean
		 */
		private function isPageWidgetsEnabled()
		{
			if (strlen ( $this->_mid ) != 6 || ! $this->_helper->isWidgetsEnabled ())
				return false;
			$currentPage = $this->getPageInfo ();
			if (array_key_exists ( $currentPage, $this->_widgetsEnabledMap ))
				return true;
			return false;
		}
		
		/**
		 * Get the mid of the store.
		 */
		private function getMid() {
			return $this->_mid;
		}
	}
}
?>