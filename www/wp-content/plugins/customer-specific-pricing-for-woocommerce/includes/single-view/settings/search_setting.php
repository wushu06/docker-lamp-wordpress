<?php

namespace cspSingleView\searchSetting;

if (!class_exists('WdmSingleViewSearch')) {
    class WdmSingleViewSearch
    {

        public function __construct()
        {
            add_action('csp_single_view_search_settings', array($this,'searchSettingsCallback'));
        }

        public function searchSettingsCallback()
        {
            $available_options = apply_filters('csp_single_view_option_types', array('customer' => __('Customer', CSP_TD),
                                       'role' => __('Role', CSP_TD),
                                       'group'=> __('Group', CSP_TD)));

            self::enqueueScript();

            ?><hr/>
                <div class="update-nag wdm-tab-info nomargin"><?php _e('Search the <strong>ACTIVE</strong> price being applied to specific customer, role or group.', CSP_TD) ?></div>
                <div class="wdm-csp-single-view-search-wrapper">

                    <div class="form-group row wdm-csp-single-view-from-group">
                        <label class="col-md-3 form-control-label"> <?php echo _e('Search price being applied for a', CSP_TD); ?> </label>
                        <div class="col-md-4 form-control-wrap">
                            <select name="wdm_setting_option_type" id="wdm_setting_option_type" class="form-control wdm-csp-single-view-form-control">
                                <option value="-1"><?php echo __('Select any value', CSP_TD); ?></option>
    <?php if (!empty($available_options) && is_array($available_options)) {
        foreach ($available_options as $key => $value) { ?>
        <option value="<?php echo $key; ?>"><?php echo $value;?></option>
                                    <?php
        } //foreach ends
} //if ends?>
                            </select>
                        </div>
                    </div>

                    <div class="wdm-csp-single-view-result-wrapper">
                    </div>
                </div>
                    <?php
        }//function ends -- Search Tab callback

        private function enqueueScript()
        {
            //Enqueue JS & CSS

            wp_enqueue_style('csp_general_css_handler', plugins_url('/css/single-view/wdm-single-view.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
            wp_enqueue_script('csp_single_search_js', plugins_url('/js/single-view/wdm-search-settings.js', dirname(dirname(dirname(__FILE__)))), array('jquery'), CSP_VERSION);

            $titles = array(
                            array( 'title' => __('Product Name', CSP_TD) ),
                            array( 'title' => __('Min Qty', CSP_TD) ),
                            array( 'title' => __('Active Price', CSP_TD) ),
                            array( 'title' => __('Discount Type', CSP_TD) ),
                            array( 'title' => __('Rule No.', CSP_TD) ),
                            array( 'title' => __('Source', CSP_TD) ),
                            );

            $array_to_be_sent = array('admin_ajax_path' => admin_url('admin-ajax.php'),
             'loading_image_path' => plugins_url('/images/loading .gif', dirname(dirname(dirname(__FILE__)))),
             'title_names' => $titles,
             'length_menu' => __('Show _MENU_ entries', CSP_TD),
             'showing_info'=> __('Showing _START_ to _END_ of _TOTAL_ entries', CSP_TD),
             'empty_table' => __('No data available in table', CSP_TD),
             'info_empty'=> __('Showing 0 to 0 of 0 entries', CSP_TD),
             'info_filtered'=> __('(filtered from _MAX_ total entries)', CSP_TD),
             'zero_records'=> __('No matching records found', CSP_TD),
             'loading_records'=> __('Loading...', CSP_TD),
             'processing' => __('Processing...', CSP_TD),
             'search' => __('Search:', CSP_TD),
             'first' => __('First', CSP_TD),
             'prev' => __('Previous', CSP_TD),
             'next' => __('Next', CSP_TD),
             'last' => __('Last', CSP_TD),

             // 'zero_records'=> __('No matching records found', CSP_TD),
             'query_log_link' => admin_url("/admin.php?page=customer_specific_pricing_single_view&tab=product_pricing&query_log=")
             );

            wp_localize_script('csp_single_search_js', 'single_view_obj', $array_to_be_sent);

            //Bootstrap
            wp_enqueue_style('csp_bootstrap_css', plugins_url('/css/import-css/bootstrap.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);

            //Datatable
            wp_enqueue_script('csp_singleview_datatable_js', plugins_url('/js/single-view/jquery.dataTables.min.js', dirname(dirname(dirname(__FILE__)))), array('jquery'), CSP_VERSION);
            wp_enqueue_script('csp_singleview_bootstrap_datatable_js', plugins_url('/js/single-view/dataTables.bootstrap.min.js', dirname(dirname(dirname(__FILE__)))), array('jquery'), CSP_VERSION);
            wp_enqueue_script('csp_singleview_button_js', plugins_url('/js/single-view/dataTables.buttons.min.js', dirname(dirname(dirname(__FILE__)))), array('csp_singleview_datatable_js'), CSP_VERSION);
            wp_enqueue_script('csp_singleview_button_column_js', plugins_url('/js/single-view/buttons.colVis.min.js', dirname(dirname(dirname(__FILE__)))), array('csp_singleview_datatable_js'), CSP_VERSION);

            wp_enqueue_style('csp_datatable_bootstrap_css', plugins_url('/css/single-view/dataTables.bootstrap.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
            wp_enqueue_style('csp_datatable_css', plugins_url('/css/single-view/jquery.dataTables.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
            wp_enqueue_style('csp_button_datatable_css', plugins_url('/css/single-view/buttons.dataTables.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
        }//enqueueScript ends
    }
}
