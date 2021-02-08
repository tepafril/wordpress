<?php

	namespace BizSolution\OdooAPI;

	class OdooWoocommerce
	{
		public function __construct()
		{
		}

		public function sync_products_with_odoo($take, $page)
		{
			$odoo_api = new OdooAPI();

			$response = $odoo_api->get_products($take, $page);

			$response = json_decode($response, true);

			$result = $response["result"];

			$odoo_id_list = [];
			$odoo_id_str  = '';

			$inc = 0;
			foreach( $result as $product )
			{
				if( !empty($product["variants"]) )
				{
					foreach($product["variants"] as $variant)
					{
						$odoo_id 		= $variant["id"];
						if(!empty($variant["id"]))
						{
							$odoo_id_list[] = $variant["id"];
							if($inc > 0)
								$odoo_id_str  	.= ', ';
							$odoo_id_str  	.= "'".$odoo_id."'";
							$inc++;
						}
					}
				}
			}
			
			$existing_products = $this->get_existing_product_by_odoo_id($odoo_id_str);

			$to_create_odoo_id = [];
			$to_update_odoo_id = [];
			foreach( $odoo_id_list as $odoo_id_index => $odoo_id_value )
			{
				$exist = false;
				foreach($existing_products as $existing_product)
				{
					$odoo_id = get_post_meta( $existing_product->id, 'odoo_id', true );
					// if product exists
					if( $odoo_id_value == $odoo_id )
					{
						$exist = true;
						$to_update_odoo_id[$existing_product->id] = $odoo_id_value;
						break;
					}
				}
				if(!$exist)
				{
					$to_create_odoo_id[] = $odoo_id_value;
				}
			}


			foreach( $result as $product )
			{
				foreach( $product["variants"] as $variant )
				{
					$variation_data = [];
					$variation_data["id"] = $variant["id"];
					$variation_data["name"] = $variant["name"];
					$variation_data["default_code"] = $variant["default_code"];
					// $variation_data["sale_price"] = $variant;
					$variation_data["image"] = $variant["image"];
					$variation_data["regular_price"] = $variant["price_unit"];
					
					foreach( $to_update_odoo_id as $index => $value )
					{
						if( $variant["id"] == $value )
						{
							$product_cat = NULL;
							// $product_id = $this->update_product( $index, $variation_data );
							if(!empty($product["woo_product_catg_ids"]))
							{
								$product_cat = [];
								foreach($product["woo_product_catg_ids"] as $cat){
									$product_cat[] = $cat["name"];
								}
								wp_set_object_terms( $product_id, $product_cat, 'product_cat' );
							}
							if(!empty($product["brand_id"]))
							{
								if(isset($product["brand_id"][1]))
									wp_set_object_terms( $product_id, $product["brand_id"][1], 'pa_brands' );
							}
							break;
						}
					}
					foreach( $to_create_odoo_id as $index => $value )
					{
						if( $variant["id"] == $value )
						{
							$product_cat = NULL;
							// $product_id = $this->create_simple_product( $variation_data );
							if(!empty($product["woo_product_catg_ids"]))
							{
								$product_cat = [];
								foreach($product["woo_product_catg_ids"] as $cat){
									$product_cat[] = $cat["name"];
								}
								wp_set_object_terms( $product_id, $product_cat, 'product_cat' );
							}
							if(!empty($product["brand_id"]))
							{
								if(isset($product["brand_id"][1]))
									wp_set_object_terms( $product_id, $product["brand_id"][1], 'pa_brands' );
							}
							break;
						}
					}
				}
			}

			return $response;
		}

		private function get_existing_product_by_odoo_id( $odoo_id )
		{
			global $wpdb;
			$products 		= $wpdb->get_results( $wpdb->prepare( "SELECT ".$wpdb->posts.".ID FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON " . $wpdb->posts . ".ID = ".$wpdb->postmeta.".post_id WHERE meta_key='odoo_id' AND meta_value IN ($odoo_id)") );

			
			$product_ids 	= wp_list_pluck($products, 'ID');
			$product_ids 	= array_unique($product_ids);
			$products = [];
			if(!empty($product_ids)){
				$products 		= wc_get_products(array(
					'limit' 	=> 	-1,
					'include'  	=> 	$product_ids,
					'status' 	=> 	'publish',
				));
			}

			return $products;
		}




		
		public function sync_categories_from_doo()
		{
			$odoo_api = new OdooAPI();

			$response = $odoo_api->get_categories();

			$response = json_decode($response, true);

			$result = $response;

			$odoo_id_list = [];
			$odoo_id_str  = '';

			$inc = 0;
			foreach( $result as $category )
			{
				
				$odoo_id 		= $category["id"];
				if(!empty($category["id"]))
				{
					$odoo_id_list[] = $category["id"];
					if($inc > 0)
						$odoo_id_str  	.= ', ';
					$odoo_id_str  	.= "'".$odoo_id."'";
					$inc++;
				}
			}
			$existing_categories = $this->get_existing_category_by_odoo_id($odoo_id_str);
			
			$to_create_odoo_id = [];
			$to_update_odoo_id = [];
			foreach( $odoo_id_list as $odoo_id_index => $odoo_id_value )
			{
				$exist = false;
				foreach($existing_categories as $existing_category_id)
				{
					$odoo_id = get_term_meta( $existing_category_id, 'odoo_id', true );
					// if category exists
					if( $odoo_id_value == $odoo_id )
					{
						$exist = true;
						$to_update_odoo_id[$existing_category_id] = $odoo_id_value;
						break;
					}
				}
				if(!$exist)
				{
					$to_create_odoo_id[] = $odoo_id_value;
				}
			}

			$json_string = [];

			foreach( $result as $category )
			{
				foreach( $to_update_odoo_id as $index => $value )
				{
					if( $category["id"] == $value )
					{
						// $parent_odoo_id = get_term_meta( $category["parent_id"], 'odoo_id', true );
						wp_update_term( $index, 
							'product_cat', 
							array(
								// 'description' => 'Description for category',
								'name' => $category["name"],
								// 'parent' => $parent_odoo_id,
							)
						);

						update_term_meta($index, 'odoo_id', $category["id"]);
						$json_string[] = [
							"wp_id" 	=> $index,
							"odoo_id" 	=> $category["id"],
						];
					}
				}
				foreach( $to_create_odoo_id as $index => $value )
				{
					if( $category["id"] == $value )
					{
						$cat_id = wp_insert_term(
							$category["name"],
							'product_cat',
							array(
								// 'description' => 'Description for category',
								// 'parent' => (int) $category["parent_id"],
							)
						);

						if( !array_key_exists('errors',$cat_id) )
						{
							update_term_meta($cat_id["term_id"], 'odoo_id', $category["id"]);
							$json_string[] = [
								"wp_id" 	=> $cat_id["term_id"],
								"odoo_id" 	=> $category["id"],
							];
						}
					}
				}
			}

			return $json_string;
		}

		private function get_existing_category_by_odoo_id( $odoo_id )
		{
			global $wpdb;
		
			$categories 	= $wpdb->get_results( $wpdb->prepare( 
				"SELECT ".$wpdb->terms.".term_id 
					FROM $wpdb->terms 
					INNER JOIN $wpdb->termmeta ON " . $wpdb->terms . ".term_id = ".$wpdb->termmeta.".term_id 
					INNER JOIN $wpdb->term_taxonomy ON " . $wpdb->terms . ".term_id = ".$wpdb->term_taxonomy.".term_id 
					WHERE ".$wpdb->termmeta.".meta_value IN ($odoo_id) 
					AND ".$wpdb->termmeta.".meta_key='odoo_id'
					AND ".$wpdb->term_taxonomy.".taxonomy='product_cat'"
			));

			$category_ids 	= wp_list_pluck($categories, 'term_id');
			$category_ids 	= array_unique($category_ids);

			return $category_ids;
		}





		public function sync_brands_from_doo()
		{
			$odoo_api = new OdooAPI();

			$response = $odoo_api->get_brands();

			$response = json_decode($response, true);

			$result = $response;

			$odoo_id_list = [];
			$odoo_id_str  = '';

			$inc = 0;
			foreach( $result as $brand )
			{
				
				$odoo_id 		= $brand["id"];
				if(!empty($brand["id"]))
				{
					$odoo_id_list[] = $brand["id"];
					if($inc > 0)
						$odoo_id_str  	.= ', ';
					$odoo_id_str  	.= "'".$odoo_id."'";
					$inc++;
				}
			}
			$existing_brands = $this->get_existing_brand_by_odoo_id($odoo_id_str);
			// return $existing_brands;
			
			$to_create_odoo_id = [];
			$to_update_odoo_id = [];
			foreach( $odoo_id_list as $odoo_id_index => $odoo_id_value )
			{
				$exist = false;
				foreach($existing_brands as $existing_brand_id)
				{
					$odoo_id = get_term_meta( $existing_brand_id, 'odoo_id', true );
					// if category exists
					if( $odoo_id_value == $odoo_id )
					{
						$exist = true;
						$to_update_odoo_id[$existing_brand_id] = $odoo_id_value;
						break;
					}
				}
				if(!$exist)
				{
					$to_create_odoo_id[] = $odoo_id_value;
				}
			}

			$json_string = [];

			foreach( $result as $brand )
			{
				foreach( $to_update_odoo_id as $index => $value )
				{
					if( $brand["id"] == $value )
					{
						// $parent_odoo_id = get_term_meta( $brand["parent_id"], 'odoo_id', true );
						wp_update_term( $index, 
							'pa_brands', 
							array(
								// 'description' => 'Description for brand',
								'name' => $brand["name"],
								// 'parent' => $parent_odoo_id,
							)
						);

						update_term_meta($index, 'odoo_id', $brand["id"]);
						$json_string[] = [
							"wp_id" 	=> $index,
							"odoo_id" 	=> $brand["id"],
						];
					}
				}
				foreach( $to_create_odoo_id as $index => $value )
				{
					if( $brand["id"] == $value )
					{
						$cat_id = wp_insert_term(
							$brand["name"],
							'pa_brands',
							array(
								// 'description' => 'Description for brand',
								// 'parent' => (int) $brand["parent_id"],
							)
						);

						if( !array_key_exists('errors',$cat_id) )
						{
							update_term_meta($cat_id["term_id"], 'odoo_id', $brand["id"]);
							$json_string[] = [
								"wp_id" 	=> $cat_id["term_id"],
								"odoo_id" 	=> $brand["id"],
							];
						}
					}
				}
			}

			return $json_string;
		}

		private function get_existing_brand_by_odoo_id( $odoo_id )
		{
			global $wpdb;
		
			$brands 	= $wpdb->get_results( $wpdb->prepare( 
				"SELECT ".$wpdb->terms.".term_id 
					FROM $wpdb->terms 
					INNER JOIN $wpdb->termmeta ON " . $wpdb->terms . ".term_id = ".$wpdb->termmeta.".term_id 
					INNER JOIN $wpdb->term_taxonomy ON " . $wpdb->terms . ".term_id = ".$wpdb->term_taxonomy.".term_id 
					WHERE ".$wpdb->termmeta.".meta_value IN ($odoo_id) 
					AND ".$wpdb->termmeta.".meta_key='odoo_id'
					AND ".$wpdb->term_taxonomy.".taxonomy='pa_brands'"
			));

			
			
			$brand_ids 	= wp_list_pluck($brands, 'term_id');
			$brand_ids 	= array_unique($brand_ids);

			return $brand_ids;
		}

		private function insert_featured_image( $product )
		{
			include_once( ABSPATH . 'wp-admin/includes/image.php' );
			$imageurl =  $product["image"];
			$imagetype = explode('/', getimagesize($imageurl)['mime']);
			$imagetype = end($imagetype);
			$uniq_name = date('dmY').''.(int) microtime(true); 
			$filename = $uniq_name.'.'.$imagetype;

			$uploaddir = wp_upload_dir();
			$uploadfile = $uploaddir['path'] . '/' . $filename;
			$contents= file_get_contents($imageurl);
			$savefile = fopen($uploadfile, 'w');
			fwrite($savefile, $contents);
			fclose($savefile);

			$wp_filetype = wp_check_filetype(basename($filename), null );
			$attachment = array(
			    'post_mime_type' => $wp_filetype['type'],
			    'post_title' => $filename,
			    'post_content' => '',
			    'post_status' => 'inherit'
			);

			$attach_id = wp_insert_attachment( $attachment, $uploadfile );
			$imagenew = get_post( $attach_id );
			$fullsizepath = get_attached_file( $imagenew->ID );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $fullsizepath );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			return $attach_id;
		}
	}