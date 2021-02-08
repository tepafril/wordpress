<?php

	use BizSolution\OdooAPI\OdooAPI;
	use BizSolution\OdooAPI\OdooWoocommerce;

	add_filter( 'woocommerce_add_to_cart_validation', 'validate_inventory_add_to_cart', 10, 5 );
	function validate_inventory_add_to_cart( $passed, $product_id, $quantity, $variation_id = '', $variations= '' )
	{
		if( is_user_logged_in() )
		{

			$product = wc_get_product( $product_id );
			$product_title = $product->get_title();
            $odoo_id = get_post_meta( $product_id, 'odoo_id', true );

			$odoo_api = new OdooAPI();
			$msg = $odoo_api->check_stock('', array(['id' => $odoo_id, 'qty' => $quantity]));
			biz_write_log( json_encode(['product_title' => $product_title, 'id' => $odoo_id, 'qty' => $quantity]) . ' - ' .$msg, 'check-stock');

			$response = json_decode( $msg, true);

			// do your validation, if not met switch $passed to false
			if ( 
				!empty($response) && 
				isset($response["result"]) && 
				isset($response["result"]["success"]) && 
				$response["result"]["success"] == true 
			){
				$passed = true;
			}
			else{
				$passed = false;
				wc_add_notice( __( '"'. $product_title . '" is out of stock.', 'textdomain' ), 'error' );
			}
			
		}
		return $passed;
	}




	add_action('woocommerce_checkout_process', 'validate_inventory_process_checkout');
	function validate_inventory_process_checkout()
	{
		$passed = true;
		$cart = WC()->cart->get_cart();
		$items = [];

		$items_to_checkout = [];

        foreach ($cart as $item => $values)
        {
            $_product = $values['data']->post;
			$product_id = $_product->ID;
			$product = wc_get_product( $product_id );
			$product_title = $product->get_title();
            $qty = $values['quantity'];
            $odoo_id = get_post_meta( $product_id, 'odoo_id', true );
            $items[] = ['id' => $odoo_id, 'qty' => $qty];
        }

		$odoo_api = new OdooAPI();
		$msg = $odoo_api->check_stock('', $items);

		biz_write_log($msg, 'woocommerce_checkout_process');

		$response = json_decode($msg, true);

		// do your validation, if not met switch $passed to false
		if (
			!empty($response) && 
			isset($response["result"]) && 
			isset($response["result"]["success"]) && 
			$response["result"]["success"] == true 
		){
			return true;
		}
		else{
			wc_add_notice( __( '"'. $product_title . '" is out of stock.', 'textdomain' ), 'error' );
			return false;
		}
	}