<?php

namespace cspImportExport\cspExport;

/**
 * fetch and return the group specific pricing data for exporting in csv file
 * @author WisdmLabs
 */
if (! class_exists('WdmWuspGroupSpecificPricingExport')) {
    class WdmWuspGroupSpecificPricingExport extends \cspImportExport\cspExport\WdmWuspExport
    {
        /**
         * fetch the data form database
         * @global type $wpdb
         * @return array content for creating csv
         */
        public function wdmFetchData()
        {
                     /**
         * Check if Groups is active
         */
            $activated_plugins = apply_filters('active_plugins', get_option('active_plugins'));
            $group_headings  = array( 'Product id', 'Group Name', 'Flat', '%' );
            $group_product_result=array();
            if (in_array('groups/groups.php', $activated_plugins)) {
                global $wpdb;
                $wpusp_group_table     = $wpdb->prefix . 'wusp_group_product_price_mapping';
                $wdm_groups_group            = $wpdb->prefix . 'groups_group';
                $wdm_post                 = $wpdb->prefix . 'posts';
                $group_headings  = array( 'Product id', 'Group Name', 'Min Qty', 'Flat', '%' );
                $group_product_result           = $wpdb->get_results("SELECT product_id, name, min_qty, price, flat_or_discount_price as discount_price FROM {$wpusp_group_table}, {$wdm_groups_group}, {$wdm_post} where {$wpusp_group_table}.group_id={$wdm_groups_group}.group_id and {$wpusp_group_table}.product_id = {$wdm_post}.id");
                if ($group_product_result) {
                    $group_product_result = $this->processResult($group_product_result);
                    return array( $group_headings, $group_product_result );
                }
            }
        }

        /**
         * [processResult process the data to be exported]
         * @param  [array] $group_product_result [Fetched result from database]
         * @return [array]                       [Processed result]
         */
        public function processResult($group_product_result)
        {
            foreach ($group_product_result as $key => $result) {
                if ($result->discount_price == 2) {
                    $group_product_result[$key]->discount_price = $result->price;
                    $group_product_result[$key]->price = '';
                } else {
                    $group_product_result[$key]->discount_price = '' ;
                }
            }
            return $group_product_result;
        }

        /**
         * returns name of file for export
         * @return string filename
         */
        public function wdmFileName()
        {
            return '/group_specific_pricing.csv';
        }
    }

}
