<?php

namespace cspSingleView;

if (! class_exists('WdmSubruleManagement')) {

    class WdmSubruleManagement
    {

        public $subruleTable;

        /**
         * @var Singleton The reference to *Singleton* instance of this class
         */
        private static $instance;
        public $errors;

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

        /**
         * Protected constructor to prevent creating a new instance of the
         * *Singleton* via the `new` operator from outside of this class.
         */
        protected function __construct()
        {
            global $wpdb;
            $this->subruleTable = $wpdb->prefix . 'wusp_subrules';
            //$this->getCountOfInactiveSubrulesForRules(array( '2' ));
            //echo count($this->countSubrules(9)); exit;
        }

        private function addError($message)
        {
            $this->errors .= $message;
        }

        public function addSubrule($ruleId, $productId, $quantity, $flatOrDiscountPrice, $price, $ruleType, $associationEntity)
        {
            $price = wc_format_decimal($price);
            if ($price < 0) {
                $this->addError(sprintf(__('Price is not valid for Product Id: %s', CSP_TD), $productId));
                return;
            }
            $ruleType = strtolower($ruleType);
            switch ($ruleType) {
                case "group":
                    $deactivateMethod    = 'deactivateSubrulesOfGroupForProduct';
                    break;
                case "role":
                    $deactivateMethod    = 'deactivateSubrulesOfRoleForProduct';
                    break;
                default:
                    $deactivateMethod    = 'deactivateSubrulesOfCustomerForProduct';
                    break;
            }
            $ruleType = ucfirst($ruleType);
            global $wpdb;
            //insert Rule in db
            if ($wpdb->insert($this->subruleTable, array(
                'rule_id'                => $ruleId,
                'product_id'             => $productId,
                'flat_or_discount_price' => $flatOrDiscountPrice,
                'price'                  => $price,
                'active'                 => 1,
                'rule_type'              => $ruleType,
                'associated_entity'      => $associationEntity,
                'min_qty'                => $quantity,
            ), array(
                '%d',
                '%d',
                '%d',
                '%f',
                '%d',
                '%s',
                '%s',
                '%d',
            )) ) {
                $currentSubruleId = $wpdb->insert_id;
                //Deactivate Other Rules
                call_user_func(array( $this, $deactivateMethod ), $productId, $associationEntity, $quantity, $currentSubruleId);
                return true;
            }
            $this->addError(__('Could not add subrule in the database. Please check if correct data is added in the form.', CSP_TD));
            return false;
        }

        public function updateSubrule($subruleId, $dataTobeUpdated)
        {
            //$ruleId, $productId, $flatOrDiscountPrice, $price, $ruleType, $associationEntity

            global $wpdb;

            if (! isset($dataTobeUpdated[ 'active' ])) {
                $dataTobeUpdated[ 'active' ] = 1;
            }

            $sizeOfData          = count($dataTobeUpdated);
            $queryPlaceholders   = array_fill(0, $sizeOfData, '%d');
            $columnsTobeUpdated  = array_keys($dataTobeUpdated);

            //lets set placeholder for price key in query
            $positionOfPrice = array_search('price', $columnsTobeUpdated);
            if ($positionOfPrice !== false) {
                $queryPlaceholders[ $positionOfPrice ] = '%f';

                if ($dataTobeUpdated[ 'price' ] < 0) {
                    $this->addError(sprintf(__('Price is not valid for Product Id: %s', CSP_TD), $dataTobeUpdated[ 'product_id' ]));
                    return;
                }
            }

            //lets set placeholder for rule_type key in query
            $posRuleTypeData = array_search('rule_type', $columnsTobeUpdated);
            if ($posRuleTypeData !== false) {
                $queryPlaceholders[ $posRuleTypeData ]    = '%s';
                $dataTobeUpdated[ 'rule_type' ]                  = ucfirst(strtolower($dataTobeUpdated[ 'rule_type' ]));
            }

            //lets set placeholder for associated_entity key in query
            $positionOfEntityData = array_search('associated_entity', $columnsTobeUpdated);
            if ($positionOfEntityData !== false) {
                $queryPlaceholders[ $positionOfEntityData ] = '%s';
            }

            if ($noOfRowsUpdated = $wpdb->update($this->subruleTable, $dataTobeUpdated, array(
                'subrule_id' => $subruleId,
            ), $queryPlaceholders, array(
                '%d'
            )) || $noOfRowsUpdated == 0 ) {
                return true;
            }
            $this->addError(__('Could not update subrule in the database. Please check if correct data is added in the form.', CSP_TD));
            return false;
        }


        public function deactivateSubrulesForCustomersNotInArray($productId, $customerIds, $qty)
        {
            if (empty($customerIds)) {
                return;
            }
            global $wpdb;
            if (is_array($customerIds) && is_array($qty)) {
                foreach ($customerIds as $index => $singleUser) {
                    @$wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` = %d AND `min_qty` = %d AND `rule_type` = %s AND product_id=%d", $singleUser, $qty[$index], 'customer', $productId));
                }

                //     $qty = implode(', ', $qty);
                //     $user_in             = implode(', ', $customerIds);
                //     $sizeOfData          = count($customerIds);
                //     $queryPlaceholders   = array_fill(0, $sizeOfData, '%d');
                //     $queryPlaceholders   = implode(', ', $queryPlaceholders);
                //     $qtyQueryPlaceholders   = array_fill(0, $sizeOfData, '%d');
                //     $qtyQueryPlaceholders   = implode(', ', $qtyQueryPlaceholders);
                //     @$wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` NOT IN ($queryPlaceholders)) AND `min_qty` NOT IN ($qtyQueryPlaceholders) AND `rule_type` = %s AND product_id=%d", $user_in, $qty, 'customer', $productId));
                // } else {
                //     $user_in = $customerIds;
                //     @$wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` != `$user_in` AND  `rule_type` = %s AND product_id=%d AND min_qty != %d", 'customer', $productId, $qty));
            }
        }

        public function deactivateSubrulesForRolesNotInArray($productId, $roles, $qty)
        {
            if (empty($roles) || empty($qty)) {
                return;
            }
            global $wpdb;
            if (is_array($roles) && is_array($qty)) {
                foreach ($roles as $index => $singleRole) {
                    @$wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` = %s AND `min_qty` = %d AND `rule_type` = %s AND product_id=%d", $singleRole, $qty[$index], 'Role', $productId));
                }
                // $qty                    = implode(', ', $qty);
                // $roles_in               = implode(', ', $roles);
                // $sizeOfData             = count($roles);
                // $queryPlaceholders      = array_fill(0, $sizeOfData, '%s');
                // $queryPlaceholders      = implode(', ', $queryPlaceholders);
                // $qtyQueryPlaceholders   = array_fill(0, $sizeOfData, '%d');
                // $qtyQueryPlaceholders   = implode(', ', $qtyQueryPlaceholders);
                // @$wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` NOT IN ($queryPlaceholders) AND `min_qty` NOT IN ($qtyQueryPlaceholders) AND `rule_type` = %s AND product_id=%d", $roles_in, $qty, 'role', $productId));
            }
            //  else {
            //     $roles_in = $roles;
            //     @$wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` != `$roles_in` AND  `rule_type` = %s AND product_id=%d AND min_qty != %d", 'role', $productId, $qty));
            // }
        }

        public function deactivateSubrulesForGroupsNotInArray($productId, $groups, $qty)
        {
            if (empty($groups)) {
                return;
            }
            global $wpdb;
            if (is_array($groups) && is_array($qty)) {
                foreach ($groups as $index => $singleGroup) {
                    @$wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` = %d AND `min_qty` = %d AND `rule_type` = %s AND product_id=%d", $singleGroup, $qty[$index], 'group', $productId));
                }

            //     $groups_in           = implode(', ', $groups);
            //     $sizeOfData          = count($groups);
            //     $queryPlaceholders   = array_fill(0, $sizeOfData, '%d');
            //     $queryPlaceholders   = implode(', ', $queryPlaceholders);
            //     @ $wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` NOT IN ($queryPlaceholders) AND  `rule_type` = %s AND product_id=%d", $groups_in, 'group', $productId));
            // } else {
            //     $groups_in = $groups;
            //     @$wpdb->query($wpdb->prepare("UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` != `$groups_in` AND  `rule_type` = %s AND product_id=%d", 'group', $productId));
            }
        }

        public function deactivateSubrulesOfCustomerForProduct($productId, $customerId, $qty, $exceptionSubruleIds = null)
        {
            $this->deactivateSubrulesOfEntityForProduct($productId, 'customer', $customerId, $qty, $exceptionSubruleIds);
        }

        public function deactivateSubrulesOfRoleForProduct($productId, $roleName, $qty, $exceptionSubruleIds = null)
        {
            $this->deactivateSubrulesOfEntityForProduct($productId, 'role', $roleName, $qty, $exceptionSubruleIds);
        }

        public function deactivateSubrulesOfGroupForProduct($productId, $groupId, $qty, $exceptionSubruleIds = null)
        {
            $this->deactivateSubrulesOfEntityForProduct($productId, 'group', $groupId, $qty, $exceptionSubruleIds);
        }


        public function deactivateSubrulesOfAllRolesForProduct($productId)
        {
            global $wpdb;
            $wpdb->update($this->subruleTable, array(
                'active' => 0,
            ), array(
                'product_id' => $productId,
                'active'     => 1,
                'rule_type'  => 'Role',
            ), array(
                '%d'
            ), array(
                '%d',
                '%d',
                '%s'
            ));
        }

        public function deactivateSubrulesOfAllCustomerForProduct($productId)
        {
            global $wpdb;
            $wpdb->update($this->subruleTable, array(
                'active' => 0,
            ), array(
                'product_id' => $productId,
                'active'     => 1,
                'rule_type'  => 'customer',
            ), array(
                '%d'
            ), array(
                '%d',
                '%d',
                '%s'
            ));
        }

        public function deactivateSubrulesOfAllGroupsForProduct($productId)
        {
            global $wpdb;
            $wpdb->update($this->subruleTable, array(
                'active' => 0,
            ), array(
                'product_id' => $productId,
                'active'     => 1,
                'rule_type'  => 'Group',
            ), array(
                '%d'
            ), array(
                '%d',
                '%d',
                '%s'
            ));
        }

        public function deactivateSubrulesOfEntityForProduct($productId, $ruleType, $associatedEntity, $qty, $exceptionSubruleIds = null)
        {
            global $wpdb;
            $ruleType = ucfirst(strtolower($ruleType));
            //Deactivate all subrules
            if ($exceptionSubruleIds == null || empty($exceptionSubruleIds)) {
                $update_status = $wpdb->update($this->subruleTable, array(
                    'active' => 0,
                ), array(
                    'associated_entity'  => $associatedEntity,
                    'product_id'         => $productId,
                    'active'             => 1,
                    'rule_type'          => $ruleType,
                    'min_qty'            => $qty,
                ), array(
                    '%d'
                ), array(
                    '%s',
                    '%d',
                    '%d',
                    '%s',
                    '%d'
                ));
                if ($update_status === false) {
                    $this->addError(__('Could not deactivate pre-existing subrules', CSP_TD));
                }
            } else {
                //Deactivate only few subrules
                $deactivateQuery = "UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` = %s AND `min_qty` = %d AND `product_id` = %d AND `active` = %d AND `rule_type` = %s AND subrule_id != %d";

                if (is_array($exceptionSubruleIds)) {
                    $numberOfSubrules    = count($exceptionSubruleIds);
                    $queryPlaceholders   = array_fill(0, $numberOfSubrules, '%d');
                    $exceptionSubruleIds = implode(', ', $exceptionSubruleIds);
                    $deactivateQuery     = "UPDATE {$this->subruleTable} SET `active`= 0 WHERE `associated_entity` = %s AND `min_qty` = %d AND `product_id` = %d AND `active` = %d AND `rule_type` = %s AND subrule_id NOT IN ($queryPlaceholders)";
                }

                $queryStatus = $wpdb->query($wpdb->prepare($deactivateQuery, $associatedEntity, $qty, $productId, 1, $ruleType, $exceptionSubruleIds));

                if ($queryStatus === false) {
                    $this->addError(__('There was an error while deactivating subrules.', CSP_TD));
                }
            }
        }

        public function countSubrules($ruleId)
        {
            $subrules = $this->getSubruleIds($ruleId);
            if (empty($subrules)) {
                return 0;
            }
            return count($subrules);
        }

        public function getCountOfInactiveSubrulesForRules($ruleIds = array())
        {
            global $wpdb;
            $rules = array();
            if (empty($ruleIds)) {
                return false;
            }
            $ruleList            = implode(', ', $ruleIds);
            $inactiveCountArray  = $wpdb->get_results("SELECT rule_id, count(active) as total_inactive_rules FROM {$this->subruleTable} WHERE rule_id IN ($ruleList) AND active = 0 GROUP BY rule_id", ARRAY_A);
            if ($inactiveCountArray) {
                foreach ($inactiveCountArray as $singleResult) {
                    $rules[ $singleResult[ 'rule_id' ] ] = $singleResult[ 'total_inactive_rules' ];
                }
                return $rules;
            }
            return false;
        }

        public function getSubruleIds($ruleId)
        {
            global $wpdb;
            $subrules = $wpdb->get_col($wpdb->prepare("SELECT subrule_id FROM {$this->subruleTable} WHERE rule_id=%d", $ruleId));
            return $subrules;
        }

        //deletes subrules and all the (user, role and group) specific pricing maping for the given product id.
        public function deleteSubruleIdsForProduct($product_id)
        {
            global $wpdb, $ruleManager;
            $subrules = $wpdb->get_results($wpdb->prepare("SELECT subrule_id, rule_id FROM {$this->subruleTable} WHERE product_id = %d", $product_id), ARRAY_A);
            if (isset($subrules)) {
                foreach ($subrules as $subrule) {
                    $this->deleteSubrule($subrule['subrule_id']);
                    $ruleManager->updateTotalNumberOfSubrules($subrule['rule_id']);
                }
            }
        }

        public function deleteSubrule($subruleId)
        {
            global $wpdb;
            // $subruleTable = $wpdb->prefix . 'wusp_subrules';
            $wpdb->delete($this->subruleTable, array(
                'subrule_id' => $subruleId,
            ), array(
                '%d',
            ));
        }
        public function deleteSubrules($subruleIds = array())
        {

            global $wpdb;
            $wpdb->show_errors();
            if (empty($subruleIds)) {
                return;
            }
            if (is_array($subruleIds)) {
                $subrules = implode(',', $subruleIds);
                //Delete query was not wokring with placeholder. So executing query directly.
                $wpdb->query("DELETE FROM {$this->subruleTable} WHERE subrule_id IN ($subrules)");
            }
        }

        public function deleteSubrulesOfRule($ruleId, $ruleType = null)
        {
            global $wpdb;
            $productsEntities    = $this->getAssociatedEntitiesForActiveSubrulesOfRule($ruleId);
            $tableName           = '';
            $entityColumnName = '';
            if (! empty($productsEntities) && $ruleType != null) {
                //Delete values from corresponding tables.
                $ruleType = strtolower($ruleType);
                switch ($ruleType) {
                    case "role":
                        $tableName          = $wpdb->prefix . 'wusp_role_pricing_mapping';
                        $entityColumnName    = 'role';
                        break;
                    case 'customer':
                        $tableName          = $wpdb->prefix . 'wusp_user_pricing_mapping';
                        $entityColumnName    = 'user_id';
                        break;
                    case 'group':
                        $tableName             = $wpdb->prefix . 'wusp_group_product_price_mapping';
                        $entityColumnName    = 'group_id';
                        break;
                }
                foreach ($productsEntities as $productID => $entity) {
                    $wpdb->delete($tableName, array(
                        'product_id'         => $productID,
                        $entityColumnName    => $entity,
                    ));
                }
            }
            // echo $ruleId;
            $wpdb->delete($this->subruleTable, array(
                'rule_id' => $ruleId,
            ), array(
                '%d',
            ));
        }

        public function getAssociatedEntitiesForActiveSubrulesOfRule($ruleId)
        {
            global $wpdb;
            $productsEntities    = array();
            $products            = $wpdb->get_results($wpdb->prepare("SELECT product_id, associated_entity FROM {$this->subruleTable} WHERE rule_id = %d AND active = %d", $ruleId, 1));

            if ($products) {
                foreach ($products as $singleProduct) {
                    $productsEntities[ $singleProduct->product_id ] = $singleProduct->associated_entity;
                }
            }
            return $productsEntities;
        }

        public function getAllSubrulesInfoForRule($ruleId)
        {
            global $wpdb;
            $subrules = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->subruleTable} WHERE rule_id=%d", $ruleId), ARRAY_A);
            return $subrules;
        }

        public function getActiveSubrulesForProduct($productId, $ruleType)
        {
            if (empty($productId) || empty($ruleType)) {
                return;
            }
            global $wpdb;
            $ruleType = ucfirst(strtolower($ruleType));

            $subrules        = $wpdb->get_results($wpdb->prepare("SELECT subrule_id, associated_entity, price, min_qty, flat_or_discount_price FROM {$this->subruleTable} WHERE product_id = %d AND active = %d AND rule_type = %s", $productId, 1, $ruleType), ARRAY_A);
            
            $activeSubrules  = array();
            if ($subrules) {
                foreach ($subrules as $singleSubrule) {
                    $activeSubrules[ $singleSubrule[ 'associated_entity' ] ][ $singleSubrule[ 'min_qty' ] ] = array(
                        'price'     => $singleSubrule[ 'price' ],
                        // 'min_qty'   => $single_result->min_qty,
                        'price_type'=> $singleSubrule[ 'flat_or_discount_price' ]
                    );
                    // $activeSubrules[ $singleSubrule[ 'associated_entity' ] ]['price'] = $singleSubrule[ 'price' ];
                    // $activeSubrules[ $singleSubrule[ 'associated_entity' ] ]['price_type'] = $singleSubrule[ 'flat_or_discount_price' ];
                }
                if (! empty($activeSubrules)) {
                    return $activeSubrules;
                }
                return false;
            }
            return false;
        }


        public function getAllRuleInfoForAssociatedEntity($associatedEntity, $ruleType)
        {
            global $wpdb;
            $ruleType = ucfirst(strtolower($ruleType));
            $subrules = $wpdb->get_results($wpdb->prepare("SELECT rule_id FROM {$this->subruleTable} WHERE rule_type = '%s' AND associated_entity = '%s'", $ruleType, $associatedEntity), ARRAY_A);
            return $subrules;
        }

        public function getAllActiveSubrulesInfoForUserRules($userId, $prodIds = array())
        {
            global $wpdb;
            $whereCondition = "";
            if (! empty($prodIds)) {
                $whereCondition = "AND `product_id` NOT IN (" . implode(',', $prodIds) . ")";
            }
            $subrules = $wpdb->get_results("SELECT rule_id, product_id, price, min_qty, flat_or_discount_price as price_type FROM {$this->subruleTable} WHERE rule_type = 'customer' AND associated_entity = '{$userId}' AND active = 1 " . $whereCondition, ARRAY_A);
            return $subrules;
        }

        public function getAllActiveSubrulesInfoForRolesRule($roleList, $prodIds = array())
        {
            global $wpdb;
            $whereCondition = "";
            if (! empty($prodIds)) {
                $whereCondition = "AND `product_id` NOT IN (" . implode(',', $prodIds) . ")";
            }
            $subrules = $wpdb->get_results("SELECT rule_id, product_id, price, min_qty, flat_or_discount_price as price_type FROM {$this->subruleTable} WHERE rule_type = 'role' AND associated_entity IN ('" . implode("','", $roleList) . "') AND active = 1 " . $whereCondition, ARRAY_A);
            return $subrules;
        }

        public function getAllActiveSubrulesInfoForGroupsRule($groupIds, $prodIds = array())
        {
            global $wpdb;
            $whereCondition = "";
            if (! empty($prodIds)) {
                $whereCondition = "AND `product_id` NOT IN (" . implode(',', $prodIds) . ")";
            }
            $source = __('Direct', CSP_TD);
            $subrules = $wpdb->get_results("SELECT rule_id, product_id, price, min_qty, flat_or_discount_price as price_type, '$source' as 'source' FROM {$this->subruleTable} WHERE rule_type = 'group' AND associated_entity IN (" . implode(',', $groupIds) . ") AND active = 1 " . $whereCondition, ARRAY_A);
            return $subrules;
        }

//		public function shouldRuleBeDeactivated($ruleId){
//			global $wpdb;
//            $subrules = $wpdb->get_results($wpdb->prepare("SELECT subrule_id, active FROM {$this->subruleTable} WHERE rule_id=%d", $ruleId), ARRAY_A);
//            return $subrules;
//		}
    }

}
$GLOBALS['subruleManager'] = WdmSubruleManagement::getInstance();
