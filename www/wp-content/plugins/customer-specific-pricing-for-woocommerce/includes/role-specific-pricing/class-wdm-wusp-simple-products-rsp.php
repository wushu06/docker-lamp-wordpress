<?php

namespace WuspSimpleProduct\WrspSimpleProduct;

/**
 * display and apply the role specific price for simple product
 */
if (! class_exists('WdmWuspSimpleProductsRsp')) {

    /**
     * Class to Display & Process data of Simple Products for Role Specific Pricing
     */
    class WdmWuspSimpleProductsRsp
    {

        public function __construct()
        {
            global $wdmWuspPluginData;
            //including the below file to get the function to validate license
            include_once(dirname(dirname(__FILE__)) . '/class-wdm-wusp-get-data.php');
            $getDataFromDb = \WdmCSP\WdmGetLicenseData::getDataFromDb($wdmWuspPluginData);
            if ('available' == $getDataFromDb) {
                add_action('wdm_add_after_simple_csp', array( $this, 'printRoleTab' ), 5);
                //including the template for group specific pricing tab
                
            // Removed as the woocommerce_product_write_panels is deprecated instead used woocommerce_product_data_panels
            // add_action('woocommerce_product_write_panels', array( $this, 'cspDisplayUserRole' ), 10);
                add_action('woocommerce_product_data_panels', array( $this, 'cspDisplayUserRole' ), 10);

                add_action('woocommerce_process_product_meta_simple', array( $this, 'addRolePriceMappingInDb' ), 10, 1);
            }
        }

        /*
		 * displays user role and prices stored in database for the specific product
		 */

        public function printRoleTab()
        {
            ?>

            <h3 class="wdm-heading"><?php _e('Role Based Pricing', CSP_TD) ?></h3>
            <div>
                <div id="role_specific_pricing_tab_data">
                    <table cellpadding="0" cellspacing="0" class="wc-metabox-content wdm_simple_product_rsp_table" style="display: table;">
                        <thead class="role_price_thead">
                            <tr>
                                <th style="text-align: left">
                                    <?php _e('User Role', CSP_TD) ?>
                                </th>
                                <th style="text-align: left">
                                    <?php _e('Discount Type', CSP_TD) ?>
                                </th>
                                <th>
                                    <?php _e('Min Qty', CSP_TD) ?>
                                </th>
                                <th colspan=3>
                                    <?php _e('Value', CSP_TD) ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="wdm_role_specific_pricing_tbody"></tbody>
                    </table>
                </div>  </div>
            <?php
        }

        public function cspDisplayUserRole()
        {
            global $post;

            // $product = wc_get_product($post->ID);
            // if ($product->is_type('simple')) {
                include(trailingslashit(dirname(dirname(dirname(__FILE__)))) . 'templates/print_role_specific_pricing_tab_content.php');
            // }
        }//end of funtion cspDisplayUserRole

        /**
         * inserts pricing and role-product mapping in database
         * @global object $wpdb Object responsible for executing db queries
         * @param type $user_product_id product id from wp_wusp_user_mapping table
         * @param type $pricing price to be set
         */
        public function addRolePriceMappingInDb($product_id)
        {
            global $wpdb;
            global $post, $subruleManager, $cspFunctions;
            // $counter             = 0;
            $temp_array_role_qty          = array();
            // $temp_array_qty           = array();
            $deleteRoles              = array();
            $deleteQty                = array();
            $deletedValues            = array();
            $newArray                 = array();
            // $product_id          = $post->ID;
            $role_pricing_table  = $wpdb->prefix . 'wusp_role_pricing_mapping';
            $role_names          = '';


            if (isset($_POST[ 'wdm_woo_rolename' ])) {
                foreach ($_POST[ 'wdm_woo_rolename' ] as $index => $wdm_woo_role_name) {
                    $newArray[] = array(
                            'role'    => $wdm_woo_role_name,
                            'min_qty' => $_POST[ 'wdm_woo_role_qty' ][ $index ]
                        );
                }
                $role_names = "('" . implode("','", $_POST[ 'wdm_woo_rolename' ]) . "')";
                $qty = "(" . implode(",", $_POST[ 'wdm_woo_role_qty' ]) . ")";
                // $get_rem_products = $wpdb->get_col($wpdb->prepare("SELECT id FROM {$role_pricing_table} WHERE role NOT IN $role_names and min_qty NOT IN $qty and product_id=%d", $product_id));
                $existing = $wpdb->get_results($wpdb->prepare("SELECT role, min_qty FROM {$role_pricing_table} WHERE product_id = %d", $product_id), ARRAY_A);
                $userType = 'role';
                $deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType);
                foreach ($deletedValues as $key => $value) {
                    $deleteRoles[] = $existing[$key][$userType];
                    $deleteQty[]   = $existing[$key]['min_qty'];
                }

                $mapping_count = count($deletedValues);
                if ($mapping_count > 0) {
                    foreach ($deleteRoles as $index => $singleRole) {
                        $query = "DELETE FROM $role_pricing_table WHERE role = %s AND min_qty = %d AND product_id = %d";
                        $wpdb->get_results($wpdb->prepare($query, $singleRole, $deleteQty[$index], $product_id));
                    }
                    $subruleManager->deactivateSubrulesForRolesNotInArray($product_id, $deleteRoles, $deleteQty);
                }
            }
            if (isset($_POST[ 'wdm_woo_rolename' ]) && ! empty($_POST[ 'wdm_woo_rolename' ])) {
                foreach ($_POST[ 'wdm_woo_rolename' ] as $index => $wdm_woo_role_name) {
                    if (isset($wdm_woo_role_name)) {
                        $roleQtyPair = $wdm_woo_role_name."-".$_POST[ 'wdm_woo_role_qty' ][ $index ];
                        if (! in_array($roleQtyPair, $temp_array_role_qty)) {
                            array_push($temp_array_role_qty, $roleQtyPair);
                            // array_push($temp_array_qty, $_POST[ 'wdm_woo_role_qty' ][ $index ]);
                            $role_id = $wdm_woo_role_name;
                            $qty = $_POST[ 'wdm_woo_role_qty' ][ $index ];
                            if (isset($_POST[ 'wdm_woo_role_price' ][ $index ]) && isset($_POST[ 'wdm_role_price_type' ][ $index ]) && isset($qty) && !($qty <= 0)) {
                                $pricing = wc_format_decimal($_POST[ 'wdm_woo_role_price' ][ $index ]);
                                $price_type = $_POST[ 'wdm_role_price_type' ][ $index ];

                                if (! empty($role_id) && ! empty($pricing) && ! empty($price_type)) {
                                    $result = $wpdb->get_results($wpdb->prepare("SELECT id FROM $role_pricing_table WHERE role = '%s' and min_qty = '%d' and product_id=%d", $wdm_woo_role_name, $qty, $product_id));
                                    if (count($result) > 0) {
                                        $update_status = $wpdb->update($role_pricing_table, array(
                                            'role'                   => $role_id,
                                            'price'                  => $pricing,
                                            'flat_or_discount_price' => $price_type,
                                            'product_id'             => $product_id,
                                            'min_qty'                => $qty,
                                        ), array( 'role' => $role_id, 'product_id' => $product_id, 'min_qty' => $qty ));

                                        if ($update_status) {
                                            $subruleManager->deactivateSubrulesOfRoleForProduct($product_id, $role_id, $qty);
                                        }
                                    } else {
                                        $wpdb->insert($role_pricing_table, array(
                                            'role'                   => $role_id,
                                            'price'                  => $pricing,
                                            'flat_or_discount_price' => $price_type,
                                            'product_id'             => $product_id,
                                            'min_qty'                => $qty,
                                        ), array(
                                            '%s',
                                            '%s',
                                            '%d',
                                            '%d',
                                            '%d',
                                        ));
                                    }
                                }
                            }
                            if (empty($pricing)) {
                                $wpdb->delete(
                                    $role_pricing_table,
                                    array(
                                    'role'       => $role_id,
                                    'product_id' => $product_id,
                                    'min_qty'    => $qty,
                                    ),
                                    array(
                                    '%s',
                                    '%d',
                                    '%d',
                                    )
                                );
                                $subruleManager->deactivateSubrulesOfRoleForProduct($product_id, $role_id, $qty);
                            }
                        }
                        // $counter ++;
                    }
                }//foreach ends
            } else {
                $wpdb->delete(
                    $role_pricing_table,
                    array(
                    'product_id' => $product_id,
                    ),
                    array(
                    '%d',
                    )
                );
            }
        }

//end of function addRolePriceMappingInDb

        /**
         * Finds out price for specific role for specific product
         * @param int $user_id
         * @param int $product_id
         * @global object $wpdb
         * @return mixed if price is found, price is returned. Otherwise it returns false
         */
        public static function getPriceOfProductForRole($current_user_id, $product_id)
        {
            global $wpdb;
            static $userPrices = array();

            if (isset($userPrices[$current_user_id][$product_id])) {
                return $userPrices[$current_user_id][$product_id];
            }

            //global $current_user, $wpdb;
            $user_info           = get_userdata($current_user_id);
            $user_role           = "('" . implode("','", $user_info->roles) . "')";
            $role_price_table    = $wpdb->prefix . 'wusp_role_pricing_mapping';

            $priceInfo           = $wpdb->get_row($wpdb->prepare("SELECT price, flat_or_discount_price as price_type FROM {$role_price_table} WHERE role IN $user_role AND product_id=%d ORDER BY `price` ASC", $product_id), ARRAY_A);

            $price = $priceInfo['price'];
            $priceType = $priceInfo['price_type'];

            if ($priceType == 2) {
                $regularPrice = get_post_meta($product_id, '_regular_price', true);
                if ($regularPrice >= 0) {
                    $discount = floatval(($price/100) * $regularPrice);
                    $price = $regularPrice - $discount;
                } else {
                    $userPrices[$current_user_id][$product_id] = 0;
                    return $userPrices[$current_user_id][$product_id];
                }
            }
            if ($price) {
                $userPrices[$current_user_id][$product_id] = $price;
            } else {
                $userPrices[$current_user_id][$product_id] = false;
            }
            return $userPrices[$current_user_id][$product_id];
        }

//end of function getPriceOfProductForRole

        /**
         * Finds out qty & price pair for specific role for specific product
         * @param int $user_id
         * @param int $product_id
         * @global object $wpdb
         * @return mixed if price is found, price is returned. Otherwise it returns false
         */
        public static function getQtyPricePairsOfProductForRole($current_user_id, $product_id, $roleList = array())
        {
            global $wpdb;
            static $pricePairs = array();

            if (isset($pricePairs[$current_user_id][$product_id])) {
                return $pricePairs[$current_user_id][$product_id];
            }

            //global $current_user, $wpdb;
            if (empty($roleList)) {
                $user_info           = get_userdata($current_user_id);
                $user_role           = "('" . implode("','", $user_info->roles) . "')";
            } else {
                $user_role           = "('" . implode("','", $roleList) . "')";
            }
            
            $role_price_table    = $wpdb->prefix . 'wusp_role_pricing_mapping';
            $price               = $wpdb->get_results($wpdb->prepare("SELECT price, min_qty, flat_or_discount_price as price_type FROM {$role_price_table} WHERE role IN $user_role AND product_id=%d ORDER BY min_qty", $product_id));
            
            $pricePairs[$current_user_id][$product_id] = $price;
            return $pricePairs[$current_user_id][$product_id];
        }


        /**
         * Retireves all prices for a product from Database.
         * @global object $wpdb
         * @param type $product_id
         * @return array returns array of user=>price combination
         */
        public static function getAllRolePricesForSingleProduct($product_id)
        {
            global $wpdb;
            $role_price_table    = $wpdb->prefix . 'wusp_role_pricing_mapping';
            $role_product_result = $wpdb->get_results($wpdb->prepare("SELECT role, price, min_qty, flat_or_discount_price as price_type FROM {$role_price_table} WHERE product_id=%d ORDER BY `id` ASC", $product_id));

            if ($role_product_result) {
                return ($role_product_result);
            }
        }

// end of function getAllRolePricesForSingleProduct
    }

    //end of class
}//end of if class exists
