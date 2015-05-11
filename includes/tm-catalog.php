<?php

/**
 * Class to retrieve the catalog of the store.
 * 
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Targetingmantra_Catalog' ) ) {
	class Targetingmantra_Catalog {
		
		private $_helper;
		
		public function __construct($helper) {
			$this->_helper = $helper;
			// callback function to get the catalog data.
			add_action( 'wp_ajax_nopriv_catalog_callback_action', array($this, 'getcatalogData') );
			add_action( 'wp_ajax_catalog_callback_action', array($this, 'getcatalogData') );
		}
		
		public function getcatalogData() { 
			$response = new WP_Ajax_Response;
			if($this->_helper->checkAuth()) {
				// post-type : retrieve all posts of type product.
				$args = array(
					'post_type' => 'product',
					'posts_per_page' => $this->_helper->getPageLimit(),
					'paged'         => $this->_helper->getPage(),
					'post_status' => 'publish',
				);
				$loop = new WP_Query( $args );
				while ( $loop->have_posts() ) {
					$loop->the_post();
					$product = get_product( get_the_ID() );
					$product_data = $this->extract_product_data( $product );
					$response->add( array(
							'supplemental' => $product_data
					) );
				}
			}
			$response->send();
			exit();
		}
		
		private function extract_product_data( $product ) {
			$postdata = $product->get_post_data();	
			$subCategory = $this->get_subCategory($product->id);
			$category_tree = $this->get_category_tree($subCategory);
			$store = $category_tree[0];
			$product_name = get_the_title($product->id);
			$product_data = array(
				'product_id'        => $product->id,
				'product_name'      => $product_name,
				'parent_id'         => $this->get_parent($product),
				'content'           => $postdata->post_content,
				'title'	            => $product_name,
				'regular_price' 	=> floatval( $product->regular_price ),
				'sale_price'		=> $this->get_salePrice($product),
				'product_type'      => $product->product_type,
				'creation_date_gmt' => $postdata->post_date_gmt,
				'product_cat'		=> $this->get_cat_name( $subCategory ),
				'product_cat_id'	=> $subCategory,
				'product_tag_ids'	=> implode( ' , ', wp_get_object_terms( $product->id, 'product_tag', array( 'fields' => 'ids' ) ) ),
				'categories'        => implode('>', $category_tree),
				'custom_attributes' => $this->get_custom_attributes($product),
				'store'  			=> $store,
				'sku'               => $product->get_sku(),
				'url'               => $product->get_permalink(), // product page url
				'isInStock'         =>  $product->is_in_stock(),
				'product_image'     => $this->get_product_image( $product ),
				'average_rating'    =>	$product->get_average_rating( ),
				'is_virtual'     	=>  $product->is_virtual(),
				'is_downloadable'	=>  $product->is_downloadable(),
				'is_visible'        =>	$product->is_visible( ),
				'is_on_sale'        =>	$product->is_on_sale( ),
				'weight'            =>	$product->get_weight( ),
				//ignore these product data attributes for now
				'store_name'        => $this->get_cat_name( $store ),
				'content_filtered'  => $postdata->post_content_filtered,
				'price'             =>	$product->get_price( ),
				'dimensions'        =>	$product->get_dimensions( ),
				'stock_quantity'    =>	$product->get_stock_quantity(),
				'total_stock'       =>	$product->get_total_stock(),
				'menu_order'        =>	$postdata->menu_order,
				'product_status'    =>	$postdata->post_status,
				'post_modified_gmt' =>	$postdata->post_modified_gmt,
				'stock_individual'  =>  $product->is_sold_individually(),
				'stock_taxable'     =>  $product->is_taxable(),
				'stock_shipping_taxable' => $product->is_shipping_taxable(),
				'backorders_allowed'=>	$product->backorders_allowed( ),
				'is_featured'       =>	$product->is_featured( ),
				'rating_count'      =>	$product->get_rating_count( ),
				'shipping_class'    =>  $product->get_shipping_class( ),
				'shipping_class_id' =>  $product->get_shipping_class_id( ),
			);
			return $product_data;
		}
		
		/**
		 * Get the sale price of the product.If not set,return the regular price.
		 * @param object $product
		 * @return number
		 */
		private function get_salePrice($product) {
			$price = floatval( $product->sale_price );
			if (!$price) {
				$price = floatval( $product->regular_price );
			}
			return $price;
		}
		
		/**
		 * Get the category name from the category id.
		 * @param integer $cat_id
		 * @return string
		 */
		private function get_cat_name ($cat_id ) {
			$term = get_term_by('id', $cat_id, 'product_cat');
			$cat_name = $term->name;
			return $cat_name;
		}
		
		/**
		 * Get the parent product id of the product
		 * @param object $product
		 * @return integer
		 */
		private function get_parent ( $product ) {
			$ppId = $product->get_parent();
			if($ppId == 0) {
				$ppId = $product->id;
			}
			return $ppId;
		}
		
		/**
		 * Get the category id from the product id
		 * @param integer $productId
		 */
		private function get_subCategory ( $productId ) {
			$product_cat_id = wp_get_object_terms( $productId, 'product_cat', array( 'fields' => 'ids' ) );
			$parents = array();
			foreach($product_cat_id as $catId) {
				$parents = array_merge($parents, get_ancestors( $catId , 'product_cat' ));
			}		
			$lowest_level_product_cat_id = array_diff($product_cat_id,  $parents);
			$single_lowest_product_catId = array_slice($lowest_level_product_cat_id, 0, 1);
			return $single_lowest_product_catId[0];
		}
		
		/**
		 * Get the category tree from the category id
		 * @param integer $catId
		 * @return array
		 */
		private function get_category_tree( $catId ) {
			$catTree = array_reverse(get_ancestors( $catId , 'product_cat' ));
			array_push($catTree, $catId);
			return $catTree;
		}
		
		/**
		 * Get the product image.
		 * @param object $product
		 * @return String
		 */
		private function get_product_image( $product ) {
			if( has_post_thumbnail( $product->id ) ) {
				$image = $this->get_thumbnail_image($product->id);
				return $image[0];
			} else {
				$image = $this->get_gallery_images($product);
				return $image[0];
			}
		}
		
		/**
		 * Get the thumbnail image of the the product
		 * @param integer $id
		 * @return array
		 */
		private function get_thumbnail_image( $id ) {
			$img_urls = array();
			$img_urls[] = wp_get_attachment_url( get_post_thumbnail_id( $id ) );
			return $img_urls;
		}
		
		/**
		 * Get a list of product image urls
		 * @param  object $product
		 * @return array
		 */
		private function get_gallery_images( $product ) {
			$img_urls = array();
			$attachment_ids = $product->get_gallery_attachment_ids();
			if ( $attachment_ids ) {
				foreach ( $attachment_ids as $attachment_id ) {
					$img_urls[] = wp_get_attachment_url( $attachment_id );
				}
			}		
			return $img_urls;
		}
		
		/**
		 * Get the custom attributes of the product in form - name=value;;name=value
		 * @param object $product
		 * @return string
		 */
		private function get_custom_attributes( $product ) {
			$product_attr = $product->get_attributes();
			$attributes;
			foreach ($product_attr as $att) {
				$attributes = $attributes.$att['name']."=".$att['value'].";;";
			}
			return $attributes;
		}
	}	
}
