<?php

namespace cspAjax;

if (! class_exists('WdmWuspAjax')) {

    class WdmWuspAjax
    {

        public function __construct()
        {
            add_action('wp_ajax_create_csv', array($this, 'wdmCreateCsv'));
            add_action('wp_ajax_get_type_selection_result', array( $this, 'getTypeSelectionResultCallback' ));

            add_action('wp_ajax_wusp_dismiss_import_notice', array($this, 'updateImportNoticeDismissalFlag'));

            add_action('wp_ajax_get_product_price_list', array( $this, 'getProductPriceListCallback' ));

            add_action('wp_ajax_save_query_log', array( $this, 'saveQueryLogCallback' ));

            add_action('wp_ajax_get_progress_status', array( $this, 'getProgressStatusCallback' ));

            add_action('wp_ajax_get_search_selection_result', array( $this, 'getSearchSelectionCallback' ));

            add_action('wp_ajax_display_product_prices_selection', array( $this, 'displayProductPricesCallback' ));

            add_action('wp_ajax_remove_query_log', array( $this, 'removeQueryLogCallback' ));

            add_action('wp_ajax_drop_batch_numbers', array( $this, 'dropBatchNumbers'));
        }

        function updateImportNoticeDismissalFlag()
        {
            update_option('csp_import_notice_dismissed', 1);
            die();
        }

        function dropBatchNumbers()
        {
            $fileType = isset($_POST['file_type']) ? $_POST['file_type'] : "";
            $cspTable = "";
            if (!empty($fileType)) {
                $this->deleteBatchColumn($fileType);
            }

            die();
        }

        function deleteBatchColumn($fileType)
        {
            global $wpdb;
            if ($fileType == "user") {
                $cspTable = $wpdb->prefix . 'wusp_user_pricing_mapping';
            } elseif ($fileType == "role") {
                $cspTable = $wpdb->prefix . 'wusp_role_pricing_mapping';
            } elseif ($fileType == "group") {
                $cspTable = $wpdb->prefix . 'wusp_group_product_price_mapping';
            }

            $existingColumn = $wpdb->get_var("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$cspTable}' AND column_name = 'batch_numbers'");

            if (!empty($existingColumn)) {
                $wpdb->query("ALTER TABLE $cspTable DROP COLUMN batch_numbers");
            }
        }

        /**
         * create csv file for export
         */
        public function wdmCreateCsv()
        {
            //WdmUserSpecificPricingExport
            $nonce = $_REQUEST['_wpnonce'];
            $nonce_verification = wp_verify_nonce($nonce, 'export_nonce');

            //Override nonce verification for extending import functionality in any third party extension
            $nonce_verification = apply_filters('csp_export_nonce_verification', $nonce_verification);
            if (! $nonce_verification) {
                 echo "Security Check";
                 exit;
            } else {
                //Allow only admin to import csv files
                $capabilityToExport = apply_filters('csp_export_allowed_user_capability', 'manage_options');
                $can_user_export = apply_filters('csp_can_user_export_csv', current_user_can($capabilityToExport));
                if (!$can_user_export) {
                    echo "Security Check";
                    exit;
                }
            }

            $class_name = isset($_POST['option_val'])? '\cspImportExport\cspExport\WdmWusp' . $_POST['option_val'] . 'SpecificPricingExport' : '';

            if (!empty($class_name)) {
                $export_object = new $class_name();
                $user_product_mapping = $export_object->wdmFetchData();
                if (isset($user_product_mapping)) {
                    $file_name = $export_object->wdmFileName();
                    $upload_dir = wp_upload_dir();

                    $deleteFile = glob($upload_dir['basedir'] . $file_name);
                    if ($deleteFile) {
                        foreach ($deleteFile as $file) {
                            unlink($file);
                        }
                    }

                    $output = fopen($upload_dir['basedir'] . $file_name, 'w');
                    fputcsv($output, $user_product_mapping[0]);
                    foreach ($user_product_mapping[1] as $row) {
                        $array = (array) $row;
                        fputcsv($output, $array);
                    }
                    fclose($output);
                    echo $upload_dir['baseurl'] . $file_name;
                } else {
                    echo menu_page_url('customer_specific_pricing_export');
                }
            } else {
                echo menu_page_url('customer_specific_pricing_export');
            }
            exit();
        }

        public function getTypeSelectionResultCallback()
        {
            //Allow only admin to get selection
            $capability_required = apply_filters('csp_get_type_selection_user_capability', 'manage_options');
            $can_user_select = apply_filters('csp_can_user_get_type_selection', current_user_can($capability_required));
            if (!$can_user_select) {
                echo "Security Check";
                exit;
            }

            $option_selection = isset($_POST[ 'option_type' ]) ? $_POST[ 'option_type' ] : '';

            if (! empty($option_selection)) {
                $this->displayTypeSelection($option_selection);
            } else {
                $this->cspDisplayError(__('There is some error in option selection.', CSP_TD));
            }

            die();
        }

        public function getSearchSelectionCallback()
        {
            //Allow only admin to get selection
            $capability_required = apply_filters('csp_get_search_selection_user_capability', 'manage_options');
            $can_user_select = apply_filters('csp_can_user_get_search_selection', current_user_can($capability_required));
            if (!$can_user_select) {
                echo "Security Check";
                exit;
            }
            $option_selection = isset($_POST[ 'option_type' ]) ? $_POST[ 'option_type' ] : '';

            if (! empty($option_selection)) {
                $selection_list = $this->getSelectionList($option_selection);

                $this->displaySelectionList($selection_list, array(), false);
            } else {
                $this->cspDisplayError(__('There is some error in option selection.', CSP_TD));
            }

            die();
        }

    /**
         * Displays Selection & Product List
         * @param  [type] $option_selection [description]
         * @param  string $query_log_id     [description]
         * @return [type]                   [description]
         */
        public function displayTypeSelection($option_selection, $existing_selections = array(), $existing_products = array())
        {

            //"Customer"
            $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));

            if ($option_selection === 'group' && ! in_array('groups/groups.php', $active_plugins)) {
                $this->cspDisplayError(__("Activate the 'Groups' Plugin to enjoy the benefits of Group Specific Pricing.", CSP_TD));
                die();
            }
            ?>
            <div class="csp-selection-wrapper wdm-clear">
            <?php

            $selection_list  = $this->getSelectionList($option_selection);

            $product_list    = $this->getProductList();

            $this->displaySelectionList($selection_list, $existing_selections);

            $this->displayProductList($product_list, $existing_products);

            if (! empty($selection_list) && ! empty($product_list)) {
                ?>
                <input type="button" class="btn btn-primary" id="wdm_csp_set_price" value="<?php echo __('Set Prices', CSP_TD); ?>">
                <!-- Show edit button only if query_log parameter is set -->
                <?php if (isset($_GET[ 'query_log' ])) { ?>
                        <input type="button" class="btn btn-primary" id="wdm_edit_entries" value="<?php _e('Edit this rule', CSP_TD) ?>"/>
                        <input type="button" class="btn btn-primary" id="wdm_back" data-selected-feild = "akshay" value="<?php _e('Back', CSP_TD) ?>"/>
                    <?php } ?>
                <div class="wdm-csp-product-details-list"></div>
                <?php
            }
            ?>
            </div>

            <?php
        }

