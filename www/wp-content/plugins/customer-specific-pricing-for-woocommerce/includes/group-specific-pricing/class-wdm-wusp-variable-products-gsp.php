<?php

namespace WuspVariableProduct\WgspVariableProduct;

if (! class_exists('WdmWuspVariableProductsGsp')) {

    /**
     * Class to Display & Process data of Variable Products for Group Specific Pricing
     */
    //class declartion
    class WdmWuspVariableProductsGsp
    {

        public function __construct()
        {
            global $wdmWuspPluginData;
            //including the below file to get the function to validate license
            include_once(dirname(dirname(__FILE__)) . '/class-wdm-wusp-get-data.php');
            $getDataFromDb = \WdmCSP\WdmGetLicenseData::getDataFromDb($wdmWuspPluginData);
            if ('available' == $getDataFromDb) {
                //Save variation fields
                add_action('admin_enqueue_scripts', array( $this, 'adminScripts' ), 99);
                //add_action('woocommerce_process_product_meta_variable', array($this, 'processGroupPricingPairs'), 4, 1);
                add_action('wdm_add_after_variable_csp', array( $this, 'variableGroupFields' ), 10, 2);
                //Update group pricing whenever the 'Save Changes' button is clicked on edit variable product page.
                add_action('woocommerce_ajax_save_product_variations', array( $this, 'processGroupPricingPairs' ));
                add_action('save_post_product', array( $this, 'processGroupPricingPairs' ), 10);
            }
        }

        public function adminScripts()
        {
            global $post;
            $screen = get_current_screen();

            if (in_array($screen->id, array( 'product', 'edit-product' ))) {
                wp_enqueue_script('wdm-variable-group-product-mapping', plugins_url('/js/variable-products/wdm-group-specific-pricing.js', dirname(dirname(__FILE__))), array( 'jquery' ), CSP_VERSION);
            }
        }

        /**
         * Shows option to set Group-Pricing Pairs for variations
         * @global object $wpdb Database Object
         * @param object $variation_data Variation Data for current variation
         * @param object $variation Basic Variation details for current variation
         */
        public function variableGroupFields($variation_data, $variation)
        {
            /**
             * Check if Groups is active
             */
            $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
            if (in_array('groups/groups.php', $active_plugins)) {
                global $wpdb;
                $discountOptions = array("1"=>__("Flat", CSP_TD), "2"=>"%");
                $groups_dropdown_v   = '';
                $group_product_table = $wpdb->prefix . 'wusp_group_product_price_mapping';
                $group_price_query   = "
				SELECT group_id, price, min_qty, flat_or_discount_price as price_type FROM {$group_product_table} WHERE product_id = %d
				";
                $group_price_result  = null;
                if (! isset($variation_data[ 'variation_post_id' ])) {
                    $variation_data[ 'variation_post_id' ] = $variation->ID;
                }
                if (isset($variation_data[ 'variation_post_id' ])) {
                    $group_price_result = $wpdb->get_results($wpdb->prepare($group_price_query, $variation_data[ 'variation_post_id' ]));
                }
                ?>
                <h3 class="wdm-heading"><?php _e('Group Based Pricing', CSP_TD) ?></h3>
                <div>
                    <tr>
                        <td colspan="2"/>
                    <div class="wdm_group_price_mapping_wrapper">

                        <?php
                        /**
                         * Fetch WordPress groups. And creating a Dropdown List
                         */
                        $groups_table    = $wpdb->prefix . 'groups_group';
                        $group_names     = $wpdb->get_results("SELECT group_id, name FROM {$groups_table}");
                        $html            = "<select name='wdm_woo_variation_groupname[" . $variation_data[ "variation_post_id" ] . "][]' id='grp_" . $variation_data[ "variation_post_id" ] . "'  class='chosen-select'>";
                        foreach ($group_names as $single_group_name) {
                            $html .= "<option value=" . $single_group_name->group_id . ' >' . esc_html($single_group_name->name) . "</option>";
                        }
                        $html .= '</select>';
                        $wdm_groups_dropdown = $html;
                        $groups_dropdown_v   = $wdm_groups_dropdown;
                        ?>

                        <span style="display:none" class="wdm_hidden_group_dropdown_csp"><?php echo base64_encode(str_replace("\n", '', $groups_dropdown_v)); ?></span>
                        <span  style="display:none" class="wdm_hidden_variation_group_data_csp"><?php echo $variation_data[ 'variation_post_id' ]; ?></span>
                        <table style="clear:both" class="wdm_variable_product_gsp_table" rel='<?php echo $variation_data[ 'variation_post_id' ]; ?>' id='var_g_tab_<?php echo $variation_data[ 'variation_post_id' ]; ?>' >
                            <thead>
                            <tr>
                                <th>
                                    <?php _e('Group Name', CSP_TD) ?>
                                </th>
                                <th>
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
                            <tbody>
                            <?php
                            //Add condition to check if array is not empty
                            $discountType = 1;
                            $cspPercentClass = "wdm_price csp-percent-discount";
                            if (! empty($group_price_result)) {
                                foreach ($group_price_result as $key => $rows) {
                                    /**
                                     * Fetch WordPress groups. And creating a Dropdown List
                                     */
                                    $groups_table    = $wpdb->prefix . 'groups_group';
                                    $group_names     = $wpdb->get_results("SELECT group_id, name FROM {$groups_table}");
                                    $html            = "<select name='wdm_woo_variation_groupname[" . $variation_data[ 'variation_post_id' ] . "][]' id='grp_" . $variation_data[ 'variation_post_id' ] . "' class='chosen-select'>";
                                    foreach ($group_names as $single_group_name) {
                                        $html .= "<option value=" . $single_group_name->group_id . " " . (($single_group_name->group_id === $rows->group_id) ? ' selected ' : ' ') . ' >' . esc_html($single_group_name->name) . "</option>";
                                    }
                                    $html .= '</select>';
                                    $wdm_groups_dropdown = $html;
                                    $groups_dropdown_v   = $wdm_groups_dropdown;
                                    ?>
                                    <tr>
                                        <td><?php echo str_replace("\n", '', $groups_dropdown_v); ?></td>
                                        <td><select name='wdm_group_price_type_v[<?php echo $variation_data[ 'variation_post_id' ]; ?>][]' class='chosen-select csp_wdm_action'>
                                            <?php

                                            foreach ($discountOptions as $i => $value) {
                                                if ($rows->price_type == $i) {
                                                    $discountType = $i;
                                                    echo "<option value = '".$rows->price_type."' selected>".$value."</option>";
                                                } else {
                                                    echo "<option value = '".$i."'>".$value."</option>";
                                                }
                                            }
                                            if ($discountOptions[$discountType] == "Flat") {
                                                $cspPercentClass = "wdm_price";
                                            }
                                            ?>
                                        </select></td>
                                        <td><input type="number" min="1" size="5" name="wdm_woo_variation_group_qty[<?php echo $variation_data[ 'variation_post_id' ]; ?>][]" value="<?php echo $rows->min_qty; ?>" class="wdm_qty"/></td>
                                        <td><input type="text"  size="5" name="wdm_woo_variation_group_price[<?php echo $variation_data[ 'variation_post_id' ]; ?>][]" value="<?php echo wc_format_localized_price($rows->price); ?>" class="<?php echo $cspPercentClass ?>"/></td>
                                        <td class="remove_var_g_csp" style="color:red;cursor: pointer;"><img src='<?php echo plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__))) ?>'></td>
                                        <?php
                                        if ($key === (sizeof($group_price_result) - 1)) {
                                            ?>
                                            <td class="add_var_g_csp" style="color:green;cursor: pointer;"><img src='<?php echo plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__))); ?>'></td>

                                            <?php
                                        }
                                        ?>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr class="single_variable_csp_row">
                                    <td><?php echo str_replace("\n", '', $groups_dropdown_v); ?></td>   
                                    <td><select name='wdm_group_price_type_v[<?php echo $variation_data[ 'variation_post_id' ]; ?>][]' class='chosen-select csp_wdm_action'>
                                    <?php
                                    foreach ($discountOptions as $i => $value) {
                                        if (1 == $i) {
                                            echo "<option value = '".$i."' selected>".$value."</option>";
                                        } else {
                                            echo "<option value = '".$i."'>".$value."</option>";
                                        }
                                    }
                                    ?>
                                    </select></td>
                                    <td><input type="number" min = "1" size="5" name="wdm_woo_variation_group_qty[<?php echo $variation_data[ 'variation_post_id' ]; ?>][]" class="wdm_qty"/>
                                    <td><input type="text" size="5" name="wdm_woo_variation_group_price[<?php echo $variation_data[ 'variation_post_id' ]; ?>][]" class="wdm_price"/>
                                    <td class="remove_var_g_csp" style="color:red;cursor: pointer;"><img src='<?php echo plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__))) ?>'></td>
                                    <td class="add_var_g_csp" style="cursor: pointer;"><img src='<?php echo plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__))); ?>'></td>
                                </tr>

                                <?php
                            }
                            unset($groups_dropdown_v); //Unset groups dropdown variable
                            ?>
                            </tbody>
                        </table>
                    </div> <!-- End Test Div -->
                </td>
                </tr>
                </div>
                <?php
            } else {
                ?><h3 class="wdm-heading"><?php _e('Group Based Pricing', CSP_TD) ?></h3>
                <div  id="group_specific_pricing_tab_data">
                    <?php _e("Activate the 'Groups' Plugin to enjoy the benefits of Group Specific Pricing.", CSP_TD) ?>
                </div><?php
            }
        }


        public function displayDiscountOptions($discountOptions, $rows)
        {
            foreach ($discountOptions as $i => $value) {
                if ($rows->price_type == $i) {
                    echo "<option value = '".$rows->price_type."' selected>".$value."</option>";
                } else {
                    echo "<option value = '".$i."'>".$value."</option>";
                }
            }
        }

        /**
         * Saves Group-Pricing Pairs for Variable Products in the database
         * @global object $wpdb Database object
         * @param int $post_id ID of the Post in context
         */
        public function processGroupPricingPairs()
        {
            global $wpdb;
            $group_product_table = $wpdb->prefix . 'wusp_group_product_price_mapping';

            /**
             * Check if Groups is active
             */
            $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
            if (in_array('groups/groups.php', $active_plugins)) {
                if (! isset($_POST[ 'variable_post_id' ]) || empty($_POST[ 'variable_post_id' ])) {
                    return;
                }

                $variable_post_id = $_POST[ 'variable_post_id' ];

                if (! isset($_POST[ 'wdm_woo_variation_groupname' ]) || ! isset($_POST[ 'wdm_woo_variation_group_price' ])|| ! isset($_POST[ 'wdm_woo_variation_group_qty' ])) {
                    foreach ($variable_post_id as $single_post_id) {
                        if (! isset($single_post_id)) {
                            //delete record, as  all records removed for particular variation
                            continue;
                        }
                        $var_id = (int) $single_post_id;
                        $wpdb->delete($group_product_table, array(
                            'product_id' => $var_id,
                        ));
                    }
                } else {
                    self::processGroupVariationLoop($_POST[ 'wdm_woo_variation_group_price' ], $_POST[ 'wdm_woo_variation_group_qty' ], $_POST[ 'wdm_group_price_type_v' ], $_POST[ 'wdm_woo_variation_groupname' ], $_POST[ 'variable_post_id' ]);
                }
            }
        }

//function ends -- processGroupPricingPairs

        private function processGroupVariationLoop($variable_price_field, $variable_qty_field, $variable_price_type, $variable_csp_group, $variable_post_id)
        {
            global $wpdb, $subruleManager, $wdmWuspPluginData;
            //including the below file to get the function to validate license
            include_once(dirname(dirname(__FILE__)) . '/class-wdm-wusp-get-data.php');
            $getDataFromDb = \WdmCSP\WdmGetLicenseData::getDataFromDb($wdmWuspPluginData);
            if ('available' == $getDataFromDb) {
                $max_loop            = max(array_keys($variable_post_id));

                //Loop through all variations
                for ($i = 0; $i <= $max_loop; $i ++) {
                    if (! isset($variable_post_id[ $i ])) {
                        continue;
                    }

                    $var_id = (int) $variable_post_id[ $i ];

                    self::addVariationGroupPriceMappingInDb($variable_csp_group, $variable_qty_field, $variable_price_field, $variable_price_type, $var_id);
                }//foreach ends
            }//if ends
        }

        private function addVariationGroupPriceMappingInDb($variable_csp_group, $variable_qty_field, $variable_price_field, $variable_price_type, $var_id)
        {
            global $wpdb, $subruleManager, $cspFunctions;
            $temp_group_qty_array   = array();
            $deleteGroups           = array();
            $deleteQty              = array();
            $deletedValues          = array();
            $newArray               = array();
            $group_product_table    = $wpdb->prefix . 'wusp_group_product_price_mapping';
            $user_names             = '';
            $userType               = 'group_id';

            if (isset($variable_csp_group[$var_id])) {
                foreach ($variable_csp_group[$var_id] as $index => $wdmSingleUser) {
                    $newArray[] = array(
                            'group_id'  => $wdmSingleUser,
                            'min_qty'   => $variable_qty_field[$var_id][ $index ]
                        );
                }

                $user_names = "('" . implode("','", $variable_csp_group[$var_id]) . "')";
                $qty = "(" . implode(",", $variable_qty_field[$var_id]) . ")";

                $existing = $wpdb->get_results($wpdb->prepare("SELECT group_id, min_qty FROM {$group_product_table} WHERE product_id = %d", $var_id), ARRAY_A);

                $deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType);

                foreach ($deletedValues as $key => $value) {
                    $deleteGroups[] = $existing[$key][$userType];
                    $deleteQty[]    = $existing[$key]['min_qty'];
                }

                $mapping_count = count($deletedValues);
                if ($mapping_count > 0) {
                    foreach ($deleteGroups as $index => $singleGroup) {
                        $query = "DELETE FROM $group_product_table WHERE group_id = %d AND min_qty = %d AND product_id = %d";
                        $wpdb->get_results($wpdb->prepare($query, $singleGroup, $deleteQty[$index], $var_id));
                    }
                    $subruleManager->deactivateSubrulesForGroupsNotInArray($var_id, $deleteGroups, $deleteQty);
                }
            }

            if (isset($variable_csp_group[$var_id]) && ! empty($variable_qty_field[$var_id])) {
                foreach ($variable_csp_group[$var_id] as $index => $wdm_woo_group_id) {
                    if (isset($wdm_woo_group_id)) {
                        $groupQtyPair = $wdm_woo_group_id."-".$variable_qty_field[$var_id][ $index ];
                        if (! in_array($groupQtyPair, $temp_group_qty_array)) {
                            array_push($temp_group_qty_array, $groupQtyPair);
                            $group_id = $wdm_woo_group_id;
                            $qty = $variable_qty_field[$var_id][ $index ];
                            if (isset($variable_price_field[$var_id][ $index ]) && isset($variable_price_type[$var_id][ $index ]) && isset($qty) && !($qty <= 0)) {
                                $pricing = wc_format_decimal($variable_price_field[$var_id][ $index ]);
                                $price_type = $variable_price_type[$var_id][ $index ];

                                if (! empty($group_id) && ! empty($pricing) && ! empty($price_type)) {
                                    $result = $wpdb->get_results($wpdb->prepare("SELECT id FROM $group_product_table WHERE group_id = '%d' and min_qty = '%d' and product_id=%d", $wdm_woo_group_id, $qty, $var_id));
                                    if (count($result) > 0) {
                                        $update_status = $wpdb->update($group_product_table, array(
                                            'group_id'                  => $group_id,
                                            'price'                     => $pricing,
                                            'flat_or_discount_price'    => $price_type,
                                            'product_id'                => $var_id,
                                            'min_qty'                   => $qty,
                                        ), array( 'group_id' => $group_id, 'product_id' => $var_id, 'min_qty' => $qty ));

                                        if ($update_status) {
                                            $subruleManager->deactivateSubrulesOfGroupForProduct($var_id, $group_id, $qty);
                                        }
                                    } else {
                                        $wpdb->insert($group_product_table, array(
                                            'group_id'                  => $group_id,
                                            'price'                     => $pricing,
                                            'flat_or_discount_price'    => $price_type,
                                            'product_id'                => $var_id,
                                            'min_qty'                   => $qty,
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
                            if (empty($pricing)) {
                                $wpdb->delete(
                                    $group_product_table,
                                    array(
                                    'group_id'      => $group_id,
                                    'product_id'    => $var_id,
                                    'min_qty'       => $qty,
                                    ),
                                    array(
                                    '%d',
                                    '%d',
                                    '%d',
                                    )
                                );
                                $subruleManager->deactivateSubrulesOfGroupForProduct($var_id, $group_id, $qty);
                            }
                        }
                    }
                }//foreach ends
            } else {
                $wpdb->delete(
                    $group_product_table,
                    array(
                    'product_id' => $var_id,
                    ),
                    array(
                    '%d',
                    )
                );
                $subruleManager->deactivateSubrulesOfAllGroupsForProduct($var_id);
            }
        }

//end if 'available'
    }

}