<?php

namespace WdmCSP;

if (! class_exists('WdmWuspAddDataInDB')) {

    class WdmWuspAddDataInDB
    {
        /**
         * inserts pricing and user-product mapping in database
         * 1-- Flat Price , 2 -- Percent discount
         *
         * @global object $wpdb Object responsible for executing db queries
         */
        public static function insertPricingInDb($user_id, $product_id, $price_type, $pricing, $qty)
        {
            global $wpdb;
            $wpusp_pricing_table = $wpdb->prefix . 'wusp_user_pricing_mapping';
            $pricing = wc_format_decimal($pricing);
            if (! empty($user_id) && ! empty($product_id) && ! empty($pricing) && !empty($price_type)) {
                $wpdb->insert($wpusp_pricing_table, array(
                    'price'                     => $pricing,
                    'product_id'                => $product_id,
                    'user_id'                   => $user_id,
                    'flat_or_discount_price'    => $price_type,
                    'min_qty'                   => $qty,
                ), array(
                    '%s',
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                ));
            }
        }

        /**
         * inserts group product price mapping in database
         *
         * @global object $wpdb Object responsible for executing db queries
         * @param type $group_id
         * @param type $product_id
         * @param type $price price to be set
         */
        public static function insertGroupProductPricingInDb(
            $group_id,
            $product_id,
            $price_type,
            $price,
            $qty
        ) {
            global $wpdb;
            $group_pricing_table = $wpdb->prefix . 'wusp_group_product_price_mapping';
            $price = wc_format_decimal($price);
            if (! empty($group_id) && ! empty($product_id) && ! empty($price) && !empty($price_type)) {
                $insert_status = $wpdb->insert($group_pricing_table, array(
                    'group_id'                  => $group_id,
                    'product_id'                => $product_id,
                    'price'                     => $price,
                    'flat_or_discount_price'    => $price_type,
                    'min_qty'                   => $qty
                ), array(
                    '%d',
                    '%d',
                    '%s',
                    '%d',
                    '%d',
                ));

                if ($insert_status) {
                    return $wpdb->insert_id;
                } else {
                    return false;
                }
            }
        }

        /**
         * Insert user product mapping in wp_wusp_role_pricing_mapping table
         *
         * @global object $wpdb
         * @param string $role
         * @param int $product_id
         * @return int returns the incremented id after insertion. If
         *              combination already exists in the database, then
         *              returns the id of that combination present in db.
         */
        public static function insertRoleProductMappingInDb(
            $role,
            $product_id,
            $price_type,
            $price,
            $qty
        ) {
            global $wpdb;
            
            $role_pricing_table = $wpdb->prefix . 'wusp_role_pricing_mapping';
            $price = wc_format_decimal($price);
            if (! empty($role) && ! empty($product_id) && ! empty($price)) {
                $insert_status = $wpdb->insert($role_pricing_table, array(
                    'product_id'                => $product_id,
                    'role'                      => $role,
                    'price'                     => $price,
                    'flat_or_discount_price'    => $price_type,
                    'min_qty'                   => $qty
                ), array(
                    '%d',
                    '%s',
                    '%s',
                    '%d',
                    '%d'
                ));
    
                if ($insert_status) {
                    return $wpdb->insert_id;
                } else {
                    return false;
                }
            }

        }

        public static function insertQueryLog(
            $selection_type,
            $selection_list,
            $product_list,
            $query_title,
            $selection_entry_ids,
            $price_list
        ) {
            global $wpdb;
            $wdm_query_log = $wpdb->prefix . 'wusp_query_log';

            $insert_status = $wpdb->insert($wdm_query_log, array(
                'query_time'     => date('Y-m-d h:i:s', current_time('timestamp')),
                'selection_type' => $selection_type,
                'selection_list' => $selection_list,
                'product_list'   => $product_list,
                'query_title'    => $query_title
            ));
            if ($insert_status) {
                $query_log_id = $wpdb->insert_id;
                self::addQuerySelections($query_log_id, $selection_entry_ids, $price_list);

                return $query_log_id;
            } else {
                return false;
            }
        }

        public static function addQuerySelections($query_log_id, $selection_entry_ids, $price_list)
        {
            global $wpdb;

            $selectionEntryTable = $wpdb->prefix . 'wusp_query_selection_entry';
            $counter             = 0;

            if (! empty($query_log_id) && ! empty($selection_entry_ids)) {
                foreach ($selection_entry_ids as $single_entry) {
                    $price = isset($price_list[ $counter ]) ? $price_list[ $counter ] : -1;

                    $wpdb->insert(
                        $selectionEntryTable,
                        array( 'query_id'      => $query_log_id,
                        'selection_id'   => $single_entry,
                        'price'          => $price )
                    );

                    $counter ++;
                    unset($price);
                }
            }
        }
    }

}
