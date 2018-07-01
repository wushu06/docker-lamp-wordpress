<?php

namespace cspImportExport\cspExport;

/**
 * fetch and return the customer specific pricing data for exporting in csv file
 * @author WisdmLabs
 */
if (! class_exists('WdmWuspUserSpecificPricingExport')) {
    class WdmWuspUserSpecificPricingExport extends \cspImportExport\cspExport\WdmWuspExport
    {
        /**
         * fetch the data form database
         * @global type $wpdb
         * @return array content for creating csv
         */
        public function wdmFetchData()
        {
            global $wpdb;

            $wpusp_pricing_table      = $wpdb->prefix . 'wusp_user_pricing_mapping';
            $wdm_users                           = $wpdb->prefix . 'users';
            $wdm_post                 = $wpdb->prefix . 'posts';
            $user_headings   = array( 'Product id', 'User', 'Min Qty', 'Flat', '%' );
            $user_product_result            = $wpdb->get_results("SELECT product_id, user_login, min_qty, price, flat_or_discount_price as discount_price FROM $wpusp_pricing_table,$wdm_users,$wdm_post where $wpusp_pricing_table.user_id=$wdm_users.id and $wpusp_pricing_table.product_id = $wdm_post.id");
            if ($user_product_result) {
                $user_product_result = $this->processResult($user_product_result);
                return array( $user_headings, $user_product_result );
            }
        }

        /**
         * [processResult process the data to be exported]
         * @param  [array] $user_product_result [Fetched result from database]
         * @return [array]                       [Processed result]
         */
        public function processResult($user_product_result)
        {
            foreach ($user_product_result as $key => $result) {
                if ($result->discount_price == 2) {
                    $user_product_result[$key]->discount_price = $result->price;
                    $user_product_result[$key]->price = '';
                } else {
                    $user_product_result[$key]->discount_price = '' ;
                }
            }
            return $user_product_result;
        }

        public function wdmFileName()
        {
            return '/user_specific_pricing.csv';
        }
    }

}
