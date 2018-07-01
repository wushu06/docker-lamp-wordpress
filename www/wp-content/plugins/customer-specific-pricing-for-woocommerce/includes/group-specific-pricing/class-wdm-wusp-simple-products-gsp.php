<?php

namespace WuspSimpleProduct\WgspSimpleProduct;

//check whether a class with the same name exists
if (! class_exists('WdmWuspSimpleProductsGsp')) {

    /**
     * Class to Display & Process data of Simple Products for Group Specific Pricing
     */
    //class declartion
    class WdmWuspSimpleProductsGsp
    {

        public function __construct()
        {
            global $wdmWuspPluginData;
            //including the below file to get the function to validate license
            include_once(dirname(dirname(__FILE__)) . '/class-wdm-wusp-get-data.php');
            $getDataFromDb = \WdmCSP\WdmGetLicenseData::getDataFromDb($wdmWuspPluginData);
            if ('available' == $getDataFromDb) {
                // to add show the groups setting tabs after the CSP
                add_action('wdm_add_after_simple_csp', array( $this, 'printGroupTabs' ), 10);

                //including the template for group specific pricing tab
                // Removed as the woocommerce_product_write_panels is deprecated instead used woocommerce_product_data_panels
                // add_action('woocommerce_product_write_panels', array( $this, 'groupSpecificPricingTabOptions' ));
                add_action('woocommerce_product_data_panels', array( $this, 'groupSpecificPricingTabOptions' ));

                //handle the saving of groups and price pair
                add_action('woocommerce_process_product_meta_simple', array( $this, 'processGroupPricingPairs' ));
            }
        }

        //display Groups GUI
        public function printGroupTabs()
        {
            /**
             * Check if Groups is active
             */
            $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
            if (in_array('groups/groups.php', $active_plugins)) {
                ?>
                <h3 class="wdm-heading"><?php _e('Group Based Pricing', CSP_TD) ?></h3>
                <div  id="group_specific_pricing_tab_data">
                    <!-- <button type="button" class="button" id="wdm_add_new_group_price_pair"><?php //_e('Add New Group-Price Pair', CSP_TD) ?></button> -->
                    <div class="options_group wdm_group_pricing_tab_options">
                        <table cellpadding="0" cellspacing="0" class="wc-metabox-content wdm_simple_product_gsp_table" style="display: table;">
                            <thead class="groupname_price_thead">
                                <tr>
                                    <th style="text-align: left">
                                        <?php _e('Group Name', CSP_TD) ?>
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
                            <tbody id="wdm_group_specific_pricing_tbody"></tbody>
                        </table>
                    </div>
                </div>
                <?php
            } else {
                ?><h3 class="wdm-heading"><?php _e('Group Based Pricing', CSP_TD) ?></h3>
                <div  id="group_specific_pricing_tab_data">
                    <?php _e("Activate the 'Groups' Plugin to enjoy the benefits of Group Specific Pricing.", CSP_TD) ?>
                </div><?php
            }
        }

        /**
         * User Specific Tab Content
         *
         * Shows the tab content i.e. allows admin to add pair and
         * remove group-price pair
         */
        public function groupSpecificPricingTabOptions()
        {
            global $post;
            /**
             * Check if Groups is active
             */
            $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
            if (in_array('groups/groups.php', $active_plugins)) {
                include(trailingslashit(dirname(dirname(dirname(__FILE__)))) . 'templates/print_group_specific_pricing_tab_content.php');
            }
        }

        /**
         * Process meta
         *
         * Processes the custom tab options when a post is saved
         */
        public function processGroupPricingPairs($product_id)
        {
            global $wpdb, $subruleManager;

            $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
            if (in_array('groups/groups.php', $active_plugins)) {
                $group_product_table     = $wpdb->prefix . 'wusp_group_product_price_mapping';

                self::removeGroupProductList($product_id, $group_product_table);

                self::addGroupProductList($product_id, $group_product_table);
            }
        }

        public function addGroupProductList($product_id, $group_product_table)
        {
            global $wpdb;
            $temp_group_qty_array     = array();
            if (isset($_POST[ 'wdm_woo_groupname' ]) && ! empty($_POST[ 'wdm_woo_group_qty' ])) {
                foreach ($_POST[ 'wdm_woo_groupname' ] as $index => $wdm_woo_group_id) {
                    $temp_group_qty_array = self::addGroupPriceMappingInDb($product_id, $index, $wdm_woo_group_id, $group_product_table, $temp_group_qty_array);
                }//foreach ends
            } else {
                $wpdb->delete(
                    $group_product_table,
                    array(
                    'product_id' => $product_id,
                    ),
                    array(
                    '%d',
                    )
                );
            }
        }

        public function addGroupPriceMappingInDb($product_id, $index, $group_id, $group_product_table, $temp_group_qty_array)
        {
            global $wpdb;
            global $post, $subruleManager;
            
            if (isset($group_id)) {
                $groupQtyPair = $group_id."-".$_POST[ 'wdm_woo_group_qty' ][ $index ];
                if (! in_array($groupQtyPair, $temp_group_qty_array)) {
                    array_push($temp_group_qty_array, $groupQtyPair);
                    self::setGroupPricingPairs($group_product_table, $product_id, $group_id, $index);
                }
            }

            return $temp_group_qty_array;
        }

        public function setGroupPricingPairs($group_product_table, $product_id, $group_id, $index)
        {
            global $wpdb, $subruleManager;
            $qty = $_POST[ 'wdm_woo_group_qty' ][ $index ];
            $pricing = '';

            if (isset($_POST[ 'wdm_woo_group_price' ][ $index ]) && isset($_POST[ 'wdm_group_price_type' ][ $index ]) && isset($qty) && !($qty <= 0)) {
                $pricing = wc_format_decimal($_POST[ 'wdm_woo_group_price' ][ $index ]);
                self::insertGroupPricingPairs($group_product_table, $pricing, $product_id, $group_id, $index, $qty);
            }

            if (empty($pricing)) {
                $wpdb->delete(
                    $group_product_table,
                    array(
                    'group_id'      => $group_id,
                    'product_id'    => $product_id,
                    'min_qty'       => $qty,
                    ),
                    array(
                    '%d',
                    '%d',
                    '%d',
                    )
                );
                $subruleManager->deactivateSubrulesOfGroupForProduct($product_id, $group_id, $qty);
            }
        }

        public function insertGroupPricingPairs($group_product_table, $pricing, $product_id, $group_id, $index, $qty)
        {
            global $wpdb, $subruleManager;
            
            $price_type = $_POST[ 'wdm_group_price_type' ][ $index ];
            if (! empty($group_id) && ! empty($pricing) && ! empty($price_type)) {
                $result = $wpdb->get_results($wpdb->prepare("SELECT id FROM $group_product_table WHERE group_id = '%d' and min_qty = '%d' and product_id=%d", $group_id, $qty, $product_id));
                if (count($result) > 0) {
                    $update_status = $wpdb->update($group_product_table, array(
                        'group_id'                   => $group_id,
                        'price'                  => $pricing,
                        'flat_or_discount_price' => $price_type,
                        'product_id'             => $product_id,
                        'min_qty'                => $qty,
                    ), array( 'group_id' => $group_id, 'product_id' => $product_id, 'min_qty' => $qty ));

                    if ($update_status) {
                        $subruleManager->deactivateSubrulesOfGroupForProduct($product_id, $group_id, $qty);
                    }
                } else {
                    $wpdb->insert($group_product_table, array(
                        'group_id'                => $group_id,
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

        public function removeGroupProductList($product_id, $group_product_table)
        {
            global $wpdb;
            global $post, $subruleManager, $cspFunctions;

            $deleteGroups           = array();
            $deleteQty              = array();
            $deletedValues          = array();
            $newArray               = array();
            $user_names             = '';
            $userType               = 'group_id';
            if (isset($_POST[ 'wdm_woo_groupname' ])) {
                foreach ($_POST[ 'wdm_woo_groupname' ] as $index => $wdmSingleUser) {
                    $newArray[] = array(
                            'group_id'    => $wdmSingleUser,
                            'min_qty' => $_POST[ 'wdm_woo_group_qty' ][ $index ]
                        );
                }

                $user_names = "('" . implode("','", $_POST[ 'wdm_woo_groupname' ]) . "')";
                $qty = "(" . implode(",", $_POST[ 'wdm_woo_group_qty' ]) . ")";

                $existing = $wpdb->get_results($wpdb->prepare("SELECT group_id, min_qty FROM {$group_product_table} WHERE product_id = %d", $product_id), ARRAY_A);

                $deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType);

                foreach ($deletedValues as $key => $value) {
                    $deleteGroups[] = $existing[$key][$userType];
                    $deleteQty[]   = $existing[$key]['min_qty'];
                }

                $mapping_count = count($deletedValues);
                if ($mapping_count > 0) {
                    foreach ($deleteGroups as $index => $singleGroup) {
                        $query = "DELETE FROM $group_product_table WHERE group_id = %d AND min_qty = %d AND product_id = %d";
                        $wpdb->get_results($wpdb->prepare($query, $singleGroup, $deleteQty[$index], $product_id));
                    }
                    $subruleManager->deactivateSubrulesForGroupsNotInArray($product_id, $deleteGroups, $deleteQty);
                }
            }
        }
    }
}