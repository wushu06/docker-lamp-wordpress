<?php

namespace WuspSimpleProduct;

//use WuspGetData as cspGetData;

//check whether a class with the same name exists
if (! class_exists('WuspCSPProductPrice')) {
    /**
     * Class to Display & Process data of Simple Products for User Specific Pricing
     */
    //class declartion
    class WuspCSPProductPrice
    {
        public $appliedPriceInCart = false;

        public function __construct()
        {
            include_once('category-pricing/class-wdm-wusp-get-category-data.php');
            include_once('category-pricing/class-wdm-wusp-add-category-data.php');
            include_once('category-pricing/class-wdm-wusp-delete-category-data.php');
            add_action('woocommerce_before_calculate_totals', array($this, 'applyQuantityPriceInCart'), 1);
            add_action('wp_enqueue_scripts', array($this, 'cspFrontEndScript'));

            if (defined('WC_VERSION')) {
                $this->hookWcGetPriceFilter();
            } else {
                add_action('woocommerce_loaded', array($this, 'hookWcGetPriceFilter'));
            }
            

            add_filter('woocommerce_get_price_html', array($this, 'showQuantityBasedPricing', ), 1, 2);
            add_action('woocommerce_single_product_summary', array($this,'cspQuantityBasedProductTotal',), 10);
            add_filter('woocommerce_variation_prices', array( $this, 'applyCSPVariationPrice' ), 10, 3);
        }

        public function hookWcGetPriceFilter()
        {
              
            if (version_compare(WC_VERSION, '3.0', '<')) {
                add_filter('woocommerce_get_price', array($this, 'applyCustomPrice', ), 1, 2);
            } else {
                 add_filter('woocommerce_product_get_price', array($this, 'applyCustomPrice', ), 1, 2);
            }
        }

        public function showCartItemPrice($price, $cart_item, $cart_item_key)
        {
            $product_id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : $cart_item['product_id'];
            $db_price = $this->getDBPrice($product_id, $price, $cart_item['quantity']);

            return $db_price;
        }

        public function applyQuantityPriceInCart($cart_object)
        {
            global $woocommerce;

            foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
                $cart_product_id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : $cart_item['product_id'];
                $price = $this->getDBPrice($cart_product_id, self::getProductPrice($cart_item['data']), $cart_item['quantity']);
                    
                if (version_compare(WC_VERSION, '3.0', '<')) {
                    $woocommerce->cart->cart_contents[$cart_item_key]['data']->price = $price;
                } else {
                    $woocommerce->cart->cart_contents[$cart_item_key]['data']->set_price($price);
                }
            }

            $this->appliedPriceInCart = true;
        }


        public function cspQuantityBasedProductTotal()
        {
            global $woocommerce, $product;
            $product_id = self::getProductId($product);
            

            if ($product->is_type('variable')) {
                $db_price = $product->get_variation_regular_price();
                $db_price = $this->getUnitPrice($product_id, $db_price);
                echo sprintf('<div class="csp-hide-product-total" id="product_total_price">%s %s</div>', __('Product Total:', 'woocommerce'), '<span class="price">'. wc_price(self::getDisplayPrices($product, $db_price)) . '</span><span class="price-suffix">' . $product->get_price_suffix() . '</span>');
            } else {
                $db_price = self::cspGetRegularPrice($product_id);
                $db_price = $this->getUnitPrice($product_id, $db_price);
               // echo sprintf('<div id="product_total_price">%s %s</div>', __('Product Total:', 'woocommerce'), '<span class="price">'. wc_price(self::getDisplayPrices($product, $db_price)) . '</span><span
	            // class="price-suffix">' . $product->get_price_suffix() . '</span>');
            }
        }

        public function getUnitPrice($product_id, $price = 0)
        {
            $price = self::cspGetRegularPrice($product_id);

            $csp_prices = self::getQuantityBasedPricing($product_id);
            $min = $this->getMinQty($csp_prices);
            $db_price = $price;
            if ($min == 1) {
                $qtyList    = array_keys($csp_prices);
                $db_price   = self::getApplicablePriceForQty($qtyList, $csp_prices, $min);
            }

            return $db_price;
        }

        public function cspFrontEndScript()
        {
            // exit;
            global $woocommerce, $post;

            if (is_user_logged_in() && is_product()) {
                $array_passed_to_js = array();
                $product = wc_get_product($post->ID);

                $product_id = self::getProductId($product);

                wp_enqueue_script('jquery');
                wp_enqueue_style('wdm_csp_product_frontend_css', plugins_url('/css/wdm-csp-product-frontend.css', dirname(__FILE__)), array(), CSP_VERSION);
                wp_enqueue_script('wdm_csp_functions', plugins_url('/js/wdm-csp-function.js', dirname(__FILE__)), array('jquery'), CSP_VERSION);
                wp_localize_script(
                    'wdm_csp_functions',
                    'wdm_csp_function_object',
                    array(
                    'decimal_separator' => wc_get_price_decimal_separator(),
                    'thousand_separator' => wc_get_price_thousand_separator(),
                    'decimals' => wc_get_price_decimals(),
                    'price_format' => get_woocommerce_price_format(),
                    'currency_symbol' => get_woocommerce_currency_symbol(),
                    )
                );

                if (is_callable(array($product, 'get_type'))) {
                    $product_type = $product->get_type();
                } else {
                    $product_type = $product->product_type;
                }

                if ('simple' == $product_type) {
                    $array_passed_to_js = $this->getSimpleProductsArrayTobePassed($product);
                    $array_passed_to_js['quantity_discount_text'] = __('Quantity Discount', CSP_TD);
                    wp_enqueue_script('wdm_csp_qty_price', plugins_url('/js/simple-products/customer-quantity-based-price/wdm-csp-frontend-qty-price.js', dirname(__FILE__)), array('jquery'), CSP_VERSION);
                    wp_localize_script('wdm_csp_qty_price', 'wdm_csp_qty_price_object', $array_passed_to_js);
                } elseif ('variable' == $product_type) {
                    $array_passed_to_js = $this->getVariableProductsArrayTobePassed($product);
                    $array_passed_to_js['quantity_discount_text'] = __('Quantity Discount', CSP_TD);
                    wp_enqueue_script('wdm_csp_qty_price', plugins_url('/js/variable-products/customer-quantity-based-price/wdm-csp-frontend-qty-price.js', dirname(__FILE__)), array('jquery'), CSP_VERSION);
                    wp_localize_script('wdm_csp_qty_price', 'wdm_csp_qty_price_object', $array_passed_to_js);
                }
            }
        }

        public function getSimpleProductsArrayTobePassed($product)
        {
            global $woocommerce;
            $product_id = self::getProductId($product);
            $csp_prices = self::getDisplayPrices($product, self::getQuantityBasedPricing($product_id));
            $qtyList = array_keys($csp_prices);

            $regular_price = self::getDisplayPrices($product, self::cspGetRegularPrice($product_id));

            return array(
                'qtyList'               => json_encode($qtyList),
                'csp_prices'            => json_encode($csp_prices),
                'regular_price'         => $regular_price,
                'cart_contents_total'   => $woocommerce->cart->cart_contents_total,
                'currency_symbol'       => get_woocommerce_currency_symbol(),
            );
        }

        public function getVariableProductsArrayTobePassed($product)
        {
            global $woocommerce;
            $csp_prices         = array();
            $regular_prices     = array();
            $qtyList            = array();
            $min                = array();
            $variation_ids      = $product->get_children();
            foreach ($variation_ids as $variation_id) {
                $csp_prices[$variation_id] = self::getDisplayPrices($product, self::getQuantityBasedPricing($variation_id));
                $regular_price = self::getDisplayPrices($product, self::cspGetRegularPrice($variation_id));
                if (!empty($csp_prices[$variation_id])) {
                    $qtyList[$variation_id] = array_keys($csp_prices[$variation_id]);
                    $min[$variation_id] = $this->getMinQty($csp_prices[$variation_id]);
                }


                if (!empty($regular_price)) {
                    $regular_prices[$variation_id] = $regular_price;
                }
            }

            return array(
                'price_suffix'          => $product->get_price_suffix(),
                'minimum'               => json_encode($min),
                'qtyList'               => json_encode($qtyList),
                'csp_prices'            => json_encode($csp_prices),
                'regular_price'         => $regular_prices,
                'cart_contents_total'   => $woocommerce->cart->cart_contents_total,
                'currency_symbol'       => get_woocommerce_currency_symbol(),
                'unavailable_text'      => __('Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce'),
                'more_text'             => __(' and more :', CSP_TD),
            );
        }


    /**
     * Apply Custom Price when user adds the variable product in the cart.
     */

        public function applyCSPVariationPrice($prices_array, $product, $display)
        {
            if (! is_user_logged_in()) {
                return $prices_array;
            }

            $variation_ids = $product->get_children();
            $variation_prices = array_keys($prices_array['price']);
            $remainingIds = array_diff($variation_ids, $variation_prices);

            if (!is_admin()) {
                $new_prices = array();
                $current_user_id = get_current_user_id();

                foreach ($prices_array['price'] as $variation_id => $price) {
                        $new_prices[$variation_id] = self::getDisplayPrices($product, $this->getUnitPrice($variation_id, $price));
                }

                foreach ($remainingIds as $variation_id) {
                    $price = self::getDisplayPrices($product, $this->getUnitPrice($variation_id));
                    if (!empty($price)) {
                        $new_prices[$variation_id] = $price;
                    }
                }

                asort($new_prices);
                $prices_array['price'] = $new_prices;

                return $prices_array;
            }

            return $prices_array;
        }

    /**
     * Apply Custom Price when user adds the simple product in the cart.
     */
        public function applyCustomPrice($price, $product)
        {
            if ($this->appliedPriceInCart == true) {
                return $price;
            }


            global $woocommerce;
            //If user is not logged in, Original Price should be returned
            if (! is_user_logged_in()) {
                return $price;
            }

            if (!is_admin()) {
                $product_id  = self::getProductId($product);

                $db_price = $this->getDBPrice($product_id, $price);

                if (isset($db_price) && $db_price) {
                    return $db_price;
                }
            }

            return $price;
        }

        public function getDBPrice($product_id, $price, $qty = 1)
        {

            global $woocommerce;
            $product = wc_get_product($product_id);
            $csp_prices = self::getQuantityBasedPricing($product_id);
            $original_product_price = self::getProductPrice($product);
            $qtyList = array_keys($csp_prices);
            return self::getPriceForQuantity($qty, $product_id, $qtyList, $csp_prices, $original_product_price);
        }

        public static function getPriceForQuantity($qty, $productId, $qtyList, $csp_prices, $original_product_price)
        {
            if (!empty($qty) && $qty == 1 && !in_array(1, $qtyList)) {
                return $original_product_price;
            }

            if (empty($qtyList) && empty($csp_prices)) {
                return $original_product_price;
            }

            if ($qty >= 1) {
                return self::getApplicablePriceForQty($qtyList, $csp_prices, $qty);
            }
        }

        public static function cspGetRegularPrice($product_id)
        {
            $product = wc_get_product($product_id);

            return self::getProductPrice($product);
        }

        /**
         * Gets the Category specific pricing for the user and product
         *
         * @global object $getCatRecords Object of the class to get category specific data
         * @param object $product
         * @param mixed $userId default null
         * @param mixed $direct default false If accesed directly and not from the getQuantityBasedPricing function
         * @return if category based price is found, price is returned. Otherwise it returns regular price
         */
        public static function getCategoryBasedPricing($product, $userId = null, $direct = false)
        {
            global $getCatRecords, $cspFunctions;
            $userId    = ($userId === null) ? get_current_user_id() : $userId;
            $productCats = $cspFunctions->getProductCategories($product);
            $regularPrice = floatval(get_post_meta($product->get_id(), '_regular_price', true));
            $catSpecificPrices = array();
            $qtyList = array();
            
            //The product does not belong to any category.
            if (!count($productCats)) {
                return array();
            }

            $catArray = $cspFunctions->getArrayColumn($productCats, 'slug');

            $catSlugs = "('" . implode("', '", $catArray) . "')";

            $userCatPrices = $getCatRecords->getUsersCategoryPricingPairs($userId, $catSlugs, $product->get_id());
            $roleCatPrices = $getCatRecords->getRolesCategoryPricingPairs($userId, $catSlugs, $product->get_id());

            $groupCatPrices = false;
            
            /**
             * Check if Groups is active
             */
            $active_plugins  = apply_filters('active_plugins', get_option('active_plugins'));
            if (in_array('groups/groups.php', $active_plugins)) {
                $groupCatPrices = $getCatRecords->getGroupsCategoryPricingPairs($userId, $catSlugs, $product->get_id());
            }

            if ((isset($userCatPrices) && $userCatPrices) || (isset($roleCatPrices) && $roleCatPrices) || (isset($groupCatPrices) && $groupCatPrices)) {
                $qtyList = self::getQtyList($userCatPrices, $groupCatPrices, $roleCatPrices);
            }

            if (!isset($qtyList) || count($qtyList) <= 0) {
                return $qtyList;
            }

            $catSpecificPrices = self::getQtyPriceArray($product, $qtyList, $userCatPrices, $roleCatPrices, $groupCatPrices);
            
            if ($direct) {
                $catSpecificPrices = array_map('wc_format_decimal', $catSpecificPrices);
            }

            return $catSpecificPrices;
        }

        public static function mergeProductCatPrices($priceArray1, $priceArray2)
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

        public static function hasQtyInPriceArray($qtysArray, $qty)
        {
            if (count($qtysArray) > 0 && in_array($qty, $qtysArray)) {
                return true;
            }
            return false;
        }

        public static function getQuantityBasedPricing($product_id, $user_id = null)
        {
            if (is_user_logged_in()) {
                $product = wc_get_product($product_id);
                $regular_price = floatval(get_post_meta($product_id, '_regular_price', true));
                $user_id    = ($user_id === null) ? get_current_user_id() : $user_id;
                $qtyList    = array();
                $csp_price  = \WdmCSP\WdmWuspGetData::getPriceOfProductForUser($user_id, $product_id);
                $rsp_price  = WrspSimpleProduct\WdmWuspSimpleProductsRsp::getQtyPricePairsOfProductForRole($user_id, $product_id);


                $gsp_price = false;
                /**
                 * Check if Groups is active
                 */
                $active_plugins  = apply_filters('active_plugins', get_option('active_plugins'));
                if (in_array('groups/groups.php', $active_plugins)) {
                    $gsp_price = \WdmCSP\WdmWuspGetData::getQtyPricePairsOfProductForGroup($user_id, $product_id);
                }

                if ((isset($csp_price) && $csp_price) || (isset($rsp_price) && $rsp_price) || (isset($gsp_price) && $gsp_price)) {
                    $qtyList = self::getQtyList($csp_price, $gsp_price, $rsp_price);
                }

                if (!isset($qtyList) || count($qtyList) <= 0) {
                    $catPrices = self::getCategoryBasedPricing($product, $user_id);
                    if (!empty($catPrices) && $product->get_type() != 'variable') {
                        if (!isset($catPrices) || count($catPrices) == 0 || !isset($catPrices[1])) {
                            $catPrices[1] = self::getProductPrice($product);
                        }
                        ksort($catPrices);
                        $catPrices = array_map('wc_format_decimal', $catPrices);
                        return $catPrices;
                    } else {
                        return $qtyList;
                    }
                }


                $cspPrices = self::getQtyPriceArray($product, $qtyList, $csp_price, $rsp_price, $gsp_price);

                $catPrices = self::getCategoryBasedPricing($product, $user_id);

                $mergedPrices = self::mergeProductCatPrices($cspPrices, $catPrices);

                if (empty($mergedPrices)) {
                    $mergedPrices = $cspPrices;
                }

                // Setting Price for Quantity 1
                if (!isset($mergedPrices) || count($mergedPrices) == 0 || !isset($mergedPrices[1])) {
                    $mergedPrices[1] = self::getProductPrice($product);
                }

                if (!empty($mergedPrices)) {
                    ksort($mergedPrices);
                }

                $mergedPrices = array_map('wc_format_decimal', $mergedPrices);

                return $mergedPrices;
            }
        }

        public static function getQtyPriceArray($product, $qtyList, $priceArray1, $priceArray2, $priceArray3, $direct = false)
        {
            global $cspFunctions;
            $cspPrices = array();
            $regularPrice = floatval(get_post_meta($product->get_id(), '_regular_price', true));
            foreach ($qtyList as $qty) {
                if ($cspFunctions->hasQty($priceArray1, $qty)) {
                    $cspPrices[$qty] = $cspFunctions->priceForQuantity($qty, $priceArray1, $regularPrice);
                } elseif ($cspFunctions->hasQty($priceArray2, $qty)) {
                    $cspPrices[$qty] = $cspFunctions->priceForQuantity($qty, $priceArray2, $regularPrice);
                } elseif ($cspFunctions->hasQty($priceArray3, $qty)) {
                    $cspPrices[$qty] = $cspFunctions->priceForQuantity($qty, $priceArray3, $regularPrice);
                }
            }

            // Setting Price for Quantity 1
            if ($direct && (!isset($cspPrices) || count($cspPrices) == 0 || !isset($cspPrices[1]))) {
                $cspPrices[1] = self::getProductPrice($product);
            }

            if (!empty($cspPrices)) {
                ksort($cspPrices);
            }

            return $cspPrices;
        }

        public function showQuantityBasedPricing($price, $product)
        {
            global $wp_query;

            $product_id  = self::getProductId($product);
            if ($wp_query->queried_object_id != $product_id) {
                return $price;
            }

            if (! is_user_logged_in() || !is_product()) {
                return $price;
            }

            $user_id    = get_current_user_id();
                
            $csp_prices = self::getQuantityBasedPricing($product_id);
            if (isset($csp_prices) && $csp_prices) {
                if (count($csp_prices) === 1) {
                    $keys = array_keys($csp_prices);
                    if ($keys[0] == 1) {
                        return wc_price(self::getDisplayPrice($product, $csp_prices[$keys[0]])) . $product->get_price_suffix();
                    }
                }
                $table = '<div class = "qty-fieldset"><h1 class="qty-legend"><span>' . __('Quantity Discount', CSP_TD) . '</span></h1><table class = "qty_table">';
                $min = $this->getMinQty($csp_prices);

                if ($min && $min != 1) {
                    $price = self::cspGetRegularPrice($product_id);
                    $table .= "<tr>";
                    $table .= "<td class = 'qty-num'>1  ". __(' and more :', CSP_TD) . "</td><td class = 'qty-price'>". wc_price(self::getDisplayPrice($product, $price)) . $product->get_price_suffix() . "</td>";
                    $table .= "</tr>";
                }

                foreach ($csp_prices as $qty => $price) {
                    $table .= "<tr>";
                    $table .= "<td class = 'qty-num'>".$qty. __(' and more :', CSP_TD) . "</td><td class = 'qty-price'>". wc_price(self::getDisplayPrice($product, $price)) . $product->get_price_suffix() . "</td>";
                    $table .= "</tr>";
                }
                $table .= "</table></div>";

                return $table;
            }
            return $price;
        }

        public static function getQtyList($csp_price = array(), $gsp_price = array(), $rsp_price = array())
        {
            $qtyList = array();

            if (is_array($csp_price) && count($csp_price) > 0) {
                foreach ($csp_price as $csp) {
                    if (!in_array($csp->min_qty, $qtyList)) {
                        array_push($qtyList, $csp->min_qty);
                    }
                }
            }

            if (is_array($rsp_price) && count($rsp_price) > 0) {
                foreach ($rsp_price as $rsp) {
                    if (!in_array($rsp->min_qty, $qtyList)) {
                        array_push($qtyList, $rsp->min_qty);
                    }
                }
            }

            if (is_array($gsp_price) && count($gsp_price) > 0) {
                foreach ($gsp_price as $gsp) {
                    if (!in_array($gsp->min_qty, $qtyList)) {
                        array_push($qtyList, $gsp->min_qty);
                    }
                }
            }

            return $qtyList;
        }

        public function getMinQty($priceArray)
        {
            if (count($priceArray) == 0) {
                return false;
            }
            $keys = array_keys($priceArray);
            $min = $keys[0];

            foreach ($priceArray as $qty => $price) {
                if ($qty < $min) {
                    $min = $qty;
                }
            }

            return $min;
        }

        // public static function priceForQuantity($quantity, $priceArray, $regular_price)
        // {
        //     if (count($priceArray) == 0) {
        //         return false;
        //     }

        //     foreach ($priceArray as $a) {
        //         if ($a->min_qty == $quantity) {
        //             if ($a->price_type == 2) {
        //                 return ($regular_price) - (round(($a->price * $regular_price), wc_get_price_decimals()) / 100);
        //             }
        //             return $a->price;
        //         }
        //     }
        // }

        public static function getPriceInQtyRange($qtyList, $csp_prices, $qty)
        {
            $qtyListSize = count($qtyList);
            for ($i = 0; $i < $qtyListSize; $i++) {
                $next = $i + 1;
                if ($qty > $qtyList[$i]) {
                    if ($next != $qtyListSize && $qty < $qtyList[$next]) {
                        return $csp_prices[$qtyList[$i]];
                    }

                    if ($next == $qtyListSize) {
                        return $csp_prices[$qtyList[$i]];
                    }
                }
            }
        }

        public static function getApplicablePriceForQty($qtyList, $csp_prices, $qty)
        {
            if (in_array($qty, $qtyList)) {
                return $csp_prices[$qty];
            } else {
                return self::getPriceInQtyRange($qtyList, $csp_prices, $qty);
            }
        }

        public static function applyTaxOnPrices($product, $prices)
        {
            if (is_array($prices)) {
                $new_prices = array();
                foreach ($prices as $key => $price) {
                    if (is_numeric($price)) {
                        if (version_compare(WC_VERSION, '3.0', '<')) {
                            $new_prices[$key] = $product->get_price_including_tax(1, $price);
                        } else {
                            $new_prices[$key] = wc_get_price_including_tax($product, array('price' => $price));
                        }
                    } else {
                        $new_prices[$key] = 0;
                    }
                }
                return $new_prices;
            }

            if (is_numeric($prices)) {
                echo "HEllo"; exit;
                if (version_compare(WC_VERSION, '3.0', '<')) {
                    return $product->get_price_including_tax(1, $prices);
                } else {
                    return wc_get_price_including_tax($product, array('price' => $prices));
                }
            }
        }

        public static function getDisplayPrices($product, $prices){
            if (is_array($prices)) {
                $new_prices = array();
                foreach ($prices as $key => $price) {
                    if (is_numeric($price)) {
                        $new_prices[$key] = self::getDisplayPrice($product, $price);
                    }
                }
                return $new_prices;
            }
            if (is_numeric($prices)) {
                return self::getDisplayPrice($product, $prices);
            }
        }

        public static function getDisplayPrice($product, $price, $qty = 1 ){
            $price = round($price, wc_get_price_decimals());

            if (version_compare(WC_VERSION, '3.0', '<')) {
                return $product->get_display_price($price, $qty);
            } else {
                return wc_get_price_to_display($product, array(
                    'price' => $price,
                    'qty'   => $qty,
                    ));
            }

        }

        public static function getProductId($productObject, $context = 'view')
        {

            if (is_callable(array($productObject, 'get_id'))) {
                return $productObject->get_id($context);
            }
            return isset($product->variation_id)? $product->variation_id : $product->id ;
        }

        public static function getProductPrice($productObject)
        {
            if (version_compare(WC_VERSION, '3.0', '<')) {
                return $productObject->price ;
            }
            //With WC 2.7 when we pass context parameter as edit, we get unfiltered value
            return $productObject->get_price('edit');
        }
    }
}
