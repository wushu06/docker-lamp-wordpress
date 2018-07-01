<?php

namespace cspFunctions;

if (! class_exists('WdmWuspFunctions')) {

    class WdmWuspFunctions
    {
        private static $instance;
        /**
         * Returns the *Singleton* instance of this class.
         *
         * @return Singleton The *Singleton* instance.
         */
        public static function getInstance()
        {
            if (null === static::$instance) {
                static::$instance = new static();
            }

            return static::$instance;
        }

        public function saveCustomerPricingPair($customer_ids, $product_values, $product_quantities, $product_actions, $query_title, $option_name, $current_query_id = null)
        {
            global $wpdb;
            $selection_details[ 'table_name' ]       = $wpdb->prefix . 'wusp_user_pricing_mapping';
            $selection_details[ 'selection_column' ] = 'user_id';
            $selection_details[ 'selection_type' ]   = 'customer';

            return self::saveSelectionPairs($selection_details, $customer_ids, $product_values, $product_quantities, $product_actions, $query_title, $option_name, $current_query_id);
        }

//function ends -- saveCustomerPricingPair

        public function saveRolePricingPair($role_list, $product_values, $product_quantities, $product_actions, $query_title, $option_name, $current_query_id = null)
        {
            global $wpdb;

            $selection_details[ 'table_name' ]       = $wpdb->prefix . 'wusp_role_pricing_mapping';
            $selection_details[ 'selection_column' ] = 'role';
            $selection_details[ 'selection_type' ]   = 'role';

            return self::saveSelectionPairs($selection_details, $role_list, $product_values, $product_quantities, $product_actions, $query_title, $option_name, $current_query_id);
        }

//function ends -- saveRolePricingPair

        public function saveGroupPricingPair($group_ids, $product_values, $product_quantities, $product_actions, $query_title, $option_name, $current_query_id = null)
        {
            global $wpdb;

            $selection_details[ 'table_name' ]       = $wpdb->prefix . 'wusp_group_product_price_mapping';
            $selection_details[ 'selection_column' ] = 'group_id';
            $selection_details[ 'selection_type' ]   = 'group';

            return self::saveSelectionPairs($selection_details, $group_ids, $product_values, $product_quantities, $product_actions, $query_title, $option_name, $current_query_id);
        }

//function ends -- saveGroupPricingPair

        private static function checkPricingRow($table_name, $option_type, $option_key)
        {
            global $wpdb;

            $query   = "Select Count(`id`) from `" . $table_name . "` where " . $option_key . " = '" . $option_type . "'";
            $result  = $wpdb->get_var($query);
            return $result;
        }

        /**
         * Sort a 2 dimensional array based on 1 or more indexes.
         *
         * msort() can be used to sort a rowset like array on one or more
         * 'headers' (keys in the 2th array).
         *
         * @param array        $array      The array to sort.
         * @param string|array $key        The index(es) to sort the array on.
         * @param int          $sort_flags The optional parameter to modify the sorting
         *                                 behavior. This parameter does not work when
         *                                 supplying an array in the $key parameter.
         *
         * @return array The sorted array.
         */
        public function msort($array, $key)
        {
            if (is_array($array) && count($array) > 0) {
                if (!empty($key)) {
                    $mapping = array();
                    foreach ($array as $k => $v) {
                        $sort_key = '';
                        if (!is_array($key)) {
                            $sort_key = $v[$key];
                        }
                        $mapping[$k] = $sort_key;
                    }
                    asort($mapping, SORT_REGULAR);
                    $sorted = array();
                    foreach ($mapping as $k => $v) {
                        $sorted[$k] = $array[$k];
                    }
                    return $sorted;
                }
            }
            return $array;
        }

        public function hasQty($array, $qty)
        {
            $qtyArray = $this->getArrayColumn($array, 'min_qty');

            if (count($qtyArray) > 0 && in_array($qty, $qtyArray)) {
                return true;
            }
            return false;
        }

        public function hasQtyInPriceArray($qtysArray, $qty)
        {
            if (count($qtysArray) > 0 && in_array($qty, $qtysArray)) {
                return true;
            }
            return false;
        }

        public function mergeProductCatPriceSearch($productPrices, $catPrices)
        {
            $allPrices = array();
            foreach ($productPrices as $key => $record) {
                if (isset($catPrices[$record['product_id']]) && isset($catPrices[$record['product_id']][$record['min_qty']])) {
                    continue;
                } else {
                    $allPrices[] = $record;
                }
            }
            
            foreach ($catPrices as $key => $record) {
                foreach ($record as $value) {
                    $allPrices[] = $value;
                }
            }
            return $allPrices;
        }

        public function getProductCategories($product)
        {
            $productId = $product->get_id();
            if ('simple' === $product->get_type()) {
                $productId = $product->get_id();
            }
       
            if ('variation' === $product->get_type()) {
                if (version_compare(WC_VERSION, '3.0', '<')) {
                    $productId = $product->id;
                } else {
                    $productId = $product->get_parent_id();
                }
            }  
            return wp_get_post_terms($productId, 'product_cat');                    
        }

        private static function saveSelectionPairs($selection_details, $selection_ids, $product_values, $product_quantities, $product_actions, $query_title, $option_name, $current_query_id = null)
        {
            global $ruleManager, $subruleManager;
            $ruleCreated     = true;
            $error                       = '';
            $subrulesOfRule              = array();
            //Create Main Rule
            $rule_id                     = $current_query_id;

            if (empty($current_query_id)) {
                $rule_id = $ruleManager->addRule($query_title, $selection_details[ 'selection_type' ]);
                //If main rule can not be created, Return here
                if ($rule_id === false) {
                    return;
                }
            } else {
                $subrulesOfRule = self::getSubruleOfRule($current_query_id, $rule_id, $query_title, $selection_details);
            }

            $selection_entry_list    = array();
            $price_list              = array();
            $product_id_list         = array();

            $row_count           = 0;
            $total_process_count = count($selection_ids) * count($product_values);

            $selection_entry_list = self::loopSelectionIds($selection_ids, $product_values, $product_quantities, $product_actions, $option_name, $row_count, $total_process_count, $selection_details, $selection_entry_list, $product_id_list, $rule_id, $price_list);

            delete_option($option_name . '_value');
            delete_option($option_name . '_status');

            if ($subruleManager->countSubrules($rule_id) == 0) {
                $ruleManager->deleteRule($rule_id);
                $error                   = __('Rule could not be created', CSP_TD);
                $ruleCreated = false;
            }
            $subruleErrors = trim($subruleManager->errors);
            $ruleErrors = trim($ruleManager->errors);
            if (!empty($subruleErrors) || !empty($ruleErrors)) {
                $ruleCreated = false;
            }

            if ($ruleCreated) {
                //Delete old subrules asssociated with current rule
                if (! empty($current_query_id)) {
                    $subruleManager->deleteSubrules($subrulesOfRule);
                }
                
                $ruleManager->updateTotalNumberOfSubrules($rule_id);
                $ruleManager->setUnusedRulesAsInactive();
            }
            return self::sendSelectionResult($ruleCreated, $ruleManager->errors . ' ' . $subruleManager->errors . ' ' . $error, $selection_entry_list, $rule_id, $current_query_id);
        } //function ends -- saveSelectionPairs

        private static function loopSelectionIds($selection_ids, $product_values, $product_quantities, $product_actions, $option_name, $row_count, $total_process_count, $selection_details, $selection_entry_list, $product_id_list, $rule_id, $price_list)
        {
            if (! empty($product_values) && ! empty($selection_ids)) {
                foreach ($product_values as $key => $price) {
                    $pattern = trim(str_replace('csp_value_', '', $key));
                    $temp=preg_match_all("/(\\d+)/is", $pattern, $matches);
                    $productId = $matches[1][0];
                    $userId = trim(str_replace("{$productId}_", '', $pattern));

                    $check_rows_exist = self::checkPricingRow($selection_details[ 'table_name' ], $userId, $selection_details[ 'selection_column' ]);
                    $selection_entry_list = self::loopProductValues($productId, $product_quantities, $product_actions, $check_rows_exist, $option_name, $row_count, $total_process_count, $selection_details, $userId, $selection_entry_list, $product_id_list, $rule_id, $price_list, $price);
                }//foreach ends
            }
            return $selection_entry_list;
        } //end of function loopSelectionIds

        private static function getSubruleOfRule($current_query_id, $rule_id, $query_title, $selection_details)
        {
            global $ruleManager, $subruleManager;
            if (! is_numeric($current_query_id)) {
                return;
            }

            $ruleUpdateStatus    = $ruleManager->updateRule($rule_id, array(
                'rule_title' => $query_title,
                'rule_type' => $selection_details[ 'selection_type' ] ));
            $subrulesOfRule      = $subruleManager->getSubruleIds($rule_id);

            //If main rule can not be created, Return here
            if ($ruleUpdateStatus === false) {
                return;
            }
            return $subrulesOfRule;
        } //end of function getSubruleOfRule

        private static function loopProductValues($productId, $product_quantities, $product_actions, $check_rows_exist, $option_name, $row_count, $total_process_count, $selection_details, $userId, $selection_entry_list, $product_id_list, $rule_id, $price_list, $price)
        {
            global $wpdb, $subruleManager;

            self::updateProgressOption($row_count, $total_process_count, $option_name);
            $row_count = $row_count + 1;

            if ($price != '' && floatval($price) >= 0) {
                update_option($option_name . "_status", __('Processing Product ID ', CSP_TD) . ' ' . $productId);

                $flat_or_discount    = isset($product_actions[ 'wdm_csp_price_type' . $productId.'_'.$userId ]) ? ($product_actions[ 'wdm_csp_price_type' . $productId.'_'.$userId ] == 2 ? 2 : 1) : 1;

                $quantity = isset($product_quantities[ 'csp_qty_' . $productId.'_'.$userId ]) ? $product_quantities[ 'csp_qty_' . $productId.'_'.$userId ] : 1;
                if ($check_rows_exist > 0) {
                    $check_exists        = "SELECT `id` FROM `" . $selection_details[ 'table_name' ] . "` WHERE `" . $selection_details[ 'selection_column' ] . "` = '" . $userId . "' AND `product_id` = '" . $productId . "' AND `min_qty` = '".$quantity."'";
                    $row_exist_result    = $wpdb->get_results($check_exists);

                    if ($row_exist_result && isset($row_exist_result[ 0 ])) {
                        //Update the result
                        self::updateSelectionPair($selection_details[ 'selection_type' ], $row_exist_result[ 0 ]->id, $userId, $productId, $price, $flat_or_discount, $quantity);
                        $selection_entry_list[]  = intval($row_exist_result[ 0 ]->id);
                        $price_list[]            = $price;
                    } else {
                        //Insert the result
                        $selection_entry_list[]  = self::setSelectionPair($selection_details[ 'selection_type' ], $userId, $productId, $price, $flat_or_discount, $quantity);
                        $price_list[]            = $price;
                    }
                } else {
                    $selection_entry_list[]  = self::setSelectionPair($selection_details[ 'selection_type' ], $userId, $productId, $price, $flat_or_discount, $quantity);
                    $price_list[]            = $price;
                }
                $subruleManager->addSubrule($rule_id, $productId, $quantity, $flat_or_discount, $price, $selection_details[ 'selection_type' ], $userId);

                if (! in_array($productId, $product_id_list)) {
                    $product_id_list[] = $productId;
                }
            }//if ends -- Price not empty
            // }//foreach ends
            return $selection_entry_list;
        } //end processProductValues

        public function getArrayColumn($array, $column)
        {
            $result = array();
            if (!is_array($array) || empty($column)) {
                return $result;
            } else {
                foreach ($array as $value) {
                    $result[] = $value->$column;
                }
                return $result;
            }
        }

        public function priceForQuantity($quantity, $priceArray, $regular_price)
        {
            if (count($priceArray) == 0) {
                return false;
            }

            foreach ($priceArray as $a) {
                if ($a->min_qty == $quantity) {
                    if ($a->price_type == 2) {
                        return ($regular_price) - (round(($a->price * $regular_price), wc_get_price_decimals()) / 100);
                    }
                    return $a->price;
                }
            }
        }

        public function priceForSearchQuantity($quantity, $priceArray, $product)
        {
            $regularPrice = floatval(get_post_meta($product->get_id(), '_regular_price', true));
            if (count($priceArray) == 0) {
                return false;
            }

            foreach ($priceArray as $a) {
                if ($a->min_qty == $quantity) {
                    return $this->getCSPArray($a, $a->price, $product);
                }
            }
        }

        public function getCSPArray($a, $price, $product)
        {
            $cspPrice = array();
            $cspPrice['price'] = $price;
            $cspPrice['min_qty'] = $a->min_qty;
            $cspPrice['price_type'] = $a->price_type;
            $cspPrice['product_id'] = $product->get_id();
            if (property_exists($a, 'cat_slug')) {
                $cspPrice['price_set'] = $a->price_set;
                $cspPrice['source'] = $a->cat_slug;
            } else {
                $cspPrice['source'] = 'Direct';
            }
            
            return $cspPrice;
        }

        public function getVariationId($product)
        {
            return $product->get_children();
        }

        private static function selectionListMessage($message, $rule_id, $error = true)
        {
            $divClass = 'updated';
            if ($error) {
                $divClass = 'error';
            }
            return '<div rule_id="' . $rule_id . '" class="' . $divClass . ' wdm_result"><p>' . $message . '</p></div>';
        }

        private static function sendSelectionResult($ruleCreated, $message, $selection_entry_list, $rule_id, $current_query_id = null)
        {

            if (! empty($selection_entry_list)) {
                if (! $ruleCreated) {
                    return self::selectionListMessage($message, $rule_id);
                } else {
                    $message = trim($message);
                    if (empty($message)) {
                        if (empty($current_query_id)) {
                            $message = sprintf(__('Rule created successfully. Click %s here %s to add new rule.', CSP_TD), '<a href="admin.php?page=customer_specific_pricing_single_view&tabie=product_pricing">', '</a>');
                        } else {
                            $message = sprintf(__('Rule updated successfully. Click %s here %s to add new rule.', CSP_TD), '<a href="admin.php?page=customer_specific_pricing_single_view&tabie=product_pricing">', '</a>');
                        }
                    }

                    return self::selectionListMessage($message, $rule_id, false);
                }
            } else {
                return self::selectionListMessage(__('Values may be improper.', CSP_TD), $rule_id);
            }
        }

        private static function updateProgressOption($count, $total_rows, $option_name)
        {
            if ($count === 0) {
                update_option($option_name . "__value", '10');
                update_option($option_name . "_status", __('Initializing', CSP_TD));
            } elseif ($count === $total_rows) {
                update_option($option_name . "_value", '95');
            } elseif ($count > abs($total_rows * 0.75)) {
                update_option($option_name . "_value", '80');
            } elseif ($count > abs($total_rows * 0.5)) {
                update_option($option_name . "_value", '60');
            } elseif ($count > abs($total_rows * 0.4)) {
                update_option($option_name . "_value", '40');
            } elseif ($count > abs($total_rows * 0.2)) {
                update_option($option_name . "_value", '22');
            } else {
                update_option($option_name . "_value", '15');
            }
        }

        private static function setSelectionPair($selection_type, $selection_id, $product_id, $price, $flat_or_discount, $quantity)
        {
            //Insert the result
            $insert_id = -1;

            if ($selection_type === 'customer') {
                \WdmCSP\WdmWuspAddDataInDB::insertPricingInDb($selection_id, $product_id, $flat_or_discount, $price, $quantity);
            } elseif ($selection_type === 'role') {
                $insert_id = \WdmCSP\WdmWuspAddDataInDB::insertRoleProductMappingInDb($selection_id, $product_id, $flat_or_discount, $price, $quantity);
            } elseif ($selection_type === 'group') {
                $insert_id = \WdmCSP\WdmWuspAddDataInDB::insertGroupProductPricingInDb($selection_id, $product_id, $flat_or_discount, $price, $quantity);
            }

            return $insert_id;
        }

        private static function updateSelectionPair($selection_type, $existing_id, $selection_id, $product_id, $price, $flat_or_discount, $quantity)
        {
            if ($selection_type === 'customer') {
                \WdmCSP\WdmWuspUpdateDataInDB::updateUserPricingInDb($existing_id, $price, $flat_or_discount, $quantity);
            } elseif ($selection_type === 'role') {
                \WdmCSP\WdmWuspUpdateDataInDB::updateRolePricingInDb($existing_id, $selection_id, $product_id, $price, $flat_or_discount, $quantity);
            } elseif ($selection_type === 'group') {
                \WdmCSP\WdmWuspUpdateDataInDB::updateGroupPricingInDb($existing_id, $selection_id, $product_id, $price, $flat_or_discount, $quantity);
            }
        }

        public function searchAllOccurrences($arr, $needle)
        {
            $array_keys = array();
            foreach ($arr as $key => $value) {
                if ($value == $needle) {
                    array_push($array_keys, $key);
                }
            }
            return $array_keys;
        }

        public function multiArrayDiff($array1, $array2, $userType, $cat = false)
        {
            $res = false;
            $newArray = array();
            foreach ($array2 as $key => $val) {
                if ($cat) {
                    $res = self::compareArrayCategory($array1, $val, $userType);
                } else {
                    $res = self::compareArrayProduct($array1, $val, $userType);
                }
                if (!$res) {
                    $newArray[$key] = $val;
                }
            }
            return $newArray;
        }

        public function compareArrayProduct($arr1, $arr2, $userType)
        {
            foreach ($arr1 as $val) {
                if ($val[$userType] == $arr2[$userType] && $val['min_qty'] == $arr2['min_qty']) {
                    return true;
                }
            }
            return false;
        }

        public function compareArrayCategory($arr1, $arr2, $userType)
        {
            foreach ($arr1 as $val) {
                if ($val[$userType] == $arr2[$userType] && $val['min_qty'] == $arr2['min_qty'] && $val['cat_slug'] == $arr2['cat_slug']) {
                    return true;
                }
            }
            return false;
        }
    }
}
$GLOBALS['cspFunctions'] = WdmWuspFunctions::getInstance();
