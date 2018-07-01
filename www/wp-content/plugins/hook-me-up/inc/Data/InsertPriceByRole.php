<?php

namespace Inc\Data;

use \Inc\Base\BaseController;

class InsertPriceByRole extends BaseController {

    public $data_check = false;

    function insert_update_by_role($file){
        global $wpdb;
        global $wp_roles;
        $all_roles = $wp_roles->roles;

        $wdm_user_product_role_mapping  = $wpdb->prefix . 'wusp_role_pricing_mapping';
        $wdm_users                       = $wpdb->prefix . 'users';
        $fetched_users = array();

      //  $csv_file                        = $this->plugin_url.'price.csv';
        $csv_file = $file;

        //for checking headers
        // $requiredHeaders                 = array( 'Product id', 'User', 'Price' );
        $requiredHeaders                 = array( 'Internal ID', 'Min Quantity', 'Feed Name', 'Role','Price' );

        $fptr                               = fopen($csv_file, 'r');
        $firstLine                       = fgets($fptr); //get first line of csv file
        fclose($fptr);
        $foundHeaders                    = str_getcsv(trim($firstLine), ',', '"'); //parse to array

        //check the headers of file
        if ($foundHeaders !== $requiredHeaders) {
            echo 'File Header not the same';
            $this->data_check = false;
            die();
        }
        $getfile = fopen($csv_file, 'r');
        //$users     = array();
        if (false !== ($getfile = fopen($csv_file, 'r') )) {
            $data        = fgetcsv($getfile, 1000, ',');
            //display table headers

            $update_cnt  = 0;
            $insert_cnt  = 0;
            $count=0;
            while (false !== ($data      = fgetcsv($getfile, 1000, ','))) {
	            if ($data[0] != NULL) {  // ignore blank lines
		            $count++;
		            $result = $data;
		            $str = implode(',', $result);
		            $slice = explode(',', $str);

		           $netsuite_id = $slice[0];
		            $min_qty = $slice[1];
		            $user = $slice[2];
		            $role =  strtolower(str_replace([" ", " "], '-',$slice[3]));
		            $price = trim(str_replace('£','',$slice[4]));




		            $count = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE custom_id = %d AND post_type = 'product'", $netsuite_id));
		            $stdInstance = json_decode(json_encode($count), true);
		            foreach ($stdInstance as $c) {
			            $product_id = $c['ID'];
		            }
		            $product = wc_get_product($product_id);

		            // cspPrintDebug($product);
		            //check all values valid or not

			            //check if product exists or not
			            if (isset($product->post) && (get_class($product) == 'WC_Product_Simple' && get_post_type($product_id) == "product")) {
				            /*  if (!isset($fetched_users[$user])) {
								  //get user id
								  $fetched_users[$user] = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wdm_users} where user_login=%s", $user));
							  }*/
				            //$get_user_id = $fetched_users[$user];
				            $discount_price = 0;

				            //Update price for existing one
				            // $result = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wdm_user_product_role_mapping} where product_id=%d", $product_id));
				            $result = $wpdb->get_row($wpdb->prepare("SELECT * from {$wdm_user_product_role_mapping} WHERE role = '%s' AND product_id = %d  AND price =%d", $role, intval($product_id), $price));
				            if ($result != null) {


					            $update_price = $wpdb->update(
						            $wdm_user_product_role_mapping,
						            array(
							            'price' => $price,
							            'flat_or_discount_price' => 0,
							            'min_qty' => 1
						            ),
						            array(
							            'id' => $result->id,

						            ),

						            array(
							            '%f',
							            '%d',
							            '%d')
					            );
					            if ($update_price == 0) {
						            $this->data_check = false;
						            $msg = __('Record already exists', 'customer-specific-pricing-lite');
					            } else {
						            $this->data_check = true;
						            $msg = __('Record Updated', 'customer-specific-pricing-lite');
						            $update_cnt++;
					            }
				            } else {
					            $this->data_check = false;
					            $msg = "did not find product";
					            //add entry in our table
					            if ($wpdb->insert(

						            $wdm_user_product_role_mapping,
						            array(
							            'product_id' => $product_id,
							            'role' => $role,
							            'min_qty' => $min_qty,
							            'price' => $price,
							            'flat_or_discount_price' => $discount_price,
						            ),
						            array(
							            '%d',
							            '%s',
							            '%d',
							            '%f',
							            '%d',
						            )
					            )) {
						            $this->data_check = true;
						            $msg = __('Record Inserted', 'customer-specific-pricing-lite');
						            $insert_cnt++;
					            } else {
						            $this->data_check = false;
						            $msg = __('Record could not be inserted', 'customer-specific-pricing-lite');
					            }
				            }


			            } else {
				            $this->data_check = false;
				            $msg = __('Either Product does not exist or not supported', 'customer-specific-pricing-lite');
			            }

		            //display status message
	            }//end if empty lines
            }//end of while
            // $ruleManager->setUnusedRulesAsInactive();
            //display summary
        }
        return $result = array('msg'=>$msg, 'check'=>$this->data_check);    }
}