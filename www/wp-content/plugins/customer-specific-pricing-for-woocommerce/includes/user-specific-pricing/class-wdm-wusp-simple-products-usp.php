<?php

namespace WuspSimpleProduct;

//use WuspGetData as cspGetData;

//check whether a class with the same name exists
if (! class_exists('WdmWuspSimpleProductsUsp')) {
    /**
     * Class to Display & Process data of Simple Products for User Specific Pricing
     */
    //class declartion
    class WdmWuspSimpleProductsUsp
    {

        public function __construct()
        {
            global $getDataFromDb;

             //including the below file to get the function to validate license
            include_once(dirname(dirname(__FILE__)) . '/class-wdm-wusp-get-data.php');
            $getDataFromDb = \WdmCSP\WdmGetLicenseData::getDataFromDb($getDataFromDb);
            if ('available' == $getDataFromDb) {
                add_action('woocommerce_product_write_panel_tabs', array(
                $this, 'userSpecificPricingTab', ));

                // Removed as the woocommerce_product_write_panels is deprecated instead used woocommerce_product_data_panels
                // add_action('woocommerce_product_write_panels', array(
                // $this, 'userSpecificPricingTabOptions', ));

                add_action('woocommerce_product_data_panels', array(
                $this, 'userSpecificPricingTabOptions', ));

                add_action('woocommerce_process_product_meta_simple', array(
                $this, 'addUserPriceMappingInDb', ));

                // Moved to new class
                // add_filter('woocommerce_get_price', array(
                // $this, 'applyCustomPrice', ), 99, 2);
                // add_filter('woocommerce_get_price_html', array($this, 'showQuantityBasedPricing', ), 1, 2);
                // add_action('woocommerce_single_product_summary', array($this,'cspQuantityBasedProductTotal',), 31);
            }
        }


        public function cspQuantityBasedProductTotal()
        {
            global $woocommerce, $product;

            // let's setup our divs
            echo sprintf('<div id="product_total_price" style="margin-bottom:20px;">%s %s<input name = "product_qty" type = "hidden"/></div>', __('Product Total:', 'woocommerce'), '<span class="price">'.$product->get_price().'</span>');
        }

    /**
     * Shows User Specific Pricing tab on Product create/edit page
     *
     * This tab shows options to add price for specific users
     * while creating a product or editing the product.
     */
        public function userSpecificPricingTab()
        {
            global $getDataFromDb;
             //including the below file to get the function to validate license
            include_once(dirname(dirname(__FILE__)) . '/class-wdm-wusp-get-data.php');
            $getDataFromDb = \WdmCSP\WdmGetLicenseData::getDataFromDb($getDataFromDb);
            if ('available' == $getDataFromDb) {
            ?>
            <li class="userSpecificPricingTab show_if_simple"><a href="#userSpecificPricingTab_data"><?php _e('Customer Specific Pricing', CSP_TD); ?></a></li>
        <?php
            }
        }

    /**
     * User Specific Tab Content
     *
     * Shows the tab content i.e. allows admin to add pair and
     * remove user-price pair
     */
        public function userSpecificPricingTabOptions()
        {
            global $post;
            include(trailingslashit(dirname(dirname(dirname(__FILE__)))) . 'templates/print_user_specific_pricing_tab_content.php');
        }

        public function getPriceTypeArray()
        {
            if (isset($_POST['wdm_price_type'])) {
                return $_POST['wdm_price_type'];
            } else {
                return array();
            }
        }

        /**
         * Processing the records and performing insert, delete and update on it
        */
        public function addUserPriceMappingInDb($product_id)
        {
            global $wpdb, $cspFunctions;
            global $post, $subruleManager;
            $temp_user_qty_array     = array();
            $deleteUsers              = array();
            $deleteQty                = array();
            $deletedValues            = array();
            $newArray                 = array();
            $wusp_pricing_table  = $wpdb->prefix . 'wusp_user_pricing_mapping';
            $user_names          = '';
            $userType = 'user_id';

            //delete records
            if (isset($_POST[ 'wdm_woo_username' ])) {
                //array of curremt records
                foreach ($_POST[ 'wdm_woo_username' ] as $index => $wdmSingleUser) {
                    $newArray[] = array(
                            'user_id'    => $wdmSingleUser,
                            'min_qty' => $_POST[ 'wdm_woo_qty' ][ $index ]
                        );
                }
                $user_names = "('" . implode("','", $_POST[ 'wdm_woo_username' ]) . "')";
                $qty = "(" . implode(",", $_POST[ 'wdm_woo_qty' ]) . ")";

                //Fetch records from databse
                $existing = $wpdb->get_results($wpdb->prepare("SELECT user_id, min_qty FROM {$wusp_pricing_table} WHERE product_id = %d", $product_id), ARRAY_A);

                //Seperating records to be deleted, i.e the records which are in DB but not in current submission
                $deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType);
                foreach ($deletedValues as $key => $value) {
                    $deleteUsers[] = $existing[$key][$userType];
                    $deleteQty[]   = $existing[$key]['min_qty'];
                }

                //delete records which are not in submission but saved in the DB
                if (count($deletedValues) > 0) {
                    foreach ($deleteUsers as $index => $singleUser) {
                        $query = "DELETE FROM $wusp_pricing_table WHERE user_id = %d AND min_qty = %d AND product_id = %d";
                        $wpdb->get_results($wpdb->prepare($query, $singleUser, $deleteQty[$index], $product_id));
                    }
                    //Deactivate subrule for deleted record
                    $subruleManager->deactivateSubrulesForCustomersNotInArray($product_id, $deleteUsers, $deleteQty);
                }
            }

            //Insert and Update records
            if (isset($_POST[ 'wdm_woo_username' ]) && ! empty($_POST[ 'wdm_woo_username' ]) && isset($_POST[ 'wdm_woo_qty' ]) && ! empty($_POST[ 'wdm_woo_qty' ])) {
                foreach ($_POST[ 'wdm_woo_username' ] as $index => $wdm_woo_user_id) {
                    if (isset($wdm_woo_user_id)) {
                        $userQtyPair = $wdm_woo_user_id."-".$_POST[ 'wdm_woo_qty' ][ $index ];
                        if (! in_array($userQtyPair, $temp_user_qty_array)) {
                            array_push($temp_user_qty_array, $userQtyPair);
                            $user_id = $wdm_woo_user_id;
                            $qty = $_POST[ 'wdm_woo_qty' ][ $index ];
                            if (isset($_POST[ 'wdm_woo_price' ][ $index ]) && isset($_POST[ 'wdm_price_type' ][ $index ]) && isset($qty) && !($qty <= 0)) {
                                $pricing = wc_format_decimal($_POST[ 'wdm_woo_price' ][ $index ]);
                                $price_type = $_POST[ 'wdm_price_type' ][ $index ];

                                if (! empty($user_id) && ! empty($pricing) && ! empty($price_type)) {
                                    $result = $wpdb->get_results($wpdb->prepare("SELECT id FROM $wusp_pricing_table WHERE user_id = '%d' and min_qty = '%d' and product_id=%d", $wdm_woo_user_id, $qty, $product_id));
                                    if (count($result) > 0) {
                                        $update_status = $wpdb->update($wusp_pricing_table, array(
                                            'user_id'                   => $user_id,
                                            'price'                  => $pricing,
                                            'flat_or_discount_price' => $price_type,
                                            'product_id'             => $product_id,
                                            'min_qty'                => $qty,
                                        ), array( 'user_id' => $user_id, 'product_id' => $product_id, 'min_qty' => $qty ));

                                        if ($update_status) {
                                            $subruleManager->deactivateSubrulesOfCustomerForProduct($product_id, $user_id, $qty);
                                        }
                                    } else {
                                        $wpdb->insert($wusp_pricing_table, array(
                                            'user_id'                => $user_id,
                                            'price'                  => $pricing,
                                            'flat_or_discount_price' => $price_type,
                                            'product_id'             => $product_id,
                                            'min_qty'                => $qty,
                                        ), array(
                                            '%d',
                                            '%s',
                                            '%d',
                                            '%d',
                                            '%d',
                                        ));
                                    }
                                }
                            }
                            //If price is not set delete that record
                            if (empty($pricing)) {
                                $wpdb->delete(
                                    $wusp_pricing_table,
                                    array(
                                    'user_id'       => $user_id,
                                    'product_id' => $product_id,
                                    'min_qty'    => $qty,
                                    ),
                                    array(
                                    '%d',
                                    '%d',
                                    '%d',
                                    )
                                );
                                $subruleManager->deactivateSubrulesOfCustomerForProduct($product_id, $user_id, $qty);
                            }
                        }
                        // $counter ++;
                    }
                }//foreach ends
            } else {
                // If all records for the product are removed
                $wpdb->delete(
                    $wusp_pricing_table,
                    array(
                    'product_id' => $product_id,
                    ),
                    array(
                    '%d',
                    )
                );
            }
        }
    }

}
