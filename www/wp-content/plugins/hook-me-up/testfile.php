<?php


function read_file_Nour(){
    global $wpdb;
    $wdm_user_product_price_mapping  = $wpdb->prefix . 'wusp_user_pricing_mapping';
    $wdm_users                       = $wpdb->prefix . 'users';
    $fetched_users = array();

     $csv_file                        = 'http://staging.wearerework.co.uk/wp_rework/price.csv'; 

    //for checking headers
    $requiredHeaders                 = array( 'Product id', 'User', 'Price' );
    $fptr                               = fopen($csv_file, 'r');
    $firstLine                       = fgets($fptr); //get first line of csv file
    fclose($fptr);
    $foundHeaders                    = str_getcsv(trim($firstLine), ',', '"'); //parse to array
    //check the headers of file
    if ($foundHeaders !== $requiredHeaders) {
        die();
    }
    //$users     = array();
    if (false !== ($getfile = fopen($csv_file, 'r') )) {
        $data        = fgetcsv($getfile, 1000, ',');
        //display table headers

        $update_cnt  = 0;
        $insert_cnt  = 0;
        $count=0;
        while (false !== ($data      = fgetcsv($getfile, 1000, ','))) {
            $count++;
            $result                  = $data;
            $str                     = implode(',', $result);
            $slice                   = explode(',', $str);
            $product_id              = $slice[ 0 ];
            $user                    = $slice[ 1 ];
            $price                   = $slice[ 2 ]; 

            $status = null;
            $product = get_product($product_id);

            // cspPrintDebug($product);
            //check all values valid or not
            if (floatval($price) || $price==0.0) {
                //check if product exists or not
                if (isset($product->post) && (get_class($product) == 'WC_Product_Simple' && $product->post->post_type == "product")) {
                    if (!isset($fetched_users[$user])) {
                        //get user id
                        $fetched_users[$user] = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wdm_users} where user_login=%s", $user));
                    }
                    $get_user_id = $fetched_users[$user];

                    if ($get_user_id == null) {
                         $status = __('User does not exist', 'customer-specific-pricing-lite');
                    } else {

                    //Update price for existing one
                        $result = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wdm_user_product_price_mapping} where product_id=%d and user_id=%d", $product_id, $get_user_id));
                        if ($result != null) {
                            $update_price = $wpdb->update(
                                $wdm_user_product_price_mapping,
                                array(
                                'price' => $price,
                                'flat_or_discount_price' => 1,
                                ),
                                array(
                                'product_id' => $product_id,
                                'user_id'   => $get_user_id,
                                ),
                                array(
                                '%f',
                                '%d',
                                ),
                                array('%d',
                                '%d')
                            );
                            if ($update_price == 0) {
                                $status = __('Record already exists', 'customer-specific-pricing-lite');
                            } else {
                                $status = __('Record Updated', 'customer-specific-pricing-lite');
                                $update_cnt ++;
                            }
                        } else {
                            //add entry in our table
                            if ($wpdb->insert(
                                $wdm_user_product_price_mapping,
                                array(
                                'product_id' => $product_id,
                                'user_id'   => $get_user_id,
                                'price' => $price,
                                'flat_or_discount_price' => 1,
                                ),
                                array(
                                '%d',
                                '%d',
                                '%s',
                                '%d',
                                )
                            )) {
                                $status = __('Record Inserted', 'customer-specific-pricing-lite');
                                $insert_cnt ++;
                            } else {
                                $status = __('Record could not be inserted', 'customer-specific-pricing-lite');
                            }
                        }
                    }
                } else {
                    $status = __('Either Product does not exist or not supported', 'customer-specific-pricing-lite');
                }
            } else {
                $status = __('Invalid field values', 'customer-specific-pricing-lite');
            }
            //display status message
        }//end of while
        // $ruleManager->setUnusedRulesAsInactive();
        //display summary
    }
}
add_action('init', 'read_file_Nour');