<?php 

namespace Inc\Api\Callbacks; 

use \Inc\Base\BaseController;

use \Inc\Api\SettingsApi;


class AdminCallbacks extends BaseController {

    function hmu_plugin () {
        
        echo require_once( "$this->plugin_path/template/dashboard.php" );
        
    
    }
    function hmu_prices_page () {
        require_once $this->plugin_path.'template/import-prices-users.php';
    }
    function hmu_users_page () {
        require_once $this->plugin_path.'template/import-users.php';
    }
    function hookeMeUp_new_products_page () {
        require_once $this->plugin_path.'template/product-page.php';
	}
    
    function hmu_cron_page () {
        require_once $this->plugin_path.'template/cron-page.php';        
    }


}