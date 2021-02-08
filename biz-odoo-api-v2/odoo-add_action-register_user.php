<?php

	use BizSolution\OdooAPI\OdooAPI;
	use BizSolution\OdooAPI\OdooWoocommerce;

	add_action('biz_customer_successfully_registered','register_odoo_user');
	function register_odoo_user($user_id)
	{
		$customer = get_user_by('id', $user_id);
		$phone = get_user_meta( $user_id, 'phone', true );
		
		$user_info = [
			"name" 			=> $customer->nickname,
			"phone" 		=> $phone,
			"email" 		=> $customer->user_email
		];



		$odoo_api = new OdooAPI();
		$msg = $odoo_api->create_customer( $customer->nickname, $phone, $customer->user_email );
		$response = json_decode($msg, true);

		if ( 
			!empty($response) && 
			isset($response["result"]) && 
			isset($response["result"]["success"]) && 
			$response["result"]["success"] == true
		){
			biz_write_log( 'OdooID: ' . $msg, 'user');
            update_user_meta( $user_id, 'odoo_id', $response["result"]["id"] );
			return true;
		}
		else{
			return false;
		}
	}