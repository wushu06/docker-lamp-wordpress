<?php

namespace cspNewOrder;

/**
 * called when new order is created from backend
 */
if (!class_exists('WdmCustomerSpecificPricingNewOrder')) {
    class WdmCustomerSpecificPricingNewOrder
    {

        /**
         * call class functions on actions and filetrs
         */
        public function __construct()
        {
            add_action('admin_enqueue_scripts', array($this, 'wdmEnqueueScripts'), 10);
            
            if (version_compare(WC_VERSION, '2.7', '<')) {
                add_filter('woocommerce_ajax_order_item', array($this, 'wdmWoocommerceLegacyAjaxOrderItem'), 10, 1);
            } else {
                add_filter('woocommerce_ajax_order_item', array($this, 'wdmWoocommerceAjaxOrderItem'), 10, 1);
            }
            
            add_action('wp_ajax_get_customer_id', array($this, 'wdmSetCustomerId'));
            add_action('wp_ajax_get_quantity_price_pairs', array($this, 'getOrderItemsQuantityPricePair'));
            add_action('woocommerce_before_order_itemmeta', array($this, 'addProductInOrderRow'), 10, 3);
        }

        /**
         * enqueue the js file
         */
        public function wdmEnqueueScripts()
        {
            global $post;
            if (isset($post) && $post->post_type == 'shop_order') {
                wp_enqueue_script('wdm_csp_functions', plugins_url('/js/wdm-csp-function.js', dirname(dirname(__FILE__))), array('jquery'), CSP_VERSION);

                wp_enqueue_script('wdm_new_order_js', plugins_url('/js/new_order/new_order.js', dirname(dirname(__FILE__))), array('jquery'), CSP_VERSION, true);
                wp_localize_script('wdm_new_order_js', 'wdm_new_order_ajax', array('ajaxurl' => admin_url('admin-ajax.php'), 'order_id' => $post->ID));
            }
        }

        /**
         * change the price of items
         * @param type $item array item data
         * @param type $item_id string
         * @return array item
         */
        public function wdmWoocommerceLegacyAjaxOrderItem($item)
        {
            $order_id = $_REQUEST['order_id'];

            $product_id = $item['product_id'];

            if (isset($item['item_meta']['_variation_id'][0]) && !empty($item['item_meta']['_variation_id'][0])) {
                $product_id = $item['item_meta']['_variation_id'][0];
            }

            $sale_price = get_post_meta($product_id, '_sale_price', true);
            if (isset($sale_price) && !empty($sale_price)) {
                $price = $sale_price;
            } else {
                $price = get_post_meta($product_id, '_regular_price', true);
            }
            $user_id = get_post_meta($_POST['order_id'], 'csp_customer_id', true);

            if (empty($user_id)) {
                $item['line_total'] = $price;
                $item['line_subtotal'] = $price;
                return $item;
            }
            $quantity = $item['item_meta']['_qty'][0];
            $csp_prices = \WuspSimpleProduct\WuspCSPProductPrice::getQuantityBasedPricing($product_id, $user_id);
            $qtyList    = array_keys($csp_prices);
            if (!empty($qty) && $qty == 1 && !in_array(1, $qtyList)) {
                $db_price   = \WuspSimpleProduct\WuspCSPProductPrice::cspGetRegularPrice($product_id);
            }
            $db_price   = \WuspSimpleProduct\WuspCSPProductPrice::getApplicablePriceForQty($qtyList, $csp_prices, $quantity);

            if (isset($db_price) && $db_price) {
                $item['line_total'] = $db_price;
                $item['line_subtotal'] = $db_price;
                $item['item_meta']['_line_subtotal'][0] = $db_price;
                $item['item_meta']['_line_total'][0] = $db_price;
            } else {
                $item['line_total'] = $price;
                $item['line_subtotal'] = $price;
                $item['item_meta']['_line_subtotal'][0] = $price;
                $item['item_meta']['_line_total'][0] = $price;
            }

            return $item;
        }

        public function wdmWoocommerceAjaxOrderItem($item)
        {
            global $cspFunctions;
            $order_id = $_REQUEST['order_id'];

            $user_id = get_post_meta($order_id, 'csp_customer_id', true);

            //If there is no specific user associated with the order, return item as is.
            if (empty($order_id) || empty($user_id)) {
                return $item;
            }

            $product_id = $item->get_product_id();

            if ($item->get_variation_id() != 0) {
                $product_id = $item->get_variation_id();
            }

            $quantity = $item->get_quantity();

            $csp_prices = \WuspSimpleProduct\WuspCSPProductPrice::getQuantityBasedPricing($product_id, $user_id);

            $qtyList    = array_keys($csp_prices);

            $original_product_price = \WuspSimpleProduct\WuspCSPProductPrice::getProductPrice(wc_get_product($product_id));

            $db_price   = \WuspSimpleProduct\WuspCSPProductPrice::getPriceForQuantity($quantity, $product_id, $qtyList, $csp_prices, $original_product_price);

            if (isset($db_price) && $db_price) {
                $item->set_total($db_price);
                $item->set_subtotal($db_price);
            }

            return $item;
        }

        public function getOrderItemsQuantityPricePair()
        {
            $response = array();
            $order_id = $_REQUEST['order_id'];
            $product_ids = $_POST['product_id'];
            
            if (!is_array($product_ids)) {
                $product_ids = array_map('trim', explode(',', $product_ids));
            }

            $user_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;

            if ($user_id !== 0) {
                if (!empty($product_ids)) {
                    if (is_array($product_ids)) {
                        foreach ($product_ids as $product_id) {
                            $response[$product_id] = $this->productQuantityPricePair($product_id, $user_id);
                        }
                    } else {
                        $response[$product_id] = $this->productQuantityPricePair($product_id, $user_id);
                    }
                }
                echo json_encode($response);
            } else {
                echo json_encode(array('error' => 'NO_DATA_FOUND'));
            }
            die();
        }

        public function productQuantityPricePair($productId, $userId)
        {
            $productObject = wc_get_product($productId);
            $response = array();
            $csp_prices = \WuspSimpleProduct\WuspCSPProductPrice::getQuantityBasedPricing($productId, $userId);
            $response['csp_prices'] = self::excludeTaxFromPrices($productObject, $csp_prices);
            $response['qtyList'] = array_keys($csp_prices);
            $response['regular_price'] = self::excludeTaxFromPrices($productObject, \WuspSimpleProduct\WuspCSPProductPrice::cspGetRegularPrice($productId));
            return $response;
        }

        public static function getApplicablePriceForQty($qtyList, $csp_prices, $qty)
        {
            if (in_array($qty, $qtyList)) {
                return $csp_prices[$qty];
            } else {
                return self::getPriceInQtyRange($qtyList, $csp_prices, $qty);
            }
        }

        public static function excludeTaxFromPrices($product, $prices)
        {
            if (is_array($prices)) {
                $new_prices = array();
                foreach ($prices as $key => $price) {
                    if (is_numeric($price)) {
                        if (version_compare(WC_VERSION, '2.7', '<')) {
                            $new_prices[$key] = $product->get_price_excluding_tax(1, $price);
                        } else {
                            $new_prices[$key] = wc_get_price_excluding_tax($product, array('price' => $price));
                        }
                    } else {
                        $new_prices[$key] = 0;
                    }
                }
                return $new_prices;
            }

            if (is_numeric($prices)) {
                if (version_compare(WC_VERSION, '2.7', '<')) {
                    return $product->get_price_excluding_tax(1, $prices);
                } else {
                    return wc_get_price_excluding_tax($product, array('price' => $prices));
                }
            }
        }

        public function wdmSetCustomerId()
        {
            //Allow only admin to get selection
            $capability_required = apply_filters('csp_get_customers_id_capability', 'manage_options');
            $can_user_get_id = apply_filters('csp_can_user_get_customers_id', current_user_can($capability_required));
            if (!$can_user_get_id && is_numeric(intval($_POST['order_id']))) {
                echo "Security Check";
                exit;
            }
            echo 'INSIDE THIS';
            //check if Order post meta exists for the user or not.
            update_post_meta($_POST['order_id'], 'csp_customer_id', $_POST['customer_id']);
            die();
        }


        public function addProductInOrderRow($item_id, $item, $_product)
        {
            $product_id = $item['product_id'];

            if (! empty($item['variation_id'])) {
                $product_id = $item['variation_id'];
            }

            echo "<input type='hidden' class='csp_order_item_product_id' value='{$product_id}'>";
        }
    }

}