//function ends -- displayTypeSelection

        public function cspDisplayError($error_string)
        {
            ?>
            <div class="error">
            <p><?php echo $error_string; ?> </p>
            </div>
            <?php
        }

        private function getOptionSelection($optionType)
        {
            if ($optionType == 'customer') {
                return __('customer', CSP_TD);
            }
            if ($optionType == 'role') {
                return __('role', CSP_TD);
            }
            if ($optionType == 'group') {
                return __('group', CSP_TD);
            }
        }

        private function displaySelectionList($selection_list, $existing_selections = array(), $default_option = false)
        {
            if (isset($_POST['option_type'])) {
                $option_selection = $this->getOptionSelection($_POST['option_type']);
            } else {
                $option_selection = '';
            }

            if (isset($selection_list[ 'value' ]) && is_array($selection_list[ 'value' ])) {
                if (isset($_POST['single_view_action']) && $_POST['single_view_action'] == 'search') {
                    $this->printSearchDropdown($option_selection, $default_option, $selection_list, $existing_selections);
                } else {
                    $this->printSelectionDropdown($option_selection, $default_option, $selection_list, $existing_selections);
                }
            ?>

                        <?php
            } else {
                $this->cspDisplayError(__('Selection List empty.', CSP_TD));
            }
        }

        private function printSelectionDropdown($option_selection, $default_option, $selection_list, $existing_selections)
        {
            ?>
            <div class="csp-selection-list-wrapper">
                <div class="form-group row">
                    <label class="wdm-csp-single-view-section-heading col-md-2 form-control-label">
                    <?php echo isset($selection_list[ 'label' ]) ? $selection_list[ 'label' ] : ''; ?>
                    </label>
                    <div class="col-md-4 form-control-wrap form-control-wrap-alt">
                        <select name='wdm_selections' class="form-control wdm-csp-single-view-form-control" id="selected-list_wdm_selections" multiple>
                <?php
                foreach ($selection_list[ 'value' ] as $key => $value) {
                ?>
           <option value="<?php echo $key; ?>" <?php
            if (in_array($key, $existing_selections)) {
                echo 'selected="selected"';
            }
                    ?>><?php echo $value; ?></option><?php
                }//foreach ends
                ?>
                        </select>
                    </div>
                </div>
            </div>
        <?php
        }

        private function printSearchDropdown($option_selection, $default_option, $selection_list, $existing_selections)
        {
            ?>
            <div class="csp-selection-list-wrapper">
                <div class="form-group row">
                    <label class="wdm-csp-single-view-section-heading col-md-3 form-control-label select-entity-type">
                    <?php echo isset($selection_list[ 'label' ]) ? $selection_list[ 'label' ] : ''; ?>
                    </label>
                    <div class="col-md-4 form-control-wrap form-control-wrap-alt">
                        <select name='wdm_selections' class="form-control wdm-csp-single-view-form-control" id="selected-list_wdm_selections">
                            <option value="-1"><?php echo __('Select', CSP_TD) . ' ' . ucfirst($option_selection); ?></option>
                        <?php
                        if ($default_option !== false) {
                        ?><option value="-1"><?php __('Select', CSP_TD) . ' ' . $default_option; ?></option><?php
                        }
                        foreach ($selection_list[ 'value' ] as $key => $value) {
                        ?><option value="<?php echo $key; ?>" <?php
if (in_array($key, $existing_selections)) {
    echo 'selected="selected"';
}
                        ?>><?php echo $value; ?></option><?php
                        }//foreach ends
                        ?>
                        </select>
                    </div>
                </div>
            </div>
        <?php
        }

        private function displayProductList($product_list, $existing_products = array())
        {
            if (! empty($product_list)) {
                ?>
                <div class="csp-product-list csp-selection-wrapper-sections">
            <div class="form-group row">
                <label class="wdm-csp-single-view-section-heading col-md-2 form-control-label"><?php echo __('Select Product', CSP_TD); ?></label>
                    <div class="col-md-4 form-control-wrap form-control-wrap-alt">
                        <select name='wdm_product_lists' id='wdm_product_lists' multiple class="form-control wdm-csp-single-view-form-control">
                <?php
                foreach ($product_list as $product_id => $product_name) {
                    ?>
                    <option value="<?php echo $product_id; ?>" <?php
                    if (in_array($product_id, $existing_products)) {
                        echo 'selected="selected"';
                    }
                    ?>><?php echo $product_name; ?></option>
                                    <?php
                }
                ?>
                </select>
                </div>
                </div>
                        <?php
            } else {
                $this->cspDisplayError(__('Please add Products.', CSP_TD));
            }
        }

        private function getSelectionList($option_type)
        {
            $option_type = strtolower($option_type);
            global $wpdb;
            $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));

            if ($option_type === 'group' && ! in_array('groups/groups.php', $active_plugins)) {
                $this->cspDisplayError(__("Activate the 'Groups' Plugin to enjoy the benefits of Group Specific Pricing.", CSP_TD));
                die();
            }

            $selection_list = array();

            if (! empty($option_type)) {
                if ($option_type === 'customer') {
                    $query = 'SELECT `ID`,`display_name`
							  FROM `' . $wpdb->prefix . 'users`
							  ORDER BY `display_name`';

                    $user_list = $wpdb->get_results($query);

                    if (! empty($user_list)) {
                        $selection_list[ 'label' ] = __('Select Customer', CSP_TD);

                        foreach ($user_list as $single_user) {
                            $selection_list[ 'value' ][ $single_user->ID ] = $single_user->display_name;
                        }//foreach ends --loop through user list
                    }//if ends -- User list not empty
                } elseif ($option_type === 'role') {
                    $editable_roles = array_reverse(get_editable_roles());

                    if (! empty($editable_roles)) {
                        $selection_list[ 'label' ] = __('Select Role', CSP_TD);

                        foreach ($editable_roles as $role => $details) {
                            $name                                = translate_user_role($details[ 'name' ]);
                            $selection_list[ 'value' ][ $role ]  = $name;
                        }//foreach ends
                    }//if ends -- editable rows not empty
                } elseif ($option_type === 'group') {
                    $query = 'SELECT  `group_id` ,  `name`
							  FROM  `' . $wpdb->prefix . 'groups_group`
							  Order By `name`';

                    $get_group_details = $wpdb->get_results($query);

                    if (! empty($get_group_details)) {
                        $selection_list[ 'label' ] = __('Select Groups', CSP_TD);

                        foreach ($get_group_details as $single_group) {
                            $selection_list[ 'value' ][ $single_group->group_id ] = $single_group->name;
                        }
                    }
                }
            }//if ends -- option_type not empty

            return $selection_list;
        }

