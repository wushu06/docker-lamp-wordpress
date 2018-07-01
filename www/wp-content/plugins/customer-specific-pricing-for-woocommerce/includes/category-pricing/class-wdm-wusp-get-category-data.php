<?php

namespace cspCategoryPricing\getData;

if (!class_exists('WdmWuspGetCategoryData')) {
    class WdmWuspGetCategoryData
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

        public function isUserCatPresent($catSlug)
        {
            global $wpdb;
            $query = "SELECT * FROM $this->userPriceTable WHERE cat_slug = '$catSlug'";
            $results = $wpdb->get_results($query);

            if (count($results) > 0) {
                return true;
            }

            return false;
        }

        public function isRoleCatPresent($catSlug)
        {
            global $wpdb;
            $query = "SELECT * FROM $this->rolePriceTable WHERE cat_slug = '$catSlug'";
            $results = $wpdb->get_results($query);

            if (count($results) > 0) {
                return true;
            }

            return false;
        }

 
        public function isGroupCatPresent($catSlug)
        {
            global $wpdb;
            $query = "SELECT * FROM $this->groupPriceTable WHERE cat_slug = '$catSlug'";
            $results = $wpdb->get_results($query);

            if (count($results) > 0) {
                return true;
            }

            return false;
        }

        public function getUsersCategoryPricingPairs($currentUserId, $catSlug, $productId, $extra = array())
        {
        	global $wpdb;
            static $catUserPrices = array();
            $regularPrice = floatval(get_post_meta($productId, '_regular_price', true));

            if (isset($catUserPrices[$currentUserId][$productId])) {
                return $catUserPrices[$currentUserId][$productId];
            }

            $userPrice         = array();

            // $user_info           = get_userdata($currentUserId);
            // $user_role           = "(" . implode(",", $user_info->roles) . ")";
            $price               = $wpdb->get_results("SELECT price, min_qty, cat_slug, flat_or_discount_price as price_type, 'set' as price_set FROM {$this->userPriceTable} WHERE user_id = $currentUserId AND cat_slug IN $catSlug ORDER BY min_qty");

            if ($price == null) {
                return;
            }

            for ($i = 0; $i < count($price); $i++) {
                $currentMinQty = $price[$i]->min_qty;
                
                if (isset($userPrice[$currentMinQty])) {
                    $newPrice = $price[$i]->price;
                    
                    if ($price[$i]->price_type == 2) {
                        $newPrice = ($regularPrice) - (($newPrice * $regularPrice) / 100);
                    }

                    $oldPrice = $userPrice[$currentMinQty]->price;
                    
                    if ($userPrice[$currentMinQty]->price_type == 2) {
                        $oldPrice = ($regularPrice) - (($oldPrice * $regularPrice) / 100);
                    }

                    if ($newPrice < $oldPrice) {
                        $userPrice[$currentMinQty] = $price[$i];
                    }
                } else {
                    $userPrice[$currentMinQty] = $price[$i];
                }
            }
            ksort($userPrice);

            $catUserPrices[$currentUserId][$productId] = $userPrice;
            return $catUserPrices[$currentUserId][$productId];
        }

        public function getRolesCategoryPricingPairs($currentUserId, $catSlug, $productId, $roleList = array())
        {
            global $wpdb;
            static $catRolePrices = array();
            $regularPrice = floatval(get_post_meta($productId, '_regular_price', true));

            if (isset($catRolePrices[$currentUserId][$productId])) {
                return $catRolePrices[$currentUserId][$productId];
            }

            $rolePrice         = array();

            if (empty($roleList)) {
                $userInfo           = get_userdata($currentUserId);
                $userRole           = "('" . implode("','", $userInfo->roles) . "')";
            } else {
                $userRole           = "('" . implode("','", $roleList) . "')";
            }

            $price               = $wpdb->get_results("SELECT price, min_qty, cat_slug, flat_or_discount_price as price_type, 'set' as price_set FROM {$this->rolePriceTable} WHERE role IN $userRole AND cat_slug IN $catSlug ORDER BY min_qty");

            if ($price == null) {
                return;
            }

            for ($i = 0; $i < count($price); $i++) {
                $currentMinQty = $price[$i]->min_qty;
                
                if (isset($rolePrice[$currentMinQty])) {
                    $newPrice = $price[$i]->price;
                    
                    if ($price[$i]->price_type == 2) {
                        $newPrice = ($regularPrice) - (($newPrice * $regularPrice) / 100);
                    }

                    $oldPrice = $rolePrice[$currentMinQty]->price;
                    
                    if ($rolePrice[$currentMinQty]->price_type == 2) {
                        $oldPrice = ($regularPrice) - (($oldPrice * $regularPrice) / 100);
                    }

                    if ($newPrice < $oldPrice) {
                        $rolePrice[$currentMinQty] = $price[$i];
                    }
                } else {
                    $rolePrice[$currentMinQty] = $price[$i];
                }
            }
            ksort($rolePrice);
            
            $catRolePrices[$currentUserId][$productId] = $rolePrice;
            return $catRolePrices[$currentUserId][$productId];
        }

        public function getGroupsCategoryPricingPairs($currentUserId, $catSlug, $productId, $groupIds = array())
        {
            global $wpdb;
            static $catGroupPrices = array();
            $wdmGroupsTable    = $wpdb->prefix . 'groups_user_group';

            if (isset($catGroupPrices[$currentUserId][$productId])) {
                return $catGroupPrices[$currentUserId][$productId];
            }

            $userGroupId        = $wpdb->get_results($wpdb->prepare("SELECT group_id FROM {$wdmGroupsTable} WHERE user_id=%d", $currentUserId));

            if (!empty($groupIds)) {
                $groupList = "(" . implode(", ", $groupIds) . ")";
                $userGroupId        = $wpdb->get_results("SELECT group_id FROM {$wdmGroupsTable} WHERE group_id IN $groupList");
            }

            // self::$userGroup[$user_id] = $user_groupid;

            if ($userGroupId) {
                $regularPrice = floatval(get_post_meta($productId, '_regular_price', true));
                // $wpusp_group_table   = $wpdb->prefix . 'wusp_group_product_price_mapping';
                $groupPrice         = array();

                foreach ($userGroupId as $singleUserGroupid) {
                    $price = $wpdb->get_results($wpdb->prepare("SELECT price, min_qty, cat_slug, flat_or_discount_price as price_type, 'set' as price_set FROM {$this->groupPriceTable} WHERE group_id=%d AND cat_slug IN $catSlug ORDER BY min_qty", $singleUserGroupid->group_id));
                    
                    if ($price == null) {
                        continue;
                    }

                    for ($i = 0; $i < count($price); $i++) {
                        $currentMinQty = $price[$i]->min_qty;
                        
                        if (isset($groupPrice[$currentMinQty])) {
                            $newPrice = $price[$i]->price;
                            
                            if ($price[$i]->price_type == 2) {
                                $newPrice = ($regularPrice) - (($newPrice * $regularPrice) / 100);
                            }

                            $oldPrice = $groupPrice[$currentMinQty]->price;
                            
                            if ($groupPrice[$currentMinQty]->price_type == 2) {
                                $oldPrice = ($regularPrice) - (($oldPrice * $regularPrice) / 100);
                            }

                            if ($newPrice < $oldPrice) {
                                $groupPrice[$currentMinQty] = $price[$i];
                            }
                        } else {
                            $groupPrice[$currentMinQty] = $price[$i];
                        }
                    }
                }

                ksort($groupPrice);
                $catGroupPrices[$currentUserId][$productId] = $groupPrice;
                return $catGroupPrices[$currentUserId][$productId];
            }
            
            $catGroupPrices[$currentUserId][$productId] = false;
            return $catGroupPrices[$currentUserId][$productId];
        }

        public function getAllUserCategoryPricingPairs()
        {
        	global $wpdb;
        	static $catUserPrices = array();

            $catUserPrices               = $wpdb->get_results("SELECT cat_slug, user_id, price, min_qty, flat_or_discount_price as price_type FROM {$this->userPriceTable}");

           	return $catUserPrices;
        }

        public function getCatUserQtyRecords($catArray, $userIdsArray, $minQtyArray)
        {
            global $wpdb;
            // $result = array_intersect($array1, $array2);
            // if ()

            // $userIds = "(".implode(', ', $userIdsArray).")";;
            // $categories = "('".implode("', '", $catArray)."')";
            // $minQtys = "(".implode(', ', $minQtyArray).")";

            $query = "SELECT user_id, cat_slug, min_qty FROM {$this->userPriceTable}";
            return $wpdb->get_results($query, ARRAY_A);
        }

        public function getCatRoleQtyRecords($catArray, $rolesArray, $minQtyArray)
        {
            global $wpdb;
            // $result = array_intersect($array1, $array2);
            // if ()

            // $roles = "('".implode("', '", $rolesArray)."')";;
            // $categories = "('".implode("', '", $catArray)."')";
            // $minQtys = "(".implode(', ', $minQtyArray).")";

            $query = "SELECT role, cat_slug, min_qty FROM {$this->rolePriceTable}";
            return $wpdb->get_results($query, ARRAY_A);
        }

        public function getCatGroupQtyRecords($catArray, $groupIdsArray, $minQtyArray)
        {
            global $wpdb;
            // $result = array_intersect($array1, $array2);
            // if ()

            // $groupIds = "(".implode(', ', $groupIdsArray).")";;
            // $categories = "('".implode("', '", $catArray)."')";
            // $minQtys = "(".implode(', ', $minQtyArray).")";

            $query = "SELECT group_id, cat_slug, min_qty FROM {$this->groupPriceTable}";
            return $wpdb->get_results($query, ARRAY_A);
        }

        public function getAllRolesCategoryPricingPairs()
        {
        	global $wpdb;
        	static $catRolePrices = array();

            $catRolePrices               = $wpdb->get_results("SELECT cat_slug, role, price, min_qty, flat_or_discount_price as price_type FROM {$this->rolePriceTable}");

           	return $catRolePrices;
        }

        public function getAllGroupCategoryPricingPairs()
        {
        	global $wpdb;
        	static $catGroupPrices = array();

            $catGroupPrices               = $wpdb->get_results("SELECT cat_slug, group_id, price, min_qty, flat_or_discount_price as price_type FROM {$this->groupPriceTable}");

           	return $catGroupPrices;
        }
        
        public function getProductsOfCat($catSlug)
        {
            global $wpdb;
            $catObj = get_term_by('slug', trim($catSlug), 'product_cat');

            $catId = $catObj->term_id;
            
            $products = $wpdb->get_col("SELECT object_id as product_id FROM `wp_term_relationships` WHERE term_taxonomy_id = $catId");
            $values = array_values($products);

            if (is_array($values) && !empty($values)) {
                return $values;
            }
            return array();
        }

        public function getAllProductPricesByUser($userId)
        {
            global $cspFunctions;
            $categories = $this->getCategoriesForUser($userId);
            $cspPrices = array();
            $catPrices = array();
            $mergedPrices = array();
            // $categories = array_unique($cspFunctions->getArrayColumn($prices, 'cat_slug'));

            $products = array();

            foreach ($categories as $value) {
                $products = array_unique(array_merge($products, $this->getProductsOfCat($value)));
            }

            $products = $this->getAllProducts($products);

            foreach ($products as $productId) {
                $cspPrices[$productId] = $this->getCSPPriceForProduct($userId, wc_get_product($productId), "\WdmCSP\WdmWuspGetData::getPriceOfProductForUser");
                
                $catPrices[$productId] = $this->getCatPricesForProduct($userId, wc_get_product($productId), "getUsersCategoryPricingPairs");
                $mergedPrice = $this->mergeCatPrices($cspPrices[$productId], $catPrices[$productId]);

                // Setting Price for Quantity 1
                // if (!isset($mergedPrice) || count($mergedPrice) == 0 || !isset($mergedPrice[1])) {
                //     $mergedPrice[1] = \WuspSimpleProduct\WuspCSPProductPrice::getProductPrice(wc_get_product($productId));
                // }

                $mergedPrices[$productId] = $mergedPrice;

            }

            return $mergedPrices;
        }

        public function mergeCatPrices($priceArray1, $priceArray2 = array())
        {
            global $cspFunctions;
            $cspPrices = array();
            if (empty($priceArray1) && empty($priceArray2)) {
                return array();
            }

            $qtyArray1 = array_keys($priceArray1);
            $qtyArray2 = array_keys($priceArray2);

            $qtysArray = array_unique(array_merge($qtyArray1, $qtyArray2));

            foreach ($qtysArray as $qty) {
                if ($cspFunctions->hasQtyInPriceArray($qtyArray1, $qty)) {
                    $cspPrices[$qty] = $priceArray1[$qty];
                } elseif ($cspFunctions->hasQtyInPriceArray($qtyArray2, $qty)) {
                    $cspPrices[$qty] = $priceArray2[$qty];
                }
            }

            ksort($cspPrices);

            return $cspPrices;
        }

        public function getAllProducts($products)
        {
            global $cspFunctions;
            $allProducts = array();
            foreach ($products as $key => $value) {
                $product = wc_get_product($value);
                if ($product->get_type() == 'variable') {
                    $allProducts = array_unique(array_merge($allProducts, $cspFunctions->getVariationId($product)));
                } else {
                    array_push($allProducts, $value);
                }
            }
            return $allProducts;
        }

        public function getCSPPriceForProduct($userId, $product, $function, $extra = array())
        {
            global $cspFunctions;
            $qtyList = array();
            $cspPrices = $function($userId, $product->get_id(), $extra);
            
            if ((isset($cspPrices) && $cspPrices)) {
                $qtyList = $cspFunctions->getArrayColumn($cspPrices, 'min_qty');

            }

            if (!isset($qtyList) || count($qtyList) <= 0) {
                return $qtyList;
            }

            return $this->getQuantityPriceArray($product, $qtyList, $cspPrices);
        }

        public function getCatPricesForProduct($userId, $product, $function, $extra = array())
        {
            global $getCatRecords, $cspFunctions;
            $userId    = ($userId === null) ? get_current_user_id() : $userId;
            $catSpecificPrices = array();

            if ($product->get_type() == 'simple' || $product->get_type() == 'variation') {

                $productCats = $cspFunctions->getProductCategories($product);
                $regularPrice = floatval(get_post_meta($product->get_id(), '_regular_price', true));
                $qtyList = array();
                
                //The product does not belong to any category.
                if (!count($productCats)) {
                    return false;
                }

                $catArray = $cspFunctions->getArrayColumn($productCats, 'slug');

                $catSlugs = "('" . implode("', '", $catArray) . "')";

                $CatPrices = $this->$function($userId, $catSlugs, $product->get_id(), $extra);

                // $roleCatPrices = $getCatRecords->getRolesCategoryPricingPairs($userId, $catSlugs, $product->get_id());
                // $groupCatPrices = false;
                
                // /**
                //  * Check if Groups is active
                //  */
                // $active_plugins  = apply_filters('active_plugins', get_option('active_plugins'));
                // if (in_array('groups/groups.php', $active_plugins)) {
                //     $groupCatPrices = $getCatRecords->getGroupsCategoryPricingPairs($userId, $catSlugs, $product->get_id());
                // }

                if ((isset($CatPrices) && $CatPrices)) {
                    $qtyList = $cspFunctions->getArrayColumn($CatPrices, 'min_qty');

                }

                if (!isset($qtyList) || count($qtyList) <= 0) {
                    return $qtyList;
                }

                $catSpecificPrices = $this->getQuantityPriceArray($product, $qtyList, $CatPrices);
            }
            return $catSpecificPrices;
        }

        public function getQuantityPriceArray($product, $qtyList, $priceArray1, $priceArray2 = array(), $direct = false)
        {
            global $cspFunctions;
            $cspPrices = array();
            
            foreach ($qtyList as $qty) {
                if ($cspFunctions->hasQty($priceArray1, $qty)) {
                    $cspPrices[$qty] = $cspFunctions->priceForSearchQuantity($qty, $priceArray1, $product);
                } elseif ($cspFunctions->hasQty($priceArray2, $qty)) {
                    $cspPrices[$qty] = $cspFunctions->priceForSearchQuantity($qty, $priceArray2, $product);
                }
            }


            // Setting Price for Quantity 1
            if ($direct && (!isset($cspPrices) || count($cspPrices) == 0 || !isset($cspPrices[1]))) {
                $cspPrices[1] = self::getProductPrice($product);
            }

            ksort($cspPrices);

            return $cspPrices;
        }

        public function getAllProductPricesByRoles($role_list)
        {
            global $cspFunctions;
            $categories = $this->getCategoriesForRole($role_list);
            $cspPrices = array();
            $catPrices = array();
            $mergedPrices = array();
            // $categories = array_unique($cspFunctions->getArrayColumn($prices, 'cat_slug'));

            $products = array();

            foreach ($categories as $value) {
                $products = array_unique(array_merge($products, $this->getProductsOfCat($value)));
            }

            $products = $this->getAllProducts($products);

            foreach ($products as $productId) {
                $cspPrices[$productId] = $this->getCSPPriceForProduct(0, wc_get_product($productId), "\WuspSimpleProduct\WrspSimpleProduct\WdmWuspSimpleProductsRsp::getQtyPricePairsOfProductForRole", $role_list);
                
                $catPrices[$productId] = $this->getCatPricesForProduct(0, wc_get_product($productId), "getRolesCategoryPricingPairs", $role_list);
                $mergedPrice = $this->mergeCatPrices($cspPrices[$productId], $catPrices[$productId]);

                // Setting Price for Quantity 1
                // if (!isset($mergedPrice) || count($mergedPrice) == 0 || !isset($mergedPrice[1])) {
                //     $mergedPrice[1] = \WuspSimpleProduct\WuspCSPProductPrice::getProductPrice(wc_get_product($productId));
                // }

                $mergedPrices[$productId] = $mergedPrice;

            }
            return $mergedPrices;
        }

        public function getAllProductPricesByGroups($groupIds)
        {
            global $cspFunctions;
            
            /**
             * Check if Groups is active
             */
            $active_plugins  = apply_filters('active_plugins', get_option('active_plugins'));
            if (!in_array('groups/groups.php', $active_plugins)) {
                return array();
            }
            
            $categories = $this->getCategoriesForGroup($groupIds);
            $cspPrices = array();
            $catPrices = array();
            $mergedPrices = array();
            // $categories = array_unique($cspFunctions->getArrayColumn($prices, 'cat_slug'));

            $products = array();

            foreach ($categories as $value) {
                $products = array_unique(array_merge($products, $this->getProductsOfCat($value)));
            }

            $products = $this->getAllProducts($products);

            foreach ($products as $productId) {
                $cspPrices[$productId] = $this->getCSPPriceForProduct(0, wc_get_product($productId), "\WdmCSP\WdmWuspGetData::getQtyPricePairsOfProductForGroup", $groupIds);
                
                $catPrices[$productId] = $this->getCatPricesForProduct(0, wc_get_product($productId), "getGroupsCategoryPricingPairs", $groupIds);
                $mergedPrice = $this->mergeCatPrices($cspPrices[$productId], $catPrices[$productId]);

                // Setting Price for Quantity 1
                // if (!isset($mergedPrice) || count($mergedPrice) == 0 || !isset($mergedPrice[1])) {
                //     $mergedPrice[1] = \WuspSimpleProduct\WuspCSPProductPrice::getProductPrice(wc_get_product($productId));
                // }

                $mergedPrices[$productId] = $mergedPrice;
            }
            return $mergedPrices;
        }

        public function getUserIdForCatQty($catSlug, $minQty)
        {

        }

        public function getQtyForCatUser($userId, $catSlug)
        {

        }

        public function getQtysForUser($userId)
        {

        }

        public function getCategoriesForUser($userId)
        {
            global $wpdb;
            $categories = $wpdb->get_col("SELECT cat_slug FROM {$this->userPriceTable} WHERE user_id = $userId");
            return $categories;
        }

        public function getCategoryForUserQty($userId, $minQty)
        {

        }

        public function getGroupIdForCatQty($catSlug, $minQty)
        {

        }

        public function getQtyForCatGroup($groupId, $catSlug)
        {

        }

        public function getQtysForGroup($groupId)
        {

        }

        public function getCategoriesForGroup($groupIds)
        {
            global $wpdb;
            $groupList = "(" . implode(", ", $groupIds) . ")";
            $categories = $wpdb->get_col("SELECT cat_slug FROM {$this->groupPriceTable} WHERE group_id IN $groupList");
            return $categories;
        }

        public function getCategoryForGroupQty($groupId, $minQty)
        {

        }

        public function getRoleIdForCatQty($catSlug, $minQty)
        {

        }

        public function getQtyForCatRole($userId, $catSlug)
        {

        }

        public function getQtysForRole($userId)
        {

        }

        public function getCategoriesForRole($roles)
        {
            global $wpdb;
            $roleList = "('" . implode("', '", $roles) . "')";
            $categories = $wpdb->get_col("SELECT cat_slug FROM {$this->rolePriceTable} WHERE role IN $roleList");
            return $categories;
        }

        public function getCategoryForRoleQty($userId, $minQty)
        {

        }
    }
}
$GLOBALS['getCatRecords'] = WdmWuspGetCategoryData::getInstance();
