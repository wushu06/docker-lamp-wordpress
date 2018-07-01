<?php

namespace WuspVariableProduct\WrspVariableProduct;

//check whether a class with the same name exists
if (! class_exists('WdmWuspVariableProductsRsp')) {

    /**
     * Class to Display & Process data of Variable Products for Role Specific Pricing
     */
    //class declartion
    class WdmWuspVariableProductsRsp
    {

        public function __construct()
        {
            global $wdmWuspPluginData;
            //including the below file to get the function to validate license
            include_once(dirname(dirname(__FILE__)) . '/class-wdm-wusp-get-data.php');
            $getDataFromDb = \WdmCSP\WdmGetLicenseData::getDataFromDb($wdmWuspPluginData);
            if ('available' == $getDataFromDb) {
                add_action('admin_enqueue_scripts', array( $this, 'adminScripts' ), 99);
                add_action('wdm_add_after_variable_csp', array( $this, 'cspDisplayUserRoleForVariableProduct' ), 4, 2);
                //add_action('woocommerce_process_product_meta_variable', array($this, 'processRolePricingPairs'), 4, 1);
                //Update role pricing whenever the 'Save Changes' button is clicked on edit variable product page.
                add_action('woocommerce_ajax_save_product_variations', array( $this, 'processRolePricingPairs' ), 10);
                add_action('save_post_product', array( $this, 'processRolePricingPairs' ), 10);
            }
        }

        public function adminScripts()
        {
            global $post;

            $screen                  = get_current_screen();
            ob_start();
            wp_dropdown_roles();
            $wdm_roles_dropdown_v    = ob_get_contents();
            ob_end_clean();

            if (in_array($screen->id, array( 'product', 'edit-product' ))) {
                    wp_enqueue_script('wdm-variable-product-mapping-v', plugins_url('/js/variable-products/wdm-role-specific-pricing.js', dirname(dirname(__FILE__))), array( 'jquery' ), CSP_VERSION);
                    wp_localize_script('wdm-variable-product-mapping-v', 'wdm_variable_product_role_csp_object', array(
                        'plus_image'                 => plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__))),
                        'minus_image'                => plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__))),
                        'wdm_roles_dropdown_html'    => str_replace("\n", '', $wdm_roles_dropdown_v),
                        'wdm_discount_options'       => array("1"=>__("Flat", CSP_TD), "2"=>"%")
                    ));
            }
        }

        /**
         * Shows option to set Role-Pricing Pairs for variations
         * @global object $wpdb Database Object
         * @param object $variation_data Variation Data for current variation
         * @param object $variation Basic Variation details for current variation
         */
        public function cspDisplayUserRoleForVariableProduct($variation_data, $variation)
        {
            global $wpdb;
            $discountOptions = array("1"=>__("Flat", CSP_TD), "2"=>"%");
            $role_pricing_table = $wpdb->prefix . 'wusp_role_pricing_mapping';

            $role_price_products = null;
            if (! isset($variation_data[ 'variation_post_id' ])) {
                $variation_data[ 'variation_post_id' ] = $variation->ID;
            }
            if (isset($variation_data[ 'variation_post_id' ])) {
                $role_price_products = $wpdb->get_results($wpdb->prepare("
                SELECT role, price, min_qty, flat_or_discount_price as price_type
                FROM {$role_pricing_table}
                WHERE product_id = %d
                Order by `id` ASC", $variation_data[ 'variation_post_id' ]));
            }
            ?>
            <h3 class="wdm-heading"><?php _e('Role Based Pricing', CSP_TD) ?></h3>
            <div>
                <tr>
                    <td colspan="2">
                        <div class="wdm_user_price_mapping_wrapper">
            <?php
            ob_start();
            $wdm_dropdown_content = wp_dropdown_roles();

            $wdm_roles_dropdown_v = ob_get_contents();
            // $minQtyLabel = __('Min Qty', CSP_TD);
            $minQtyTip = __('Set minimum quantity', CSP_TD);
            $priceTip = __('Price will be applicable for min quantity and above.', CSP_TD);
            ob_end_clean();
            ?>
                            <span style="display:none" class="wdm_hidden_user_dropdown_csp"><?php echo base64_encode(str_replace("\n", '', $wdm_dropdown_content)); ?></span>
                            <span  style="display:none" class="wdm_hidden_variation_data_csp"><?php echo $variation_data[ 'variation_post_id' ]; ?></span>
                            <table style="clear:both" class="wdm_variable_product_role_usp_table" rel='<?php echo $variation_data[ 'variation_post_id' ]; ?>' id='var_tab_<?php echo $variation_data[ 'variation_post_id' ]; ?>' >
                                <thead>
                                <tr>
                                    <th>
                                        <?php _e('User Role', CSP_TD) ?>
                                    </th>
                                    <th>
                                        <?php _e('Discount Type', CSP_TD) ?>
                                    </th>
                                    <th><span class='help_tip tips' data-tip='<?php echo esc_attr($minQtyTip) ?>'><?php echo esc_attr(__('Min Qty', CSP_TD)) ?></span>
                                    </th>
                                    <th colspan=3><span class='help_tip tips' data-tip='<?php echo esc_attr($priceTip) ?>'><?php echo esc_attr(__('Value', CSP_TD)) ?></span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
            <?php
            $discountType = 1;
            $cspPercentClass = "wdm_price csp-percent-discount";
            if (! empty($role_price_products)) {
                foreach ($role_price_products as $key => $rows) {
                    ob_start();
                    $wdm_dropdown_content = wp_dropdown_roles($rows->role);

                    $wdm_roles_dropdown_v = ob_get_contents();

                    ob_end_clean();
                    ?>

                                        <tr>
                                            <td><select name='wdm_woo_rolename_v[<?php echo $variation_data[ 'variation_post_id' ]; ?>][]' class='chosen-select'><?php echo str_replace("\n", '', $wdm_roles_dropdown_v); ?></select></td>
                                            <td><select name='wdm_role_price_type_v[<?php echo $variation_data[ 'variation_post_id' ]; ?>][]' class='chosen-select csp_wdm_action'>
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
                                            <td><input type="number" min = "1" size="5" class ="wdm_qty" name="wdm_woo_variation_qty_v[<?php echo $variation_data[ 'variation_post_id' ]; ?>][]" value="<?php echo $rows->min_qty ?>"/></td>
                                            <td><input type="text"  size="5" class ="<?php echo $cspPercentClass ?>" name="wdm_woo_variation_price_v[<?php echo $variation_data[ 'variation_post_id' ]; ?>][]" value="<?php echo wc_format_localized_price($rows->price) ?>"/></td>
                                            <td class="remove_var_csp_v" style="color:red;cursor: pointer;"><img src='<?php echo plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__))) ?>'></td>
                    <?php
                    if ($key === (sizeof($role_price_products) - 1)) {
                        ?>
                                                <td class="add_var_csp_v" style="color:green;cursor: pointer;"><img src='<?php echo plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__))); ?>'></td>

                                                <?php
                    }
                                            ?>
                                        </tr>
                                            <?php
                }
            } else {
                ?>
            <tr class="single_variable_csp_row">
                <td><select name='wdm_woo_rolename_v[<?php echo $variation_data[ 'variation_post_id' ]; ?>][]' class='chosen-select'><?php echo str_replace("\n", "", $wdm_roles_dropdown_v); ?></select></td>
                <td><select name='wdm_role_price_type_v[<?php echo $variation_data[ 'variation_post_id' ]; ?>][]' class='chosen-select csp_wdm_action'>
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
                <td><input type="number" min = "1" size="5" class ='wdm_qty' name="wdm_woo_variation_qty_v[<?php echo $variation_data[ 'variation_post_id' ]; ?>][]" />
                <td><input type="text" size="5" class ='wdm_price' name="wdm_woo_variation_price_v[<?php echo $variation_data[ 'variation_post_id' ]; ?>][]" />
                <td class="remove_var_csp_v" style="color:red;cursor: pointer;"><img src='<?php echo plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__))) ?>'></td>
                <td class="add_var_csp_v" style="cursor: pointer;"><img src='<?php echo plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__))); ?>'></td>
            </tr>

                <?php
            }

            unset($wdm_roles_dropdown_v); //Unset roles dropdown variable
            ?>
                            </tbody>
                            </table>
                        </div> <!-- End Test Div -->

            </div>
            <?php
        }

//end of function cspDisplayUserRoleForVariableProduct

        /**
         * Saves Role-Pricing Pairs for Variable Products in the database
         * @global object $wpdb Database object
         * @param int $post_id ID of the Post in context
         */
        public function processRolePricingPairs()
        {
            global $wpdb;
            $role_pricing_table = $wpdb->prefix . 'wusp_role_pricing_mapping';
            //unused while pushing to git

            if (! isset($_POST[ 'variable_post_id' ]) || empty($_POST[ 'variable_post_id' ])) {
                return;
            }

            $variable_post_id = $_POST[ 'variable_post_id' ];

            if (! isset($_POST[ 'wdm_woo_rolename_v' ]) || ! isset($_POST[ 'wdm_woo_variation_price_v' ])|| ! isset($_POST[ 'wdm_woo_variation_qty_v' ])) {
                foreach ($variable_post_id as $single_post_id) {
                    if (! isset($single_post_id)) {
                        //delete record, as  all records removed for particular variation
                        continue;
                    }
                    $var_id = (int) $single_post_id;
                    $wpdb->delete($role_pricing_table, array(
                        'product_id' => $var_id,
                    ));
                }
            } else {
                self::processRoleVariationLoop($_POST[ 'wdm_woo_variation_price_v' ], $_POST[ 'wdm_woo_variation_qty_v' ], $_POST[ 'wdm_role_price_type_v' ], $_POST[ 'wdm_woo_rolename_v' ], $_POST[ 'variable_post_id' ]);
            }
        }

//function ends

        private function processRoleVariationLoop($variable_price_field, $variable_qty_field, $variable_price_type, $variable_csp_role, $variable_post_id)
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

                    self::addVariationRolePriceMappingInDb($variable_csp_role, $variable_qty_field, $variable_price_field, $variable_price_type, $var_id);
                }//foreach ends
            }//if ends
        }


        private function removeRolePrices($user_in, $var_id)
        {
            global $wpdb;

            $role_pricing_table = $wpdb->prefix . 'wusp_role_pricing_mapping';

            $remaining_products = $wpdb->get_col($wpdb->prepare("SELECT id FROM {$role_pricing_table} WHERE role NOT IN $user_in and product_id=%d", $var_id));

            if ($remaining_products) {
                $rem_id = '(' . implode(',', $remaining_products) . ')';
                if (sizeof($remaining_products) > 0) {
                    $wpdb->query($wpdb->prepare("DELETE FROM {$role_pricing_table}  WHERE product_id = %d ", $rem_id));
                }
                unset($remaining_products);
            }
        }

        /**
         * inserts pricing and role-product mapping in database
         * @global object $wpdb Object responsible for executing db queries
         */
        private function addVariationRolePriceMappingInDb($variable_csp_role, $variable_qty_field, $variable_price_field, $variable_price_type, $var_id)
        {
            global $wpdb, $subruleManager, $cspFunctions;
            $temp_array_role_qty    = array();
            $deleteRoles            = array();
            $deleteQty              = array();
            $deletedValues          = array();
            $newArray               = array();
            $role_pricing_table     = $wpdb->prefix . 'wusp_role_pricing_mapping';
            $role_names             = '';

            if (isset($variable_csp_role[$var_id])) {
                foreach ($variable_csp_role[$var_id] as $index => $wdm_woo_role_name) {
                    $newArray[] = array(
                            'role'    => $wdm_woo_role_name,
                            'min_qty' => $variable_qty_field[$var_id][ $index ]
                        );
                }

                $role_names = "('" . implode("','", $variable_csp_role[$var_id]) . "')";
                $qty = "(" . implode(",", $variable_qty_field[$var_id]) . ")";
                $existing = $wpdb->get_results($wpdb->prepare("SELECT role, min_qty FROM {$role_pricing_table} WHERE product_id = %d", $var_id), ARRAY_A);
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
                        $wpdb->get_results($wpdb->prepare($query, $singleRole, $deleteQty[$index], $var_id));
                    }
                    $subruleManager->deactivateSubrulesForRolesNotInArray($var_id, $deleteRoles, $deleteQty);
                }
            }
            if (isset($variable_csp_role[$var_id]) && ! empty($variable_csp_role[$var_id])) {
                foreach ($variable_csp_role[$var_id] as $index => $wdm_woo_role_name) {
                    if (isset($wdm_woo_role_name)) {
                        $roleQtyPair = $wdm_woo_role_name."-".$variable_qty_field[ $var_id ][ $index ];
                        if (! in_array($roleQtyPair, $temp_array_role_qty)) {
                            array_push($temp_array_role_qty, $roleQtyPair);
                            $role_id = $wdm_woo_role_name;
                            $qty = $variable_qty_field[$var_id][ $index ];

                            if (isset($variable_price_field[$var_id][ $index ]) && isset($variable_price_type[$var_id][ $index ]) && isset($qty) && !($qty <= 0)) {
                                $pricing = wc_format_decimal($variable_price_field[$var_id][ $index ]);
                                $price_type = $variable_price_type[$var_id][ $index ];

                                if (! empty($role_id) && ! empty($pricing) && ! empty($price_type)) {
                                    $result = $wpdb->get_results($wpdb->prepare("SELECT id FROM $role_pricing_table WHERE role = '%s' and min_qty = '%d' and product_id=%d", $wdm_woo_role_name, $qty, $var_id));
                                    if (count($result) > 0) {
                                        $update_status = $wpdb->update($role_pricing_table, array(
                                            'role'                   => $role_id,
                                            'price'                  => $pricing,
                                            'flat_or_discount_price' => $price_type,
                                            'product_id'             => $var_id,
                                            'min_qty'                => $qty,
                                        ), array( 'role' => $role_id, 'product_id' => $var_id, 'min_qty' => $qty ));

                                        if ($update_status) {
                                            $subruleManager->deactivateSubrulesOfRoleForProduct($var_id, $role_id, $qty);
                                        }
                                    } else {
                                        $wpdb->insert($role_pricing_table, array(
                                            'role'                   => $role_id,
                                            'price'                  => $pricing,
                                            'flat_or_discount_price' => $price_type,
                                            'product_id'             => $var_id,
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
                                    'product_id' => $var_id,
                                    'min_qty'    => $qty,
                                    ),
                                    array(
                                    '%s',
                                    '%d',
                                    '%d',
                                    )
                                );
                                $subruleManager->deactivateSubrulesOfRoleForProduct($var_id, $role_id, $qty);
                            }
                        }
                    }
                }//foreach ends
            } else {
                $wpdb->delete(
                    $role_pricing_table,
                    array(
                    'product_id' => $var_id,
                    ),
                    array(
                    '%d',
                    )
                );
                $subruleManager->deactivateSubrulesOfAllRolesForProduct($var_id);
            }
        }

        private function removeProductRolePrice($variable_post_id)
        {
            global $wpdb;

            $role_pricing_table = $wpdb->prefix . 'wusp_role_pricing_mapping';

            foreach ($variable_post_id as $single_post_id) {
                if (isset($single_post_id)) {
                    $var_id = $single_post_id;
                    $wpdb->delete(
                        $role_pricing_table,
                        array(
                        'product_id' => $var_id,
                        ),
                        array(
                        '%d',
                        )
                    );
                }
            }
        }
    }

    //end of class
}//end of if class exists
