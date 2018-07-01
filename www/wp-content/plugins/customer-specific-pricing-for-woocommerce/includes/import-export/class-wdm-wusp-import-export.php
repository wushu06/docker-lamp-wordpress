<?php

namespace cspImportExport;

if (!class_exists('WdmWuspImportExport')) {
    class WdmWuspImportExport
    {
        public function __construct()
        {
            //Include files for Import
            include_once('import/class-wdm-wusp-import.php');

            //Include files for Export
            include_once('export/class-wdm-wusp-export.php');
        }

        public function loadUploadWdmCsp()
        {
            wp_enqueue_script('wdm_csp_upload_js', plugins_url('/js/import-js/wdm-csp-upload.js', dirname(dirname(dirname(__FILE__)))), array('jquery'), CSP_VERSION);

            wp_localize_script(
                'wdm_csp_upload_js',
                'wdm_csp_upload',
                array(
                    'admin_ajax_path'     => admin_url('admin-ajax.php'),
                    'import_nonce'        => wp_create_nonce('import_nonce'),
                    'loading_image_path'  => plugins_url('/images/loading .gif', dirname(dirname(dirname(__FILE__)))),
                )
            );
        }


        public function cspExport()
        {
            ?>
            <div class="wrap">
                <?php
                    $wdm_export = new cspExport\WdmWuspExport();

                    $exportDropdown = array(
                        'User' => __('User Specific Pricing', CSP_TD),
                        'Role' => __('Role Specific Pricing', CSP_TD),
                        'Group' => __('Group Specific Pricing', CSP_TD),
                    );

                    $activePluginsArray = apply_filters('active_plugins', get_option('active_plugins'));

                if (!in_array('groups/groups.php', $activePluginsArray)) {
                    unset($exportDropdown['Group']);
                }

                    //Below values are shown in the dropdown of Export page
                    $wdm_export->setOptionValuesPair($exportDropdown);
                    
                    do_action('show_export');
                ?>
            </div>
            <?php
        }
        
        public function cspImport()
        {
            ?>
            <div class="wrap">
                <?php
                    $wdm_import = new cspImport\WdmWuspImport();

                    $importDropdown = array(
                        'Wdm_User_Specific_Pricing_Import' => __('User Specific Pricing', CSP_TD),
                        'Wdm_Role_Specific_Pricing_Import' => __('Role Specific Pricing', CSP_TD),
                        'Wdm_Group_Specific_Pricing_Import' => __('Group Specific Pricing', CSP_TD),
                    );

                    $activePluginsArray = apply_filters('active_plugins', get_option('active_plugins'));

                if (!in_array('groups/groups.php', $activePluginsArray)) {
                    unset($importDropdown['Wdm_Group_Specific_Pricing_Import']);
                }

                    //Below values are shown in the dropdown of Import page
                    $wdm_import->setOptionValuesPair($importDropdown);

                    do_action('show_import');
                ?>
            </div>
            <?php
        }
    }
}
