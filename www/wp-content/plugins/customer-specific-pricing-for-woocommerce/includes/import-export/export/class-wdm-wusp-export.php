<?php

namespace cspImportExport\cspExport;

/**
 * Display the export option for customer specific,role specific and group specific pricing
 * @author WisdmLabs
 */

if (!class_exists('WdmWuspExport')) {
    class WdmWuspExport
    {

        private $_class_value_pairs = array();

        /**
         * call the function for display export option and create csv file
         */
        public function __construct()
        {
            add_action('show_export', array($this, 'wdmShowExportOptions'));
        }

        /**
         * store class value pairs for display in dropdown
         * @param array $class_value_pairs
         */
        public function setOptionValuesPair($class_value_pairs)
        {
            $this->_class_value_pairs = $class_value_pairs;
        }

        /**
         * display export form
         */
        public function wdmShowExportOptions()
        {
            $array_to_be_send = array(
                    'ajaxurl'       =>  admin_url('admin-ajax.php'),
                    'please_Assign_valid_user_file_msg' => __('Please Assign User Specific Prices to export the CSV file successfully.', CSP_TD),
                    'please_Assign_valid_role_file_msg' => __('Please Assign Role Specific Prices to export the CSV file successfully.', CSP_TD),
                    'please_Assign_valid_group_file_msg' => __('Please Assign Group Specific Prices to export the CSV file successfully.', CSP_TD),
                    'export_nonce'      => wp_create_nonce('export_nonce'),
                );
            wp_enqueue_style('wdm_csp_export_css', plugins_url('/css/export-css/wdm-csp-export.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
            wp_enqueue_script('wdm_csp_export_js', plugins_url('/js/export-js/wdm-csp-export.js', dirname(dirname(dirname(__FILE__)))), array('jquery'), CSP_VERSION, true);
            wp_localize_script('wdm_csp_export_js', 'wdm_csp_export_ajax', $array_to_be_send);
            ?>
            <div class="wrap">
                <h3 class="import-export-header"> <?php _e('CSP Export', CSP_TD) ?> </h3>
             </div>
            <div id="wdm_message" class="below-h2" style="display: block;"><p class="wdm_message_p"></p></div>
            <form name="export_form" class="wdm_export_form" method="POST">
                <table cellspacing="10px">
                    <tr><td>
                            <label for="dd_show_export_options"><?php _e('Select Export Type :', CSP_TD) ?> </label>
                            <select name="dd_show_export_options" id="dd_show_export_options">
                                <?php
                                foreach ($this->_class_value_pairs as $key => $val) {
                                    echo '<option value=' . $key . '>' . $val . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="submit" value="<?php _e('Download Export File', CSP_TD) ?>" id="export" name="export" class="button button-primary">
                        </td></tr>
                </table>
            </form>
            <?php
        }
    }

}

/**
 * Include all files required for Export
 */
include_once('process-export/class-wdm-wusp-group-specific-pricing-export.php');
include_once('process-export/class-wdm-wusp-role-specific-pricing-export.php');
include_once('process-export/class-wdm-wusp-user-specific-pricing-export.php');