//function ends -- getSelectionList

        private function getProductList()
        {
            $full_product_list = array();

            $products_list = get_posts(array(
                'post_type'      => array( 'product' ),
                'posts_per_page' => -1,
            ));
            
            if ($products_list) {
                foreach ($products_list as $singleProduct) :
                    $product_id = $singleProduct->ID;

                    $product = wc_get_product($product_id);
                    $product_title = get_the_title($product_id);

                    if ($product->get_type() == 'variable') {
                        $attribute_names = array_keys($product->get_attributes());
                        $available_variations    = $product->get_available_variations();
                        $default_variation       = array( 'variation_id' => '', 'attributes' => array() );
                        
                        foreach ($available_variations as $single_variation) {
                            $variation_args = wp_parse_args($single_variation, $default_variation);

                            $variation_id            = $variation_args[ 'variation_id' ];
                            $variation_attributes    = array_values($variation_args[ 'attributes' ]);
                            $pos = 0;
                            foreach ($variation_attributes as $key => $value) {
                                if (empty($value)) {
                                    $variation_attributes[$key] = "Any ".$attribute_names[$pos++];
                                }
                            }
                            
                            if (! empty($variation_id) && ! empty($variation_attributes)) {
                                $full_product_list[ $variation_id ] = $product_title . '-->' . implode(", ", $variation_attributes);
                            }
                        }
                    } //if ends
                    else {
                        $full_product_list[ $product_id ] = $product_title;
                    }
                //endwhile;
                endforeach;
                // exit;
            }//posts end

            /* Restore original Post Data */
            //wp_reset_postdata();

            // sort into alphabetical order, by title
            asort($full_product_list);
            return $full_product_list;
        }

        public function getProductPriceListCallback()
        {
            $capability_required = apply_filters('csp_get_product_price_list_user_capability', 'manage_options');
            $can_user_select = apply_filters('csp_can_user_get_product_price_list', current_user_can($capability_required));
            if (!$can_user_select) {
                echo "Security Check";
                exit;
            }
            $selection_list = '';
            if (isset($_POST[ 'selection_list' ])) {
                $selection_list = $_POST[ 'selection_list' ];
            }
            $product_list = '';
            if (isset($_POST[ 'product_list' ])) {
                $product_list = $_POST[ 'product_list' ];
            }
            $option_type = '';
            if (isset($_POST[ 'option_type' ])) {
                $option_type = $_POST[ 'option_type' ];
            }

            if (! empty($selection_list) && ! empty($product_list) && ! empty($option_type)) {
                //Process the details

                $product_result[ 'title_name' ] = $this->getProductDetailTitles($option_type);

                $product_result[ 'value' ] = $this->getProductDetailList($option_type, $product_list, $selection_list);

                $product_result[ 'query_input' ] = $this->getQueryInput();
                echo json_encode($product_result);
            } else {
                $this->cspDisplayError(__('Some details are not found.', CSP_TD));
            }
            die();
        }

        public function getProductDetailList($option_selected, $product_list, $selection_values, $subruleInfo = array())
        {

            $query_log_details   = array();
            if (! empty($subruleInfo)) {
                foreach ($subruleInfo as $singleRule) {
                    if (! isset($query_log_details[ $singleRule[ 'product_id' ].'_'.$singleRule[ 'associated_entity' ] ])) {
                        $query_log_details[ $singleRule[ 'product_id' ].'_'.$singleRule[ 'associated_entity' ] ][ 'action' ] = ($singleRule['flat_or_discount_price'] == '2') ? '2' : '1';
                        $query_log_details[ $singleRule[ 'product_id' ].'_'.$singleRule[ 'associated_entity' ] ][ 'value' ] = $singleRule[ 'price' ];
                        $query_log_details[ $singleRule[ 'product_id' ].'_'.$singleRule[ 'associated_entity' ] ][ 'min_qty' ] = $singleRule[ 'min_qty' ];
                    }
                }
            }
            $product_detail_list = $this->getProductPriceMapping($option_selected, $product_list, $selection_values, $query_log_details);

            return apply_filters('csp_single_view_product_list', $product_detail_list);
        }

        public function getProductPriceMapping($option_selected, $product_list, $selection_values, $query_log_details)
        {
            $discountOptions = array("1"=>__("Flat", CSP_TD), "2"=>"%");
            $product_detail_list = array();
            $value = '';
            $minQty = '';
            // $existing_qty = 1;

            foreach ($selection_values as $SingleUser => $SingleName) {
                $userId = $SingleUser;
                foreach ($product_list as $product_id => $product_name) {
                    $regular_price   = floatval(get_post_meta($product_id, '_regular_price', true));
                    $sale_price      = floatval(get_post_meta($product_id, '_sale_price', true));

                    $existing_qty       = 1;
                    $existing_value     = '';
                    $existing_action    = '';
                    if (isset($query_log_details[ $product_id.'_'.$userId ][ 'value' ])) {
                        $existing_value = wc_format_localized_price($query_log_details[ $product_id.'_'.$userId ][ 'value' ]);
                    }
                    if (isset($query_log_details[ $product_id.'_'.$userId ][ 'min_qty' ])) {
                        $existing_qty = $query_log_details[ $product_id.'_'.$userId ][ 'min_qty' ];
                    }
                    if (isset($query_log_details[ $product_id.'_'.$userId ][ 'action' ])) {
                        $existing_action = $query_log_details[ $product_id.'_'.$userId ][ 'action' ];
                    }

                    $minQty = '<input type="number" min = "1" value="' . $existing_qty . '" placeholder="1" name="csp_qty_'.$product_id.'_'.$userId.'" id="csp_qty" class="csp_single_view_qty" />';
                    if ($existing_action == '2') {
                        if ($discountOptions[$existing_action] == '%') {
                            $value = '<input type="text" value="' . $existing_value . '" placeholder="0" name="csp_value_'.$product_id.'_'.$userId.'" id="csp_value" class="csp_single_view_value csp-percent-discount" />';
                        }
                    } else {
                        $value = '<input type="text" value="' . $existing_value . '" placeholder="0" name="csp_value_' . $product_id . '_'.$userId.'" id="csp_value" class="csp_single_view_value" value="' . $existing_value . '"/>';
                    }

                    $action = '<select name="wdm_csp_price_type' . $product_id . '_'.$userId.'" class="chosen-select csp_single_view_action">';

                    foreach ($discountOptions as $k => $val) {
                        if ($existing_action == $k) {
                            $action .= '<option value = "'.$k.'" selected>'.$discountOptions[$k].'</option>';
                        } else {
                            $action .= '<option value = "'.$k.'">'.$discountOptions[$k].'</option>';
                        }
                        unset($val);
                    }
                    $action .= '</select>';
                    $product_detail_list[] = array( $product_id, $product_name, $SingleName, $regular_price, $sale_price, $action, $minQty, $value );
                }                # code...
            }

            return apply_filters('csp_single_view_product_price_mapping', $product_detail_list);
        }

        public function getProductDetailTitles($option_type)
        {
            $tableOptionTypes    = array(
            'role'       => __('Roles', CSP_TD),
            'group'      => __('Groups', CSP_TD),
            'customer'   => __('Customers', CSP_TD),
            );
            $titles              = array(
            array( 'title' => __('Product ID', CSP_TD) ),
            array( 'title' => __('Product Name', CSP_TD) ),
            array( 'title' => $tableOptionTypes[ $option_type ] ),
            array( 'title' => __('Regular Price', CSP_TD) ),
            array( 'title' => __('Sale Price', CSP_TD) ),
            array( 'title' => __('Flat or Discounts', CSP_TD) ),
            array( 'title' => __('Min Qty', CSP_TD) ),
            array( 'title' => __('Value', CSP_TD) ),
            );

            return apply_filters('csp_single_view_table_titles', $titles);
        }

        public function getQueryInput($query_title = '')
        {
            ob_start();
            ?>
    <div class="row form-group">
    <label class="col-md-2 form-control-label"><?php _e('Rule Title', CSP_TD); ?>
                    <span class="wdm-required">*</span>
                    <a class="wdm_wrapper">
                        <img class="help_tip" src="<?php echo plugins_url('/images/help.png', dirname(__FILE__)); ?>" height="20" width="20">
                        <span class='wdm-tooltip-content'>
                            <span class="wdm-tooltip-text">
                                <span class="wdm-tooltip-inner">
            <?php _e('Rule title will help identify the rules generated for Users/Roles/Groups.', CSP_TD); ?>
                                </span>
                            </span>
                        </span>
                    </a>
                </label>
                <div class="col-md-4 form-control-wrap">
                    <input type="text" name="wdm_csp_query_title" id="wdm_csp_query_title" size="80" value="<?php echo $query_title; ?>" class="form-control" />
                    </span>
                    <input type="hidden" name="wdm_csp_query_time" id="wdm_csp_query_time" value="<?php echo get_current_user_id() . '_' . time(); ?>">
                </div>
            </div>
            <input type="button" class="btn btn-primary" id="wdm_csp_save_changes" value="<?php echo isset($_GET[ 'query_log' ]) ? __('Update Rule', CSP_TD) : __('Save Rule', CSP_TD); ?>">

            <div class="progress progress-striped">
                <div class="progress-bar six-sec-ease-in-out" role="progressbar" data-transitiongoal="0"></div>
            </div>
            <p class="csp-log-progress"></p>
            <?php
            $result = ob_get_contents();

            ob_end_clean();

            return $result;
        }

        public function saveQueryLogCallback()
        {
            global $cspFunctions;
            //Allow only admin to get selection
            $capability_required = apply_filters('csp_save_query_log_user_capability', 'manage_options');
            $can_user_save = apply_filters('csp_can_user_save_query_log', current_user_can($capability_required));
            if (!$can_user_save) {
                echo "Security Check";
                exit;
            }
            $wdm_save_result = '';

            $default_values = array(
                'option_type' => '',
                'selection_list' => '',
                'product_values' => '',
                'product_actions' => '',
                'product_quantities' => '',
                'query_title' => '',
                'option_name' => '',
                'current_query_id' => ''
            );

            $wdm_data_array = array_filter(array(
                 'option_type'          => $_POST[ 'option_type' ],
                 'selection_list'       => $_POST[ 'selection_list' ],
                 'product_values'       => $_POST[ 'product_values' ],
                 'product_actions'      => $_POST[ 'product_actions' ],
                 'product_quantities'   => $_POST[ 'product_quantities' ],
                 'query_title'          => $_POST[ 'query_title' ],
                 'option_name'          => $_POST[ 'option_name' ],
                 'current_query_id'     => isset($_POST[ 'current_query_id' ]) ? $_POST[ 'current_query_id' ] : ''
                ));

            $wdm_parsed_values = wp_parse_args($wdm_data_array, $default_values);

            $selection_list = trim($wdm_parsed_values[ 'selection_list' ], ',');

            $option_type = $wdm_parsed_values[ 'option_type' ];

            $values  = array();
            $quantities = array();
            $actions = array();

            parse_str($wdm_parsed_values[ 'product_values' ], $values);
            parse_str($wdm_parsed_values[ 'product_quantities' ], $quantities);
            parse_str($wdm_parsed_values[ 'product_actions' ], $actions);

            if ($option_type === 'customer') {
                $wdm_save_result = $cspFunctions->saveCustomerPricingPair(explode(',', $selection_list), $values, $quantities, $actions, $wdm_parsed_values[ 'query_title' ], $wdm_parsed_values[ 'option_name' ], $wdm_parsed_values[ 'current_query_id' ]);
            } elseif ($option_type === 'role') {
                $wdm_save_result = $cspFunctions->saveRolePricingPair(explode(',', $selection_list), $values, $quantities, $actions, $wdm_parsed_values[ 'query_title' ], $wdm_parsed_values[ 'option_name' ], $wdm_parsed_values[ 'current_query_id' ]);
            } elseif ($option_type === 'group') {
                $wdm_save_result = $cspFunctions->saveGroupPricingPair(explode(',', $selection_list), $values, $quantities, $actions, $wdm_parsed_values[ 'query_title' ], $wdm_parsed_values[ 'option_name' ], $wdm_parsed_values[ 'current_query_id' ]);
            }

            echo $wdm_save_result;

            die();
        }

        public function getProgressStatusCallback()
        {
            //Allow only admin to get selection
            $capability_required = apply_filters('csp_get_progress_status_user_capability', 'manage_options');
            $can_user_get_status = apply_filters('csp_can_user_get_progress_status', current_user_can($capability_required));
            if (!$can_user_get_status) {
                echo "Security Check";
                exit;
            }

            $option_name = isset($_POST[ 'option_name' ]) ? $_POST[ 'option_name' ] : '';
            $result      = array( 'value' => 0, 'status' => '' );

            if (! empty($option_name)) {
                $result[ 'value' ]   = get_option($option_name . '_value', 0);
                $result[ 'status' ]  = get_option($option_name . '_status', '');
            }

            echo json_encode($result);
            die();
        }

        public function displayProductPricesCallback()
        {
            global $cspFunctions;
            //Allow only admin to get selection
            $capability_required = apply_filters('csp_display_product_price_user_capability', 'manage_options');
            $can_user_display = apply_filters('csp_can_user_display_product_price', current_user_can($capability_required));
            if (!$can_user_display) {
                echo "Security Check";
                exit;
            }
            $option_type     = '';
            $selection_name  = '';

            if (isset($_POST[ 'option_type' ])) {
                $option_type = $_POST[ 'option_type' ];
            }

            if (isset($_POST[ 'selection_name' ])) {
                $selection_name = $_POST[ 'selection_name' ];
            }

            $group_plugin_active = false;

            $selection_list  = array();
            $product_list    = array();

            $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));

            if (in_array('groups/groups.php', $active_plugins)) {
                $group_plugin_active = true;
            }

            if ($option_type === 'customer') {
                $user_id         = intval($selection_name);
                $selection_list  = $this->getSelectionCustomer($user_id);
                if (! empty($selection_list)) {
                    $product_list = array_keys($selection_list);
                }
                $selection_list = $selection_list + $this->getSelectionCustomerDirect($user_id, $product_list);
                $selection_list = $cspFunctions->msort($selection_list, 'min_qty');

                $user_info = get_userdata($user_id);

                $product_list    = $product_list + array_keys($selection_list);


                $selection_list  = $selection_list + $this->getSelectionRole($user_info->roles, $product_list);

                $product_list    = $product_list + array_keys($selection_list);
                $selection_list  = $selection_list + $this->getSelectionRoleDirect($user_info->roles, $product_list);
                if ($group_plugin_active) {
                    $product_list = array_keys($selection_list);
                    $groups_user = new \Groups_User(intval($selection_name));
                    
                    // get group ids (user is direct member)
                    $user_group_ids = $groups_user->group_ids;

                    $product_list    = $product_list + array_keys($selection_list);
                    $selection_list  = $selection_list + $this->getSelectionGroup($user_group_ids, $product_list);
                    $product_list    = $product_list + array_keys($selection_list);
                    $selection_list  = $selection_list + $this->getSelectionGroupDirect($user_group_ids, $product_list);
                }
            } elseif ($option_type === 'role') {
                $selection_list = $this->getSelectionRole(array( $selection_name ));
                if (! empty($selection_list)) {
                    $product_list = array_keys($selection_list);
                }
                $selection_list = $selection_list + $this->getSelectionRoleDirect(array( $selection_name ), $product_list);
            } elseif ($option_type === 'group' && $group_plugin_active) {
                $selection_list = $this->getSelectionGroup(array( $selection_name ));
                if (! empty($selection_list)) {
                    $product_list = array_keys($selection_list);
                }

                $selection_list = $selection_list + $this->getSelectionGroupDirect(array( $selection_name ), $product_list);
            }
            //Print selection
            $selection_list = $cspFunctions->msort($selection_list, 'min_qty');

            echo json_encode($this->displaySelections($selection_list));
            die();
        }

        private function displaySelections($selection_list)
        {
            $display_list = array();

            if (! empty($selection_list)) {
                foreach ($selection_list as $id => $selection_detail) {
                    $product_id = $selection_detail['product_id'];
                    if (get_post_type($product_id) == 'product_variation') {
                        $parent_id           = wp_get_post_parent_id($product_id);
                        $product_title       = get_the_title($parent_id);
                        $variable_product    = new \WC_Product_Variation($product_id);
                        $attributes          = $variable_product->get_variation_attributes();

                        //get all attributes name associated witj this variation
                        $attribute_names = array_keys($variable_product->get_attributes());

                        $pos = 0; //Counter for the position of empty attribute
                        foreach ($attributes as $key => $value) {
                            if (empty($value)) {
                                $attributes[$key] = "Any ".$attribute_names[$pos++];
                            }
                        }

                        $product_title .= '-->' . implode(", ", $attributes);
                    } else {
                        $product_title = get_the_title($product_id);
                    }

                    $display_list[] = array( $product_title, $selection_detail[ 'min_qty' ], wc_format_localized_price($selection_detail[ 'price' ]),$selection_detail[ 'price_type' ], $selection_detail[ 'query_id' ], $selection_detail[ 'query_title' ] );
                }
                return $display_list;
            } else {
                return array( 'error' => '<div class="error">' . __('No details saved.', CSP_TD) . '</div>' );
            }
        }

        private function processResult($res, $source)
        {
            global $wpdb, $ruleManager;

            $result = array();
            foreach ($res as $key) {
                $prod_name  = $wpdb->get_results(
                    $wpdb->prepare(
                        "select post_title FROM " . $wpdb->prefix . "posts where ID = %d",
                        $key[ 'product_id' ]
                    ),
                    ARRAY_A
                );

                $result[ $key[ 'product_id' ]."_".$key[ 'min_qty' ] ][ 'product_id' ]  = $key[ 'product_id' ];
                $result[ $key[ 'product_id' ]."_".$key[ 'min_qty' ] ][ 'product_name' ]    = $prod_name[ 0 ][ 'post_title' ];

                if ($source == 'rule') {
                    $rule_title = $ruleManager->getRuleTitle($key[ 'rule_id' ]);
                    $result[ $key[ 'product_id' ]."_".$key[ 'min_qty' ] ][ 'query_title' ]     = $rule_title[ 'rule_title' ];
                    $result[ $key[ 'product_id' ]."_".$key[ 'min_qty' ] ][ 'query_id' ]        = $key[ 'rule_id' ];
                } else {
                    $result[ $key[ 'product_id' ]."_".$key[ 'min_qty' ] ][ 'query_title' ]     = $key[ 'source' ];
                    $result[ $key[ 'product_id' ]."_".$key[ 'min_qty' ] ][ 'query_id' ]        = "--";
                }

                $result[ $key[ 'product_id' ]."_".$key[ 'min_qty' ] ][ 'price_type' ]  = $key[ 'price_type' ];
                $result[ $key[ 'product_id' ]."_".$key[ 'min_qty' ] ][ 'min_qty' ]  = $key[ 'min_qty' ];
                if ($key['price_type'] == 2) {
                    $regular_price = floatval(get_post_meta($key[ 'product_id' ], '_regular_price', true));
                    if ($regular_price >= 0) {
                        $result[ $key[ 'product_id' ]."_".$key[ 'min_qty' ] ][ 'price' ] = $regular_price - ( ($key[ 'price' ] / 100) * $regular_price);
                    }
                } else {
                    $result[ $key[ 'product_id' ]."_".$key[ 'min_qty' ] ][ 'price' ]       = $key[ 'price' ];
                }
            }
            return $result;
        }

        private function getProductIds($productsQuantity)
        {
            $productIdQty = array();
            foreach ($productsQuantity as $key => $value) {
                $tempArray = explode('_', $value);
                $productIdQty[] = $tempArray[0];
            }

            return $productIdQty;
        }

        //SELECT product_id, price, 'direct' as 'source'  FROM wp_wusp_user_pricing_mapping WHERE user_id = 23 AND product_id NOT IN ($1product_ids)
        private function getSelectionCustomerDirect($user_id, $product_exclude = array())
        {
            global $wpdb, $getCatRecords, $cspFunctions;

            $source = __('Direct', CSP_TD);
            $res = $wpdb->get_results($wpdb->prepare("SELECT product_id, price, flat_or_discount_price as price_type, min_qty, '$source' as 'source' FROM " . $wpdb->prefix . "wusp_user_pricing_mapping WHERE user_id = %d order by product_id", $user_id), ARRAY_A);

            $catPrice = $getCatRecords->getAllProductPricesByUser($user_id);

            $mergedPrices = $cspFunctions->mergeProductCatPriceSearch($res, $catPrice);
            if (empty($mergedPrices)) {
                return array();
            }

            foreach ($mergedPrices as $key => $singleResult) {
                if (in_array($singleResult['product_id'] . '_' . $singleResult['min_qty'], $product_exclude)) {
                    unset($mergedPrices[$key]);
                }
            }

            $resultDirectCustomer = $this->processResult($mergedPrices, 'direct');

            if (! empty($resultDirectCustomer)) {
                return $this->processSelectionResult($resultDirectCustomer);
            }

            return array();
        }


