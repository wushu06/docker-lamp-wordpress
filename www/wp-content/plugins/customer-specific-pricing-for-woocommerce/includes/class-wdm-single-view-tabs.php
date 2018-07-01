<?php

namespace SingleView;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WdmShowTabs')) {
    class WdmShowTabs
    {
         /**
         * [__construct Adds the Menu Page action]
         */
        public function __construct()
        {
            global $singleView, $categoryPricing;
            global $importExport;

            add_action('admin_init', array($this, 'loadUploadWdmCsp'));
            add_action('admin_menu', array($this, 'cspPageInit'), 99);
            add_action('admin_notices', array($this, 'displayAdminNotices'));

            if (is_admin()) {
                include_once('single-view/class-wdm-single-view.php');
                $singleView = new \cspSingleView\WdmSingleView();
                
                //including file Import/Export functionality
                include_once('import-export/class-wdm-wusp-import-export.php');
                $importExport = new \cspImportExport\WdmWuspImportExport();

                include_once('category-pricing/class-wdm-wusp-category-pricing.php');
                $categoryPricing = new \cspCategoryPricing\WdmWuspCategoryPricing();
            }
        }

        public function loadUploadWdmCsp()
        {
            $currentTab = $this->getCurrentTab();

            if ($currentTab == 'import') {
                wp_enqueue_script(
                    'wdm_csp_import_js',
                    plugins_url('/js/import-js/wdm-csp-import.js', dirname(__FILE__)),
                    array('jquery'),
                    CSP_VERSION
                );

                wp_localize_script(
                    'wdm_csp_import_js',
                    'wdm_csp_import',
                    array(
                        'admin_ajax_path'     => admin_url('admin-ajax.php'),
                        'import_nonce'        => wp_create_nonce('import_nonce'),
                        'header_text'         => __('CSP Import', CSP_TD),
                        'loading_image_path'  => plugins_url('/images/loading .gif', dirname(__FILE__)),
                        'loading_text'        => __('Importing . . .', CSP_TD),
                        'import_successfull'  => __('File Imported Successfully', CSP_TD),
                        'total_no_of_rows'    => __('Total number of rows found ', CSP_TD),
                        'total_insertion'     => __('. Total number of rows inserted ', CSP_TD),
                        'total_updated'       => __(', total number of rows updated ', CSP_TD),
                        'total_skkiped'       => __(', and total number of rows skipped ', CSP_TD),
                        'import_page_url'     => menu_page_url('customer_specific_pricing_single_view', false)."&tabie=import",
                        'templates_url'       => plugins_url('/templates/', dirname(__FILE__)),
                        'user_specific_sample'  => __('User Specific Sample', CSP_TD),
                        'role_specific_sample'  => __('Role Specific Sample', CSP_TD),
                        'group_specific_sample'  => __('Group Specific Sample', CSP_TD),
                    )
                );
            }
        }

        /**
         * [cspPageInit Function To add menu page and sub menu page for csp]
         * @return [void]
         */
        public function cspPageInit()
        {
            global $singleViewPage, $wdmWuspPluginData;

            $getDataFromDb = \WdmCSP\WdmGetLicenseData::getDataFromDb($wdmWuspPluginData, false);

            
            if ($getDataFromDb != 'available') {
                return;
            }

            $singleViewPage = add_menu_page('CSP Administration', 'CSP', 'manage_options', 'customer_specific_pricing_single_view', array(
                $this,
                'singleViewTabs'
            ));
        }

        /**
         * [importExportTabs Function to show The Import/Export tabs
         *  of import_export sub menu Page under the CSP menu page]
         * @return [void]
         */
        public function singleViewShowTabs($current = 'import')
        {
            $tabs = array(
                'import' => __('Import', CSP_TD),
                'export' => __('Export', CSP_TD),
                'product_pricing' => __('Product Pricing', CSP_TD),
                'category_pricing' => __('Category Pricing', CSP_TD),
                'search_by' => __('Search By', CSP_TD),
            );
            ?>
            <h2 class="nav-tab-wrapper">
            <?php
            foreach ($tabs as $tab => $name) {
                // echo $name;
                $class = ( $tab == $current ) ? ' nav-tab-active' : '';
                echo "<a class='nav-tab$class' href='admin.php?page=customer_specific_pricing_single_view&tabie=$tab'>".
                $name."</a>";
            }
            ?>
            </h2>
            <?php
        }

        public function displayAdminNotices()
        {
            $currentTab = $this->getCurrentTab();

            if ($currentTab == 'import') {
                \cspImportExport\cspImport\WdmWuspImport::showImportNotices();
            }
        }

        public function getCurrentTab()
        {

            global $pagenow;
            static $currentTab = null;

            if ($currentTab !== null) {
                return $currentTab;
            }

            if ($pagenow == 'admin.php' && $_GET['page'] == 'customer_specific_pricing_single_view') {
                if (isset($_GET['tabie'])) {
                    $currentTab = $_GET['tabie'];
                    return $currentTab;
                }

                if (isset($_GET['tab'])) {
                    $currentTab = 'product_pricing';
                    return $currentTab;
                }

                $currentTab = 'import';
                return $currentTab;
            }

            $currentTab = false;
            return $currentTab;
        }
        /**
         * [importExportTabs Function to navigate through Import/Export tabs in the import/export CSP sub menu page]
         * @return [void]
         */
        public function singleViewTabs()
        {
            global $pagenow, $singleView, $importExport, $categoryPricing;

            $currentTab = $this->getCurrentTab();
            
            if ($currentTab === false) {
                return;
            }

            ?>
            <div class="wrap">
                <?php
                    $this->singleViewShowTabs($currentTab);
                ?>
                <div id="poststuffIE">
                <?php
                switch ($currentTab) {
                    case 'import':
                        $importExport->cspImport();
                        break;
                    case 'export':
                        $importExport->cspExport();
                        break;
                    case 'product_pricing':
                        $singleView->cspSingleView();
                        break;
                    case 'category_pricing':
                        $categoryPricing->cspShowCategoryPricing();
                        break;
                    case 'search_by':
                        do_action('csp_single_view_search_settings');
                        break;
                }//end of switch
                ?>
                </div>
            </div>
        <?php
        } // end of function importExportTabs
    } //end of class
} //end of if class exists
