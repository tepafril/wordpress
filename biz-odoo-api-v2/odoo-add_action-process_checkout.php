<?php

	use BizSolution\OdooAPI\OdooAPI;
	use BizSolution\OdooAPI\OdooWoocommerce;

	add_action('woocommerce_order_status_processing', 'process_checkout_with_aba_payway_aim');
	function process_checkout_with_aba_payway_aim($order_id)
	{
		$order = wc_get_order( $order_id );
		$items_to_checkout = [];
		foreach ($order->get_items() as $item_key => $item ):

			$product        = $item->get_product(); 
			$product_price  = $product->get_price();
			$item_data    	= $item->get_data();
			$product_id   	= $item_data['product_id'];
			$quantity     	= $item_data['quantity'];

			$odoo_id = get_post_meta( $product_id, 'odoo_id', true );

			$items_to_checkout[] = [
				'product_id' 	=> (int) $odoo_id,
				'qty' 			=> $quantity,
				'price_unit' 	=> $product_price,
				'discount' 		=> 0,
			];

		endforeach;

		update_post_meta( $order_id, 'odoo_sale_order', 'pending' );
		update_post_meta( $order_id, 'odoo_confirm_sale_order', 'pending' );
		
		$user_id = get_post_meta($order_id, '_customer_user', true);
		$user_odoo_id = get_user_meta( $user_id, 'odoo_id', true );

		$odoo_api 	= new OdooAPI();
		$msg	 	= $odoo_api->process_checkout($user_odoo_id, 352, $items_to_checkout);
		// biz_write_log( $msg, 'process_checkout' );
		update_post_meta( $order_id, 'odoo_sale_order_log', $msg );

		$response 	= json_decode($msg, true);

		$_payment_method 	= get_post_meta( $order_id, '_payment_method', true );


		if ( 
			!empty($response) && 
			isset($response["result"]) &&
			isset($response["result"]["success"]) &&
			$response["result"]["success"] == true
		){

			$order_odoo_id = $response["result"]["id"];
			update_post_meta( $order_id, 'odoo_id', $order_odoo_id );
			update_post_meta( $order_id, 'odoo_sale_order', 'success' );
			// biz_write_log( $_payment_method, 'confirm_sale_order' );

			if($_payment_method == 'aba_payway_aim')
			{
				$aba_transaction_id = get_post_meta( $order_id, 'aba_transaction_id', true );
				if( aba_PAYWAY_AIM::checkTransaction( $order_id ) ){
					$odoo_api 	= new OdooAPI();
					$msg	 	= $odoo_api->confirm_sale_order( $order_odoo_id, $aba_transaction_id );
					// biz_write_log( $msg, 'confirm_sale_order' );
					update_post_meta( $order_id, 'odoo_confirm_sale_order_log', $msg );

					$response 	= json_decode($msg, true);

					if ( 
						!empty($response) && 
						isset($response["result"]) && 
						isset($response["result"]["success"]) && 
						$response["result"]["success"] == true
					){
						update_post_meta( $order_id, 'odoo_confirm_sale_order', 'success' );
						return true;
					}
					else{
						wc_add_notice( __( 'Payment was not successful, please contact our support.', 'textdomain' ), 'error' );
						return false;
					}
				}

			}
			else
			{
				return true;
			}
		}
		else{
			wc_add_notice( __( 'Checkout was not successful, please contact our support.', 'textdomain' ), 'error' );
			return false;
		}
	}
