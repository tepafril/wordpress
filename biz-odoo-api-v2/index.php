<?php
/**
 * Plugin Name: Biz Odoo API
 * Description: This plugin is designed for WooCommerce app that sync data from/to Odoo through API
 * Version: 1.0.0
 * Author: Biz Solution Co., Ltd.
 * Author URI: https://bizsolution.com.kh
 * @package Biz Solution
 */

	define( 'BIZ_ODOO_API_PLUGIN_DIR', __DIR__ );
	
	require_once "inc/OdooAPI.php";
	require_once "inc/OdooWoocommerce.php";
	require_once "odoo-add_action-add_to_cart.php";
	require_once "odoo-add_action-process_checkout.php";
	require_once "odoo-add_action-register_user.php";
	require_once "wp-admin_enqueue_scripts.php";
	require_once "wp-add_submenu_page.php";
	

	use BizSolution\OdooAPI\OdooAPI;
	use BizSolution\OdooAPI\OdooWoocommerce;

    /**
     * Registering rest route /wp-json/odoo/products
     */
	add_action( 'rest_api_init', function(){
		register_rest_route( 'odoo', '/products', [
			'methods'       =>      "GET",
			'callback'      =>      'biz_odoo_api_products_callback',
			'permission_callback' => '__return_true'
		]);
	});



	/**
	 * Rest route callback /wp-json/odoo/products
	 */
	if( !function_exists("biz_odoo_api_products_callback") ):
		function biz_odoo_api_products_callback()
		{
			$odoo_woo = new OdooWoocommerce();
			$result = $odoo_woo->sync_products_with_odoo(10, 1);

			return $result;
		}
	endif;


	if (!function_exists('biz_write_log')) {

	    function biz_write_log($log, $type = 'debug') {
	    	ini_set( 'error_log', WP_CONTENT_DIR . '/'. $type .'.log' );
	        if (true === WP_DEBUG) {
	            if (is_array($log) || is_object($log)) {
	                error_log(print_r($log, true));
	            } else {
	                error_log($log);
	            }
	        }
	    }
	}




    
	



	
	

	