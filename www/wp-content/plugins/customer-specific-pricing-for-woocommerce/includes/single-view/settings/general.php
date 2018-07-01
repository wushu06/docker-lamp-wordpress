<?php

namespace cspSingleView\general;

if (! class_exists('WdmSingleViewGeneral')) {
    
    class WdmSingleViewGeneral
    {

        public function __construct()
        {
            add_action('csp_single_view_product_pricing', array( $this, 'generalSettingCallback' ));
        }

        public function generalSettingCallback()
        {
            global $wpdb, $ruleManager;
            $query_log_id        = isset($_GET[ 'query_log' ]) ? $_GET[ 'query_log' ] : '';
            $option_selected     = '';
            $query_log_result    = '';

            self::enqueueScript();

            if (! empty($query_log_id)) {
                //$query = 'SELECT `selection_type`, `selection_list`, `product_list`, `query_title` FROM `'.$wpdb->prefix.'wusp_query_log` WHERE `query_id` = '.$query_log_id;

                $query               = "SELECT rule_title, rule_type FROM {$ruleManager->ruleTable} WHERE rule_id = %d";
                $query_log_result    = $wpdb->get_row($wpdb->prepare($query, $query_log_id));
                if ($query_log_result != null) {
                    $option_selected = strtolower($query_log_result->rule_type);
                }
            }

            $available_options = apply_filters('csp_single_view_option_types', array( 'customer'    => __('Customer Specific Pricing', CSP_TD),
                'role'       => __('Role Specific Pricing', CSP_TD),
                'group'      => __('Group Specific Pricing', CSP_TD), ));
            ?>
            <hr/>
            <div class="update-nag wdm-tab-info nomargin"><?php _e('Set rules for multiple products for specific customer, role  or group.', CSP_TD) ?></div>
            <div class="wdm-csp-single-view-general-wrapper">

                <div class="form-group row wdm-csp-single-view-from-group">
                    <label class="col-md-3 form-control-label"> <?php echo _e('Select Option', CSP_TD);
            ?> </label>
                    <div class="col-md-4 form-control-wrap">
                        <select name="wdm_setting_option_type" id="wdm_setting_option_type" class="form-control wdm-csp-single-view-form-control">
                            <option value="-1"><?php echo __('Select any value', CSP_TD);
            ?></option>
            <?php
                 
            if (! empty($available_options) && is_array($available_options)) {
                foreach ($available_options as $key => $value) {
                    ?>
                                    <option value="<?php echo $key;
                    ?>" <?php selected($key, $option_selected);
                    ?>><?php echo $value;
                    ?></option>
                                            <?php
                } //foreach ends
            } //if ends
                                ?>
                        </select>
                    </div>
                </div>

                <div class="wdm-csp-single-view-result-wrapper">
            <?php

            $product_result = self::loadExistingDetails($option_selected, $query_log_result, $query_log_id);

            $array_to_be_sent = array( 'admin_ajax_path'             => admin_url('admin-ajax.php'),
                'error_selection_empty'      => __(' selection empty.', CSP_TD),
                'error_product_list_empty'   => __('product selection empty.', CSP_TD),
                'hide_column_msg'            => __('Hide / Show Columns', CSP_TD),
                'invalid_quantity_value'     => __('Invalid Quantity Value', CSP_TD),
                'error_query_title_empty'    => __('Please fill Rule Title.', CSP_TD),
                'error_all_fields_empty'     => __('Please fill some values, all fields are empty.', CSP_TD),
                'error_field_not_numeric'    => __('All values should be numeric.', CSP_TD),
                'error_field_max_val'        => __('Max val for discount type % should be less than 100'),
                'error_field_negative_number'=> __('Values should not be negative.', CSP_TD),
                'confirm_msg_if_error'       => __('There are some errors, do you still want to proceed? This will override all other values.', CSP_TD),
                'confirm_msg_if_empty'       => __('Some fields are empty, do you still want to proceed? Empty fields will be ignored.', CSP_TD),
                'confirm_msg_invalid_values' => __('Some fields have invalid values, do you still want to proceed? Empty fields will be ignored.', CSP_TD),
                'confirm_msg'                => __('This will override all values. Do you still want to proceed?', CSP_TD),
                'loading_image_path'         => plugins_url('/images/loading .gif', dirname(dirname(dirname(__FILE__)))),
                'progress_loading_text'      => __('Loading..', CSP_TD),
                'progress_complete_text'     => __('Completed', CSP_TD),
                'query_log_id'               => $query_log_id,
                'product_result'             => $product_result,
                'customer_text'              => __('customer'),
                'role_text'              => __('role'),
                'group_text'              => __('group'),
                'change_product_selection'            => __(' or products selection', CSP_TD),
                'change_text'                => __('Change ', CSP_TD),
                // 'change_group'               => __('Change group or products selection', CSP_TD),
                'update_rule'                => __('Update Rule', CSP_TD),
                'show_all'                   => __('Show all', CSP_TD),
                'showing_all'                => __('Showing all {0}', CSP_TD),
                'empty_list'                => __('Empty list', CSP_TD),
                'filter'                    => __('filter', CSP_TD),
                'filtered'                  => __('filtered', CSP_TD),
                'move_selected'             => __('Move selected', CSP_TD),
                'move_all'                  => __('Move all', CSP_TD),
                'remove_all'                => __('Remove all', CSP_TD),
                'remove_selected'           => __('Remove selected', CSP_TD),
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

                );

            wp_localize_script('csp_single_general_js', 'single_view_obj', $array_to_be_sent);

            if (! empty($option_selected)) {
                self::loadAdditionalButtons();
            }
            ?>
                </div>
            </div>
                    <?php
        }

        public function getSelectionValues($option_selected, $selectedEntities)
        {
            global $wpdb;
            $selectionValues = array();

            if ($option_selected == "customer") {
                foreach ($selectedEntities as $value) {
                    $result =  $wpdb->get_row($wpdb->prepare("SELECT display_name FROM ".$wpdb->prefix."users WHERE ID = %d", $value));
                    $selectionValues[$value] = $result->display_name;
                }
            } elseif ($option_selected == "role") {
                $availableRoles = array_reverse(get_editable_roles());
                $roleKeys = array();
                if (! empty($availableRoles)) {
                    $roleKeys = array_keys($availableRoles);
                }
                foreach ($selectedEntities as $value) {
                    if (in_array($value, $roleKeys)) {
                        $selectionValues[$value] = translate_user_role($availableRoles[$value]['name']);
                    }
                }
            } elseif ($option_selected == "group") {
                foreach ($selectedEntities as $value) {
                    $result =  $wpdb->get_row($wpdb->prepare("SELECT name FROM ".$wpdb->prefix."groups_group WHERE group_id = %d", $value));
                    $selectionValues[$value] = $result->name;
                }
            }
            return $selectionValues;
        }

        private function loadExistingDetails($option_selected, $query_log_result, $query_log_id)
        {
            global $subruleManager;
            $product_result      = '';
            $subruleInfo         = '';
            $selectedEntities    = array();
            $selectedProducts    = array();
            $selectionValues     = array();

            if (! empty($option_selected) && ! is_wp_error($query_log_result)) {
                if ($query_log_id != null) {
                    $subruleInfo = $subruleManager->getAllSubrulesInfoForRule($query_log_id);
                }
                        
                if (empty($subruleInfo)) {
                    return;
                }

                //Find out all entities and products which were selected
                if (is_array($subruleInfo)) {
                    foreach ($subruleInfo as $singleSubrule) {
                        if (! in_array($singleSubrule[ 'associated_entity' ], $selectedEntities)) {
                            $selectedEntities[] = $singleSubrule[ 'associated_entity' ];
                        }

                        $selectionValues = $this->getSelectionValues($option_selected, $selectedEntities);

                                
                        if (! in_array($singleSubrule[ 'product_id' ], $selectedProducts)) {
                            $selectedProducts[] = $singleSubrule[ 'product_id' ];
                        }
                    }
                }
                $csp_ajax = new \cspAjax\WdmWuspAjax();

                
                $csp_ajax->displayTypeSelection($option_selected, $selectedEntities, $selectedProducts);
        
                $product_result[ 'title_name' ] = $csp_ajax->getProductDetailTitles($option_selected);
                $product_name_list   = array();
                $product_list        =  $selectedProducts;

                foreach ($product_list as $single_product_id) {
                    if (get_post_type($single_product_id) == 'product_variation') {
                        $parent_id           = wp_get_post_parent_id($single_product_id);
                        $product_title       = get_the_title($parent_id);
                        $variable_product    = new \WC_Product_Variation($single_product_id);
                        $attributes          = $variable_product->get_variation_attributes();

                        //get all attributes name associated with this variation
                        $attribute_names = array_keys($variable_product->get_attributes());

                        $pos = 0; //Counter for the position of empty attribute
                        foreach ($attributes as $key => $value) {
                            if (empty($value)) {
                                $attributes[$key] = "Any ".$attribute_names[$pos++];
                            }
                        }

                        $product_title .= '-->' . implode(', ', $attributes);

                        $product_name_list[ $single_product_id ] = $product_title;
                    } else {
                        $product_name_list[ $single_product_id ] = get_the_title($single_product_id);
                    }
                }

                $product_result[ 'value' ] = $csp_ajax->getProductDetailList($option_selected, $product_name_list, $selectionValues, $subruleInfo);

                $product_result[ 'query_input' ] = $csp_ajax->getQueryInput($query_log_result->rule_title);
            }

            return $product_result;
        }

        private function loadAdditionalButtons()
        {
            ?>
            <p class="pull-right">
<!--				<input type="button" class="btn btn-primary" id="wdm_edit_entries" value="<?php _e('Edit', CSP_TD) ?>"/>-->
<!--				<input type="button" class="btn btn-primary" id="wdm_clear_entries" value="<?php _e('Clear', CSP_TD) ?>"/>-->
            </p>
            <?php
        }

        private function enqueueScript()
        {
            //Enqueue JS & CSS

            wp_enqueue_style('csp_general_css_handler', plugins_url('/css/single-view/wdm-single-view.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
            wp_enqueue_script('csp_single_general_js', plugins_url('/js/single-view/wdm-general-settings.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);

            //ListBox asset
            wp_enqueue_script('csp_singleview_listbox_js', plugins_url('/js/single-view/jquery.bootstrap-duallistbox.min.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);

            wp_enqueue_style('csp_singleview_listbox_css', plugins_url('/css/single-view/bootstrap-duallistbox.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);

            //Bootstrap
            wp_enqueue_style('csp_bootstrap_css', plugins_url('/css/import-css/bootstrap.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);

            //Progress bar
            wp_enqueue_script('csp_bootstrap_progressbar_js', plugins_url('/js/single-view/bootstrap-progressbar.min.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);
            wp_enqueue_style('csp_bootstrap_progressbar_css', plugins_url('/css/single-view/bootstrap-progressbar-3.3.4.min.css', dirname(dirname(dirname(__FILE__)))), CSP_VERSION);

            //Datatable
            wp_enqueue_script('csp_singleview_datatable_js', plugins_url('/js/single-view/jquery.dataTables.min.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);
            wp_enqueue_script('csp_singleview_bootstrap_datatable_js', plugins_url('/js/single-view/dataTables.bootstrap.min.js', dirname(dirname(dirname(__FILE__)))), array( 'jquery' ), CSP_VERSION);
            wp_enqueue_script('csp_singleview_button_js', plugins_url('/js/single-view/dataTables.buttons.min.js', dirname(dirname(dirname(__FILE__)))), array( 'csp_singleview_datatable_js' ), CSP_VERSION);
            wp_enqueue_script('csp_singleview_button_column_js', plugins_url('/js/single-view/buttons.colVis.min.js', dirname(dirname(dirname(__FILE__)))), array( 'csp_singleview_datatable_js' ), CSP_VERSION);

            wp_enqueue_style('csp_datatable_bootstrap_css', plugins_url('/css/single-view/dataTables.bootstrap.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
            wp_enqueue_style('csp_datatable_css', plugins_url('/css/single-view/jquery.dataTables.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
            wp_enqueue_style('csp_button_datatable_css', plugins_url('/css/single-view/buttons.dataTables.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
        }
    }

}
