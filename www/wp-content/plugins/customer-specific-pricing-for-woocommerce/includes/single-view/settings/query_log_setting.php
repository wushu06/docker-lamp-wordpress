<?php

namespace cspSingleView\queryLogSetting;

if (! class_exists('WdmSingleViewQueryLog')) {

    class WdmSingleViewQueryLog
    {

        public function __construct()
        {
            add_action('csp_single_view_rule_log', array( $this, 'queryLogSettingsCallback' ));
        }

        public function queryLogSettingsCallback()
        {
            global $wpdb, $ruleManager;
            $activeRulesTab  = '';
            $safeToDeleteTab = '';
            $activeCondition = '';
            if (isset($_GET[ 'subtab' ]) && ($_GET[ 'subtab' ] == 'safe_to_delete')) {
                $safeToDeleteTab = 'current';
                $activeCondition = ' active = 0 ';
            } else {
                $activeRulesTab  = 'current';
                $activeCondition = ' active = 1 ';
            }
            ?> 
            <div class="wdm-clear">
                <div class="update-nag wdm-tab-info"><?php _e("List of rules you created in 'Set Rules' tab.", CSP_TD) ?> </div></div>
            <div class = "rule-vertical-space">
                <ul class="subsubsub">
                    <li>
                        <a href="admin.php?page=customer_specific_pricing_single_view&tab=rule_log" class="<?php echo $activeRulesTab ?>"><?php _e('Active', CSP_TD) ?></a> |
                    </li>
                    <li>
                    <a href="admin.php?page=customer_specific_pricing_single_view&tab=rule_log&subtab=safe_to_delete" class="<?php echo $safeToDeleteTab ?>"><?php _e('Inactive', CSP_TD) ?></a>
                    </li>
                </ul>
            </div> 
            

            <?php
            self::enqueueScript();
            $query = "SELECT rule_id, rule_id as rule_number, rule_title, rule_type, DATE_FORMAT(  `rule_creation_time` ,  '%d-%M-%Y %k:%i:%s' ) AS  'rule_time', DATE_FORMAT(  `rule_modification_time` ,  '%d-%M-%Y %k:%i:%s' ), active FROM {$ruleManager->ruleTable} WHERE {$activeCondition} ORDER BY  `rule_time` DESC";


            $query_log_result = $wpdb->get_results($query, ARRAY_N);

            foreach ($query_log_result as $key => $res) {
                if ($res[3] == 'Customer') {
                    $res[3] = __('Customer', CSP_TD);
                } elseif ($res[3] == 'Role') {
                    $res[3] = __('Role', CSP_TD);
                } elseif ($res[3] == 'Group') {
                    $res[3] = __('Group', CSP_TD);
                }
                $query_log_result[$key] = $res;
            }

            if (count($query_log_result) > 0) {
                $titles = array(
                    array( 'title' => '<input name="select_all" value="1" type="checkbox">' ),
                    array( 'title' => __('Rule No.', CSP_TD) ),
                    array( 'title' => __('Rule Title', CSP_TD) ),
                    array( 'title' => __('Rule Type', CSP_TD) ),
                    array( 'title' => __('Rule Creation Time', CSP_TD) ),
                    array( 'title' => __('Rule Modification Time', CSP_TD) ),
                    array( 'title' => __('Active', CSP_TD) ),
                );

                $array_to_be_sent = array( 'admin_ajax_path'     => admin_url('admin-ajax.php'),
                    'loading_image_path' => plugins_url('/images/loading .gif', dirname(dirname(dirname(__FILE__)))),
                    'title_names'        => $titles,
                    'query_log_link'     => admin_url('/admin.php?page=customer_specific_pricing_single_view&tab=product_pricing&query_log='),
                    'data'               => $query_log_result,
                    'error_message'      => __('Please, select some log.', CSP_TD),
                    'error_log_empty'    => __('Please, Save some Rule log. Rule log list empty.', CSP_TD),
                    'confirm_msg'        => __('Are you sure, You want to delete this rule?', CSP_TD),
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
                    'is_it_safe_to_delete' => isset($_GET[ 'subtab' ]) && ($_GET[ 'subtab' ] == 'safe_to_delete') ? true : false,
                );

                wp_register_script('csp_single_qlog_js', plugins_url('/js/single-view/wdm-query-log-settings.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);
                wp_enqueue_script('csp_single_qlog_js');

                wp_localize_script('csp_single_qlog_js', 'single_view_obj', $array_to_be_sent);
                ?>
                <!-- <h4 class="wdm-csp-single-view-main-title"><?php echo __('Rule Log', CSP_TD);
                ?> </h4> -->

                <div class="wdm-csp-query-log-wrapper">

                    <input type="button" class="btn btn-primary hide" id="wdm_delete_qlog" value="<?php _e('Delete', CSP_TD);
                ?>">
                </div>
                <?php
            } else {
                ?>
                <div class="notice-error csp-no-data-error">
                    <p>
                        <?php
                        if (isset($_GET[ 'subtab' ]) && ($_GET[ 'subtab' ] == 'safe_to_delete')) {
                            _e('No inactive rules found.', CSP_TD);
                        } else {
                            _e('No active rules found.', CSP_TD);
                        }
                        ?></p>
                </div>
                <?php
            }
        }

//function ends -- Search Tab callback

        private function enqueueScript()
        {
            //Enqueue JS & CSS

            wp_enqueue_style('csp_general_css_handler', plugins_url('/css/single-view/wdm-single-view.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);

            //Bootstrap
            wp_enqueue_style('csp_bootstrap_css', plugins_url('/css/import-css/bootstrap.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);

            //Datatable
            wp_enqueue_script('csp_singleview_datatable_js', plugins_url('/js/single-view/jquery.dataTables.min.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);
            wp_enqueue_script('csp_singleview_bootstrap_datatable_js', plugins_url('/js/single-view/dataTables.bootstrap.min.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);
            wp_enqueue_script('csp_singleview_button_js', plugins_url('/js/single-view/dataTables.buttons.min.js', dirname(dirname(dirname(__FILE__)))), array( 'csp_singleview_datatable_js' ), CSP_VERSION);
            wp_enqueue_script('csp_singleview_button_column_js', plugins_url('/js/single-view/buttons.colVis.min.js', dirname(dirname(dirname(__FILE__)))), array( 'csp_singleview_datatable_js' ), CSP_VERSION);

            wp_enqueue_style('csp_datatable_bootstrap_css', plugins_url('/css/single-view/dataTables.bootstrap.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
            wp_enqueue_style('csp_datatable_css', plugins_url('/css/single-view/jquery.dataTables.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
            wp_enqueue_style('csp_button_datatable_css', plugins_url('/css/single-view/buttons.dataTables.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
        }

//enqueueScript ends
    }

}