// getSelectionCustomerDirect ends

        private function getSelectionRoleDirect($role_list, $product_exclude = array())
        {
            global $wpdb, $getCatRecords, $cspFunctions;

            $source = __('Direct', CSP_TD);
            $res = $wpdb->get_results("SELECT product_id, price, flat_or_discount_price as price_type, min_qty, '$source' as 'source' FROM " . $wpdb->prefix . "wusp_role_pricing_mapping WHERE role IN ('" . implode("','", $role_list) . "') order by product_id", ARRAY_A);
// getAllProductPricesByRoles
            $catPrice = $getCatRecords->getAllProductPricesByRoles($role_list);
            $mergedPrices = $cspFunctions->mergeProductCatPriceSearch($res, $catPrice);

            if ($mergedPrices == null) {
                return array();
            }


            foreach ($mergedPrices as $key => $singleResult) {
                if (in_array($singleResult['product_id'] . '_' . $singleResult['min_qty'], $product_exclude)) {
                    unset($mergedPrices[$key]);
                }
            }

            $resultRoleDirect = $this->processResult($mergedPrices, 'direct');

            if (! empty($resultRoleDirect)) {
                return $this->processSelectionResult($resultRoleDirect);
            }

            return array();
        }

// getSelectionRoleDirect
// 
        private function getSelectionGroupDirect($group_ids, $product_exclude = array())
        {
            global $wpdb, $getCatRecords, $cspFunctions;

            $source = __('Direct', CSP_TD);
            $res = $wpdb->get_results("SELECT product_id, price, flat_or_discount_price as price_type, min_qty, '$source' as 'source' FROM " . $wpdb->prefix . "wusp_group_product_price_mapping WHERE group_id IN (" . implode(',', $group_ids) . ") order by product_id", ARRAY_A);

            $catPrice = $getCatRecords->getAllProductPricesByGroups($group_ids);
            $mergedPrices = $cspFunctions->mergeProductCatPriceSearch($res, $catPrice);

            if ($mergedPrices == null) {
                return array();
            }

            foreach ($mergedPrices as $key => $singleResult) {
                if (in_array($singleResult['product_id'] . '_' . $singleResult['min_qty'], $product_exclude)) {
                    unset($mergedPrices[$key]);
                }
            }


            $resultGroupDirect = $this->processResult($mergedPrices, 'direct');

            if (! empty($resultGroupDirect)) {
                return $this->processSelectionResult($resultGroupDirect);
            }

            return array();
        }

        private function getSelectionCustomer($user_id)
        {
            global $subruleManager;

            $product_details = array();

            $res = $subruleManager->getAllActiveSubrulesInfoForUserRules($user_id);

            if ($res == null) {
                return array();
            }

            $resultCustomer = $this->processResult($res, 'rule');
            if (! empty($resultCustomer)) {
                return $this->processSelectionResult($resultCustomer);
            }

            return $product_details;
        }

