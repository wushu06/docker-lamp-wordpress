<?php

namespace cspCategoryPricing\addData;

if (!class_exists('WdmWuspAddCategoryData')) {
    class WdmWuspAddCategoryData
    {
        private static $instance;
        public $errors;
        public $userPriceTable;
        public $rolePriceTable;
        public $groupPriceTable;

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

        public function __construct()
        {
            global $wpdb;
            $this->userPriceTable    = $wpdb->prefix . 'wcsp_user_category_pricing_mapping';
            $this->rolePriceTable    = $wpdb->prefix . 'wcsp_role_category_pricing_mapping';
            $this->groupPriceTable    = $wpdb->prefix . 'wcsp_group_category_pricing_mapping';
        }

    	public function addUserCategoryRecords($catArray, $userIdsArray, $priceArray, $minQtyArray, $discountTypeArray)
    	{
            global $deleteCatRecords;

            $UserCatQtyArray     = array();
            $user_names          = '';

            //delete records
            $deleteCatRecords->removeUserCatQtyList($catArray, $userIdsArray, $minQtyArray);

            //Insert and Update records
            if (! empty($userIdsArray) && ! empty($minQtyArray) && ! empty($catArray)) {
                foreach ($userIdsArray as $index => $wdmWooUserId) {
                	$UserCatQtyArray = $this->loopAddUserCatRecord($index, $wdmWooUserId, $catArray, $priceArray, $minQtyArray, $discountTypeArray, $UserCatQtyArray);
                }//foreach ends
            }
    	}

    	public function loopAddUserCatRecord($index, $wdmWooUserId, $catArray, $priceArray, $minQtyArray, $discountTypeArray, $UserCatQtyArray)
    	{
            global $wpdb, $post, $getCatRecords;

            if (isset($wdmWooUserId) && $wdmWooUserId != '-1') {
                $userCatQtyPair = $wdmWooUserId."-".$catArray[ $index ]."-".$minQtyArray[ $index ];
                if (! in_array($userCatQtyPair, $UserCatQtyArray)) {
                    array_push($UserCatQtyArray, $userCatQtyPair);
                    $userId = $wdmWooUserId != '-1' ? $wdmWooUserId : "";
                    $qty = $minQtyArray[ $index ] != '-1' ? $minQtyArray[ $index ] : "";
                    $categorySlug = $catArray[ $index ] != '-1' ? $catArray[ $index ] : "";
                    if (isset($priceArray[ $index ]) && isset($discountTypeArray[ $index ]) && $discountTypeArray[ $index ] != '-1' && isset($qty) && !($qty <= 0)) {
                        $pricing = wc_format_decimal($priceArray[ $index ]);
                        $priceType = $discountTypeArray[ $index ];
                        $this->addSingleUserRecord($userId, $pricing, $priceType, $wdmWooUserId, $qty, $categorySlug);
                    }
                    //If price is not set delete that record
                    if (empty($pricing)) {
                        $wpdb->delete(
                            $this->userPriceTable,
                            array(
                            'user_id'       => $userId,
                            'cat_slug' => $categorySlug,
                            'min_qty'    => $qty,
                            ),
                            array(
                            '%d',
                            '%s',
                            '%d',
                            )
                        );
                    }
                }
            }

            return $UserCatQtyArray;
    	}

    	public function addSingleUserRecord($userId, $pricing, $priceType, $wdmWooUserId, $qty, $categorySlug)
    	{
    		global $wpdb;
            if (! empty($userId) && ! empty($pricing) && ! empty($priceType)) {
                $result = $wpdb->get_results($wpdb->prepare("SELECT id FROM $this->userPriceTable WHERE user_id = '%d' and min_qty = '%d' and cat_slug=%s", $wdmWooUserId, $qty, $categorySlug));
                if (count($result) > 0) {
                    $update_status = $wpdb->update($this->userPriceTable, array(
                        'user_id'                   => $userId,
                        'price'                  => $pricing,
                        'flat_or_discount_price' => $priceType,
                        'cat_slug'             => $categorySlug,
                        'min_qty'                => $qty,
                    ), array( 'user_id' => $userId, 'cat_slug' => $categorySlug, 'min_qty' => $qty ));

                    if ($update_status) {
                        
                    }
                } else {
                    $wpdb->insert($this->userPriceTable, array(
                        'user_id'                => $userId,
                        'price'                  => $pricing,
                        'flat_or_discount_price' => $priceType,
                        'cat_slug'             => $categorySlug,
                        'min_qty'                => $qty,
                    ), array(
                        '%d',
                        '%s',
                        '%d',
                        '%s',
                        '%d',
                    ));
                }
            }
    	}

    	public function addRoleCategoryRecords($catArray, $rolesArray, $priceArray, $minQtyArray, $discountTypeArray)
    	{
    		global $deleteCatRecords;

            //delete records
            $deleteCatRecords->removeRoleCatQtyList($catArray, $rolesArray, $minQtyArray);

            $RoleCatQtyArray     = array();
            if (! empty($rolesArray) && ! empty($minQtyArray) && ! empty($catArray)) {
                foreach ($rolesArray as $index => $wdmRoleName) {
                	$RoleCatQtyArray = $this->loopAddRoleCatRecord($index, $wdmRoleName, $catArray, $priceArray, $minQtyArray, $discountTypeArray, $RoleCatQtyArray);
                }//foreach ends
            }
       	}

       	public function loopAddRoleCatRecord($index, $wdmRoleName, $catArray, $priceArray, $minQtyArray, $discountTypeArray, $RoleCatQtyArray)
       	{
            global $wpdb, $post, $getCatRecords, $deleteCatRecords;
            if (isset($wdmRoleName) && $wdmRoleName != '-1') {
                $roleCatQtyPair = $wdmRoleName."-".$catArray[ $index ] ."-".$minQtyArray[ $index ];
                if (! in_array($roleCatQtyPair, $RoleCatQtyArray)) {
                    array_push($RoleCatQtyArray, $roleCatQtyPair);
                    $roleId = $wdmRoleName;
                    $qty = $minQtyArray[ $index ] != '-1' ? $minQtyArray[ $index ] : "";
                    $categorySlug = $catArray[ $index ] != '-1' ? $catArray[ $index ] : "";
                    if (isset($priceArray[ $index ]) && isset($discountTypeArray[ $index ]) && $discountTypeArray[ $index ] != '-1' && isset($qty) && !($qty <= 0)) {
                        $pricing = wc_format_decimal($priceArray[ $index ]);
                        $priceType = $discountTypeArray[ $index ];
                        $this->addSingleRoleRecord($roleId, $pricing, $priceType, $wdmRoleName, $qty, $categorySlug);
                    }
                    if (empty($pricing)) {
                        $wpdb->delete(
                            $this->rolePriceTable,
                            array(
                            'role'       => $roleId,
                            'cat_slug' => $categorySlug,
                            'min_qty'    => $qty,
                            ),
                            array(
                            '%s',
                            '%d',
                            '%d',
                            )
                        );
                    }
                }
                // $counter ++;
            }       		

            return $RoleCatQtyArray;
       	}

    	public function addSingleRoleRecord($roleId, $pricing, $priceType, $wdmRoleName, $qty, $categorySlug)
    	{
    		global $wpdb;
            if (! empty($roleId) && ! empty($pricing) && ! empty($priceType)) {
                $result = $wpdb->get_results($wpdb->prepare("SELECT id FROM $this->rolePriceTable WHERE role = '%s' and min_qty = '%d' and cat_slug=%s", $wdmRoleName, $qty, $categorySlug));
                if (count($result) > 0) {
                    $wpdb->update($this->rolePriceTable, array(
                        'role'                   => $roleId,
                        'price'                  => $pricing,
                        'flat_or_discount_price' => $priceType,
                        'cat_slug'             => $categorySlug,
                        'min_qty'                => $qty,
                    ), array( 'role' => $roleId, 'cat_slug' => $categorySlug, 'min_qty' => $qty ));
                } else {
                    $wpdb->insert($this->rolePriceTable, array(
                        'role'                   => $roleId,
                        'price'                  => $pricing,
                        'flat_or_discount_price' => $priceType,
                        'cat_slug'             => $categorySlug,
                        'min_qty'                => $qty,
                    ), array(
                        '%s',
                        '%s',
                        '%d',
                        '%s',
                        '%d',
                    ));
                }
            }
    	}

    	public function addGroupCategoryRecords($catArray, $groupIdsArray, $priceArray, $minQtyArray, $discountTypeArray)
    	{
    		global $deleteCatRecords;

            //delete records
            $deleteCatRecords->removeGroupCatQtyList($catArray, $groupIdsArray, $minQtyArray);

    		$GroupCatQtyArray     = array();
            if (isset($groupIdsArray) && ! empty($minQtyArray) && ! empty($catArray)) {
                foreach ($groupIdsArray as $index => $wdmGroupId) {
                    $GroupCatQtyArray = $this->loopAddGroupCatRecord($index, $wdmGroupId, $catArray, $priceArray, $minQtyArray, $discountTypeArray, $GroupCatQtyArray);
                }//foreach ends
            }
    	}

       	public function loopAddGroupCatRecord($index, $wdmGroupId, $catArray, $priceArray, $minQtyArray, $discountTypeArray, $GroupCatQtyArray)
       	{
            global $wpdb, $post, $getCatRecords, $deleteCatRecords;
            if (isset($wdmGroupId) && $wdmGroupId != '-1') {
                $groupCatQtyPair = $wdmGroupId."-".$catArray[ $index ]."-".$minQtyArray[ $index ];
                if (! in_array($groupCatQtyPair, $GroupCatQtyArray)) {
                    array_push($GroupCatQtyArray, $groupCatQtyPair);
                    $groupId = $wdmGroupId;
                    $qty = $minQtyArray[ $index ] != '-1' ? $minQtyArray[ $index ] : "";
                    $categorySlug = $catArray[ $index ] != '-1' ? $catArray[ $index ] : "";
		            if (isset($priceArray[ $index ]) && isset($discountTypeArray[ $index ]) && isset($qty) && !($qty <= 0)) {
		                $pricing = wc_format_decimal($priceArray[ $index ]);
       		            $priceType = $discountTypeArray[ $index ];
		                $this->addSingleGroupRecord($groupId, $pricing, $priceType, $wdmGroupId, $qty, $categorySlug);
		            }

		            if (empty($pricing)) {
		                $wpdb->delete(
		                    $this->groupPriceTable,
		                    array(
		                    'group_id'      => $groupId,
		                    'cat_slug'    => $categorySlug,
		                    'min_qty'       => $qty,
		                    ),
		                    array(
		                    '%d',
		                    '%d',
		                    '%d',
		                    )
		                );
		            }
                }
            }

            return $GroupCatQtyArray;
       	}

    	public function addSingleGroupRecord($groupId, $pricing, $priceType, $wdmGroupId, $qty, $categorySlug)
    	{
            global $wpdb;
            
            if (! empty($groupId) && ! empty($pricing) && ! empty($priceType)) {
                $result = $wpdb->get_results($wpdb->prepare("SELECT id FROM $this->groupPriceTable WHERE group_id = '%d' and min_qty = '%d' and cat_slug=%s", $groupId, $qty, $categorySlug));
                if (count($result) > 0) {
                    $update_status = $wpdb->update($this->groupPriceTable, array(
                        'group_id'                   => $groupId,
                        'price'                  => $pricing,
                        'flat_or_discount_price' => $priceType,
                        'cat_slug'             => $categorySlug,
                        'min_qty'                => $qty,
                    ), array( 'group_id' => $groupId, 'cat_slug' => $categorySlug, 'min_qty' => $qty ));
                } else {
                    $wpdb->insert($this->groupPriceTable, array(
                        'group_id'                => $groupId,
                        'price'                  => $pricing,
                        'flat_or_discount_price' => $priceType,
                        'cat_slug'             => $categorySlug,
                        'min_qty'                => $qty,
                    ), array(
                        '%d',
                        '%s',
                        '%d',
                        '%s',
                        '%d',
                    ));
                }
            }
    	}

    }
}
$GLOBALS['addCatRecords'] = WdmWuspAddCategoryData::getInstance();
