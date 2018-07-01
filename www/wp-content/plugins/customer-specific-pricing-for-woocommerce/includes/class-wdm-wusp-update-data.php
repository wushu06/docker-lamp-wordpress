<?php

namespace WdmCSP;

if (! class_exists('WdmWuspUpdateDataInDB')) {
    class WdmWuspUpdateDataInDB
    {
        public static function updateUserPricingInDb($update_id, $price, $price_type, $quantity)
        {
            global $wpdb;
            $wpusp_product_table = $wpdb->prefix . 'wusp_user_pricing_mapping';
            $price = wc_format_decimal($price);
            if (! empty($update_id) && ! empty($price)) {
                $wpdb->update($wpusp_product_table, array(
                        'min_qty'                   => $quantity,
                        'price'                     => $price,
                        'flat_or_discount_price'    => $price_type,
                    ), array(
                        'id'        => $update_id,
                    ), array(
                    '%d',
                    '%s',
                    '%d',
                    ), array(
                    '%d'));
            }
        }

        public static function updateRolePricingInDb($update_id, $role, $product_id, $price, $price_type, $quantity)
        {
            global $wpdb;

            $role_product_table = $wpdb->prefix . 'wusp_role_pricing_mapping';
            $price = wc_format_decimal($price);
            if (! empty($role) && ! empty($price) && !empty($product_id)) {
                $wpdb->update($role_product_table, array(
                        'min_qty'                   => $quantity,
                        'price'                     => $price,
                        'flat_or_discount_price'    => $price_type,
                    ), array(
                        'id'    => $update_id
                    ), array(
                    '%d',
                    '%s',
                    '%d',
                    ), array(
                    '%d',
                    '%s',
                    '%d'));
            }
        }

        public static function updateGroupPricingInDb($update_id, $group_id, $product_id, $price, $price_type, $quantity)
        {
            global $wpdb;

            $group_product_table = $wpdb->prefix . 'wusp_group_product_price_mapping';
            $price = wc_format_decimal($price);
            if (! empty($group_id) && ! empty($price) && !empty($product_id)) {
                $wpdb->update($group_product_table, array(
                        'min_qty'                   => $quantity,
                        'price'                     => $price,
                        'flat_or_discount_price'    => $price_type,
                    ), array(
                        'id'    => $update_id
                    ), array(
                    '%d',
                    '%s',
                    '%d',
                    ), array(
                    '%d',
                    '%d',
                    '%d'));
            }
        }
    }
}
