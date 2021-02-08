<?php

	namespace BizSolution\OdooAPI;

	class OdooAPI
	{
		private $url = 'http://124.248.186.25:8068';
		private $client_id = 'gt9IN3QvGVcF95AOKSSRAaa73zc39OoInl5n6qFUE7z5JuQ8aYG53D5wzdine3rpy8EbFjE9V8B176LgYDEPXdU4X10hsdV1bWEZgklnaAjFmh4dCgRennQ3nOJoK1oF';
		private $client_secret = '7kZZWrzAsbsMloVn24uTjQ3KQ1nIBva3VII3IqTLv3sBLyMFr24zDC8JIAFrd9jY0M7vBvb3GcUld20fvDhdaIxKGPcGPTtkyLJSuRaf7Cwd0M2OpLKZsKTxO6RKV9TY';
		private $db = '5q6ONleOaO1TypkFWDncCNM0ouw61tiwlfkPgrIAubPYVeKNPIrfpFFYMLMs25llqhhkekcnafdPvvqtK93lfwMPDYoa1HmMCrD4rvasqI18YaadH23WE9EzldWUJC8TdJFkAQrRzSz';
		private $token;

		public function __construct()
		{
			$uri  = '/client/api/oauth2/access_token';
			$postfields = 'client_id=' . $this->client_id . '&client_secret=' . $this->client_secret . '&db=' . $this->db;
			$response = $this->post( $uri, $postfields );
			
			$this->token = json_decode($response, true);
		}
		
		public function create_customer( $nickname, $phone, $email )
		{
			$uri  = "/api/customer/create";
			$postfields = '{
				"params": {
					"name": 	"'. $nickname .'",
					"phone": 	"'. $phone .'",
					"email": 	"'. $email .'",
					"db": 		"'. $this->db .'"
				}
			}';

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->url . $uri,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_HTTPHEADER => array(
					"Authorization: Bearer " . $this->token["access_token"],
				    'Content-Type: application/json'
				),
				CURLOPT_POSTFIELDS => $postfields,
			));

			$response = curl_exec($curl);

			curl_close($curl);

			return $response;
		}
		
		public function check_stock( $phone, $products )
		{
			$product_json = json_encode($products);
			$uri  = "/api/check/product";
			$postfields = '{
				"params":{
					"db": "'. $this->db .'",
					"phone": "'. $phone .'",
					"products": '.$product_json.'
				}
			}';

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->url . $uri,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_HTTPHEADER => array(
				    'Content-Type: application/json'
				),
				CURLOPT_POSTFIELDS => $postfields,
			));

			$response = curl_exec($curl);

			curl_close($curl);

			return $response;
		}
		
		public function process_checkout( $partner_id, $address_id, $products )
		{
			$product_json = json_encode($products);
			$uri  = "/api/create/sale_order";
			$postfields = ["params"=>[
				"partner_id" => $partner_id,
				"address_id" => $address_id,
				"detail" => $products,
				"db" => $this->db,
			]];
			$postfields = json_encode($postfields);

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->url . $uri,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_HTTPHEADER => array(
					"Authorization: Bearer " . $this->token["access_token"],
				    'Content-Type: application/json'
				),
				CURLOPT_POSTFIELDS => $postfields,
			));

			$response = curl_exec($curl);

			curl_close($curl);

			return $response;
		}
		
		public function confirm_sale_order( $order_odoo_id, $aba_transaction_id )
		{
			$uri  = "/api/confirm/sale_order/" . $order_odoo_id;
			$postfields = ["params"=>[
				"payment_id" 			=> 26,
				"aba_transaction_id" 	=> $aba_transaction_id,
				"db" => $this->db,
			]];
			$postfields = json_encode($postfields);

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->url . $uri,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_HTTPHEADER => array(
					"Authorization: Bearer " . $this->token["access_token"],
				    'Content-Type: application/json'
				),
				CURLOPT_POSTFIELDS => $postfields,
			));

			$response = curl_exec($curl);

			curl_close($curl);

			return $response;
		}
		
		public function get_categories()
		{
			$uri  = "/api/get/product_category";
			$postfields = 'db=' . $this->db;
			$response = $this->get( $uri, $postfields, $this->token["access_token"] );
			return $response;
		}
		
		public function get_brands()
		{
			$uri  = "/api/get/brand";
			$postfields = 'db=' . $this->db;
			$response = $this->get( $uri, $postfields, $this->token["access_token"] );
			return $response;
		}
		
		public function get_products( $take = 10, $page = 1)
		{
			$uri  = "/api/get/product?page=$page&take=$take";
			$postfields = 'db=' . $this->db;
			$response = $this->get( $uri, $postfields, $this->token["access_token"] );

			return $response;
		}

		protected function get( $uri, $postfields, $bearer_token = '')
		{
			return $this->curl( 'GET', $uri, $postfields, $bearer_token );
		}

		protected function post($uri, $postfields, $bearer_token = '')
		{
			return $this->curl( 'POST', $uri, $postfields, $bearer_token );
		}

  		private function curl( $method, $uri, $postfields, $bearer_token = '', $content_type = "Content-Type: application/x-www-form-urlencoded" )
  		{
			$curl = curl_init();

			$http_header = array($content_type);

			if( !empty($bearer_token) )
			{
				$http_header = array(
					"Authorization: Bearer " . $bearer_token,
					$content_type
				);
			}

			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->url . $uri,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => $method,
				CURLOPT_POSTFIELDS => $postfields,
				CURLOPT_HTTPHEADER => $http_header,
			));

			$response = curl_exec($curl);

			curl_close($curl);

			return $response;
  		}
	}