<?php

	// WooCommerce Rest API Client
	require_once ('vendor/autoload.php');

    use Automattic\WooCommerce\Client;
    use Automattic\WooCommerce\HttpClient\HttpClientException;

	if ( ! function_exists( 'biz_odoo_api_admin_enqueue_scripts' ) ):
		/**
		 * Adds the version of a package to the $jetpack_packages global array so that
		 * the autoloader is able to find it.
		 */
		function biz_odoo_api_admin_enqueue_scripts($hook)
		{
			if( in_array($hook, ["biz-solution_page_biz-odoo-api-page"] ) ):
				wp_register_style( 'biz-odoo-api-loading-bar-css', plugins_url('biz-odoo-api/assets/css/loading-bar.min.css'), false, '1.0.1' );
				wp_enqueue_style( 'biz-odoo-api-loading-bar-css' );

				wp_register_script( 'biz-odoo-api-loading-bar-script', plugins_url('biz-odoo-api/assets/js/loading-bar.min.js'), array('jquery'), '1.0.1' );
				wp_enqueue_script( 'biz-odoo-api-loading-bar-script' );

				wp_register_script( "biz-odoo-api-main-script", plugins_url('biz-odoo-api/assets/js/main.js'), array('jquery') );
				wp_enqueue_script( 'biz-odoo-api-main-script' );
				wp_localize_script( 'biz-odoo-api-main-script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ))); 
			endif;
		}
		add_action( 'admin_enqueue_scripts', 'biz_odoo_api_admin_enqueue_scripts' );
	endif;



	add_action("wp_ajax_odoo_sync_action", "odoo_sync_action");
	add_action("wp_ajax_nopriv_auth_odoo_sync", "auth_odoo_sync");

	function odoo_sync_action()
	{
		if( !isset( $_POST['page_number'] ) && !isset( $_POST['stage'] ) )
		{
			exit("Page not found.");
		}
		if ( !wp_verify_nonce( $_REQUEST['nonce'], "biz_odoo_api_sync_nonce")) {
			exit("You are not authenticated.");
		}

		$stage = $_POST['stage'];


        $woocommerce = new Client(
            site_url(), 
            'ck_92c1bfaa76a8a8b2289b11f256034473b5549299', 
            'cs_ad82d7ca8059c7c5ba918c25438d2604f337b725',
            [
                'version' => 'wc/v3',
            ]
        );

		switch( $stage )
		{
			case "categories":
				$odoo_woo = new OdooWoocommerce();
				$result = $odoo_woo->sync_categories_from_doo();
				break;
			case "brands":
				$odoo_woo = new OdooWoocommerce();
				$result = $odoo_woo->sync_brands_from_doo();
				break;
			case "variable_products":
				$odoo_woo = new OdooWoocommerce();
				$result = $odoo_woo->sync_brands_from_doo();
				break;
			case "product_variations":
				break;
		}


		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$result = json_encode($result);
			echo $result;
		}
		else {
			header("Location: ".$_SERVER["HTTP_REFERER"]);
		}

		die();

	}

	function auth_odoo_sync() {
		echo "You must log in to vote";
		die();
	}