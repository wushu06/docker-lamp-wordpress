<?php

namespace cspImportExport\cspExport;

/**
 * fetch and return the role specific pricing data for exporting in csv file
 * @author WisdmLabs
 */
if (! class_exists('WdmWuspRoleSpecificPricingExport')) {
    class WdmWuspRoleSpecificPricingExport extends \cspImportExport\cspExport\WdmWuspExport
    {
        /**
         * fetch the data form database
         * @global type $wpdb
         * @return array content for creating csv
         */
        public function wdmFetchData()
        {
            global $wpdb;
            $wpusp_role_table      = $wpdb->prefix . 'wusp_role_pricing_mapping';
            $wdm_post              = $wpdb->prefix . 'posts';
            $role_heading   = array( 'Product id', 'role', 'Min Qty', 'Flat', '%' );

            $role_product_result        = $wpdb->get_results("SELECT product_id, role, min_qty, price, flat_or_discount_price as discount_price FROM {$wpusp_role_table}, {$wdm_post} where {$wpusp_role_table}.product_id = {$wdm_post}.id");
  
            if ($role_product_result) {
                $role_product_result = $this->processResult($role_product_result);
                return array( $role_heading, $role_product_result );
            }
        }

        /**
         * [processResult process the data to be exported]
         * @param  [array] $role_product_result [Fetched result from database]
         * @return [array]                       [Processed result]
         */
        public function processResult($role_product_result)
        {
            foreach ($role_product_result as $key => $result) {
                if ($result->discount_price == 2) {
                    $role_product_result[$key]->discount_price = $result->price;
                    $role_product_result[$key]->price = '';
                } else {
                    $role_product_result[$key]->discount_price = '' ;
                }
            }
            return $role_product_result;
        }

        /**
         * returns name of file for export
         * @return string filename
         */
        public function wdmFileName()
        {
            return '/role_specific_pricing.csv';
        }
    }

}
