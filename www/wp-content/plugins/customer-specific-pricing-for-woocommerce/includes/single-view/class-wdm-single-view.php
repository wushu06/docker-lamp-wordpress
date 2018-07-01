<?php

namespace cspSingleView;

if (!class_exists('WdmSingleView')) {
    class WdmSingleView
    {

        private $csp_single_view_menu_slug = 'customer_specific_pricing_single_view';
        private $general_settings_key = 'product_pricing';
        private $search_settings_key = 'search_settings';
        private $query_log_setting_key = 'rule_log';
        private $csp_settings_tabs = array();

        public function __construct()
        {
            
            add_action('init', array($this, 'wdmDefineTabs'), 99);
            add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
            //Include files for General / Search settings
            include_once('settings/general.php');
            new general\WdmSingleViewGeneral();

            include_once('settings/search_setting.php');
            new searchSetting\WdmSingleViewSearch();

            include_once('settings/query_log_setting.php');
            new queryLogSetting\WdmSingleViewQueryLog();
        }

        public function enqueueScripts()
        {
            wp_enqueue_script('wdm_csp_functions', plugins_url('/js/wdm-csp-function.js', dirname(dirname(__FILE__))), array( 'jquery' ), CSP_VERSION);
            wp_localize_script(
                'wdm_csp_functions',
                'wdm_csp_function_object',
                array(
                'decimal_separator' => wc_get_price_decimal_separator(),
                'thousand_separator' => wc_get_price_thousand_separator(),
                'decimals' => wc_get_price_decimals(),
                'price_format' => get_woocommerce_price_format(),
                'currency_symbol' => get_woocommerce_currency_symbol(),
                )
            );
        }

        public function wdmDefineTabs()
        {
            $this->csp_settings_tabs[$this->general_settings_key] = __('Set Rules', CSP_TD);
            $this->csp_settings_tabs[$this->query_log_setting_key] = __('Rule Log', CSP_TD);
        }

        public function cspSingleView()
        {
            $tab = isset($_GET['tab']) ? $_GET['tab'] : $this->general_settings_key;
            ?>
            <div class="wrap">
            <h3 class = 'import-export-header'><?php _e('Product Pricing', CSP_TD) ?></h3>
            <?php $this->getOptionsTab(); ?>
            <?php do_action('csp_single_view_' . $tab); ?>
            </div>
            <?php
        }

    /*        private function getOptionsTab()
        {
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : $this->general_settings_key;

        screen_icon();
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($this->csp_settings_tabs as $tab_key => $tab_caption) {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->csp_single_view_menu_slug . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
        }
        echo '</h2>';
        }
    */
        private function getOptionsTab()
        {
            $current_tab = isset($_GET['tab']) ? $_GET['tab'] : $this->general_settings_key;

            ?>
            <div>
            <ul class="subsubsub hrline">
                <?php

                foreach ($this->csp_settings_tabs as $tab_key => $tab_caption) {
                    $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
                    if ($tab_key == 'rule_log') {
                        echo '<li><a class="' . $active . '" href="?page=' . $this->csp_single_view_menu_slug . '&tab=' . $tab_key . '">' . $tab_caption . '</a></li>';
                    } else {
                        echo '<li><a class="' . $active . '" href="?page=' . $this->csp_single_view_menu_slug . '&tab=' . $tab_key . '">' . $tab_caption . '</a> |</li>';
                    }
                }
                ?>
            </ul>
            </div>
            <hr/>
            <?php
        }
    }
}