//getSelectionCustomer ends

        private function getSelectionRole($role_list, $product_exclude = array())
        {
            global $subruleManager;
            $product_details = array();
            $res             = $subruleManager->getAllActiveSubrulesInfoForRolesRule($role_list);
                    
            if ($res == null) {
                return array();
            }

            foreach ($res as $key => $singleResult) {
                if (in_array($singleResult['product_id'] . '_' . $singleResult['min_qty'], $product_exclude)) {
                    unset($res[$key]);
                }
            }

            $resultRole = $this->processResult($res, 'rule');

            if (! empty($resultRole)) {
                return $this->processSelectionResult($resultRole);
            }
            return $product_details;
        }

        // getSelectionRole ends

        private function getSelectionGroup($user_group_ids, $product_exclude = array())
        {
            global $subruleManager;
            $product_details = array();
            $res             = $subruleManager->getAllActiveSubrulesInfoForGroupsRule($user_group_ids);

            if ($res == null) {
                return array();
            }

            foreach ($res as $key => $singleResult) {
                if (in_array($singleResult['product_id'] . '_' . $singleResult['min_qty'], $product_exclude)) {
                    unset($res[$key]);
                }
            }

            $resultGroup = $this->processResult($res, 'rule');

            if (! empty($resultGroup)) {
                return $this->processSelectionResult($resultGroup);
            }
            return $product_details;
        }


        private function processSelectionResult($result)
        {
            $product_details = array();

            foreach ($result as $product) {
                if (! in_array($product[ 'product_id' ]."_".$product[ 'min_qty' ], $product_details)) {
                    if (is_null($product[ 'query_id' ])) {
                        $product[ 'query_id' ] = '--';
                    }

                    if (is_null($product[ 'query_title' ])) {
                        $product[ 'query_title' ] = __('Direct', CSP_TD);
                    }
                    if ($product[ 'price_type' ] == 1) {
                        $product[ 'price_type' ] = __('Flat', CSP_TD);
                    } elseif ($product[ 'price_type' ] == 2) {
                        $product[ 'price_type' ] = '%';
                    }

                    $product_details[ $product[ 'product_id' ]."_".$product[ 'min_qty' ] ] = array( 'product_id'   => $product[ 'product_id' ],
                        'product_name'  => $product[ 'product_name' ],
                        'price'         => $product[ 'price' ],
                        'price_type'    => $product[ 'price_type' ],
                        'min_qty'       => $product[ 'min_qty' ],
                        'query_id'      => $product[ 'query_id' ],
                        'query_title'   => $product[ 'query_title' ] );
                }
            }
            return $product_details;
        }

        public function removeQueryLogCallback()
        {
            global $ruleManager;
            //Allow Admin access
            $capability_required = apply_filters('csp_remove_query_log_user_capability', 'manage_options');
            $can_user_remove = apply_filters('csp_can_user_remove_query_log', current_user_can($capability_required));
            if (!$can_user_remove) {
                echo "Security Check";
                exit;
            }
            $query_log_ids = $_POST[ 'query_log_id' ];

            if (! empty($query_log_ids)) {
                foreach ($query_log_ids as $single_qlog_id) {
                    $ruleManager->deleteRule($single_qlog_id);
                }

                echo '<div class="updated wdm-qlog-notification settings-error notice is-dismissible"><p>' . __('Rule Deleted.', CSP_TD) . '</p></div>';
            } else {
                echo '<div class="error wdm-qlog-notification"><p>' . __('Please select some Log', CSP_TD) . '</p></div>';
            }

            die();
        }

//function ends -- removeQueryLogCallback
    }
}