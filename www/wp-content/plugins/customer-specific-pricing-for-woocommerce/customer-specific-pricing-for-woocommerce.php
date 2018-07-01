<?php
/**
 * Plugin Name: Customer Specific Pricing for WooCommerce
 * Plugin URI: https://wisdmlabs.com/woocommerce-user-specific-pricing-extension/
 * Description: Allows administrator to add customer specific pricing, role specific & group specific pricing for Simple & Variable WooCommerce Products.
 * Author: WisdmLabs
 * Version: 4.1.0
 * Text Domain: customer-specific-pricing-for-woocommerce
 * Author URI: https://wisdmlabs.com
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * WC requires at least: 2.6.14
 * WC tested up to: 3.2.0
 */

/*
  Copyright (C) 2015  WisdmLabs

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if (!defined('ABSPATH')) {
    exit;
}// Exit if accessed directly

// Define plugin constants
// Constant for text domain
define('CSP_TD', 'customer-specific-pricing-for-woocommerce');
//constant 
define('CSP_VERSION', '4.1.0');
// 
define('CSP_PLUGIN_URL', untrailingslashit(plugin_dir_path(__FILE__)));

add_action('plugins_loaded', 'cspTextInitFunc');

function cspTextInitFunc()
{
    load_plugin_textdomain(CSP_TD, false, dirname(plugin_basename(__FILE__)).'/languages/');
}

$wdmWuspPluginData = array(
    'pluginShortName' => 'CSP',
    'pluginSlug' => 'csp',
    'pluginVersion' => CSP_VERSION,
    'pluginName' => 'Customer Specific Pricing for WooCommerce',
    'storeUrl' => 'https://wisdmlabs.com/check-update',
    'authorName' => 'WisdmLabs',
    'pluginTextDomain'  =>  CSP_TD
);

$php_version = '';

if (function_exists('phpversion')) {
    $php_version = phpversion();
} elseif (defined(PHP_VERSION)) {
    $php_version = PHP_VERSION;
}

// Including classes for handling pricing manager database tables
include_once(CSP_PLUGIN_URL.'/includes/single-view/class-wdm-subrule-management.php');
include_once(CSP_PLUGIN_URL.'/includes/single-view/class-wdm-rule-management.php');

if (version_compare($php_version, '5.3.0') >= 0) {
    include(CSP_PLUGIN_URL.'/includes/class-wdm-wusp-install.php');

    //Install Tables associated with User Specific Pricing plugin
    register_activation_hook(__FILE__, array('WdmWuspInstall\WdmWuspInstall', 'createTables'));

    if (!class_exists('\WdmPluginUpdater')) {
        include(CSP_PLUGIN_URL.'/includes/class-wdm-plugin-updater.php');
    }

    // setup the updater
    new WdmCSP\WdmPluginUpdater($wdmWuspPluginData['storeUrl'], __FILE__, array(
        'version' => $wdmWuspPluginData['pluginVersion'], // current version number
        'license' => trim(get_option('edd_' . $wdmWuspPluginData['pluginSlug'] . '_license_key')), // license key (used get_option above to retrieve from DB)
        'item_name' => $wdmWuspPluginData['pluginName'], // name of this plugin
        'author' => $wdmWuspPluginData['authorName'], //author of the plugin
    ));


    // Update tables for CSP if plugin is updated
    function csp_update_check()
    {

        global $wdmWuspPluginData;

        $get_plugin_version = get_option($wdmWuspPluginData['pluginSlug'] . '_version', false);

        if (false === $get_plugin_version || $get_plugin_version != $wdmWuspPluginData['pluginVersion']) {
             \WdmWuspInstall\WdmWuspInstall::createTables();

             update_option($wdmWuspPluginData['pluginSlug'] . '_version', $wdmWuspPluginData['pluginVersion']);
        }
    }

    /*
     * Check for Plugin updatation
     * @since 2.2
     */
    add_action('plugins_loaded', 'csp_update_check');

    /**
     * Check if WooCommerce is active
     */
    $arrayOfActivatedPlugins = apply_filters('active_plugins', get_option('active_plugins'));
    if (in_array('woocommerce/woocommerce.php', $arrayOfActivatedPlugins)) {
        include_once(CSP_PLUGIN_URL.'/includes/class-wdm-add-license-data.php');
        include_once(CSP_PLUGIN_URL.'/includes/class-wdm-wusp-add-data-in-db.php');
        include_once(CSP_PLUGIN_URL.'/includes/class-wdm-get-license-data.php');
        include_once(CSP_PLUGIN_URL.'/includes/class-wdm-wusp-delete-data.php');
        include_once(CSP_PLUGIN_URL.'/includes/class-wdm-wusp-update-data.php');

        new WdmCSP\WdmAddLicenseData($wdmWuspPluginData);

        new WdmCSP\WdmWuspAddDataInDB();
        new WdmCSP\WdmWuspUpdateDataInDB();

        

        //deleting records from wp_wusp_user_pricing_mapping tables if user doesn't exists(i.e if the user is deleted later.)
        add_action('delete_user', 'deletePricingPairsForUser');
        add_action('delete_post', 'deletePricingPairsForProduct');
        add_action('groups_deleted_group', 'deletePricingPairsForGroups', 10, 1);
        add_action('delete_term', 'deletePricingPairsForCategory', 10, 4);
        add_action('init', 'cspBootstrap');
        // if ('available' == $getDataFromDb) {
            
        // }
    } //if ends -- check for woocommerce activation
    else {
        add_action('admin_notices', 'cspBasePluginInactiveNotice');
    }
} //if ends -- PHP version check ends
else {
    add_action('admin_notices', 'cspPHPVersionNotice');
}


function cspBootstrap()
{
    global $wdmWuspPluginData;

    $getDataFromDb = WdmCSP\WdmGetLicenseData::getDataFromDb($wdmWuspPluginData, false);

    if ($getDataFromDb != 'available') {
        return;
    }

    if (is_user_logged_in()) {
        //including file for the working of Customer Specific Pricing on Simple Products
        include_once(CSP_PLUGIN_URL.'/includes/user-specific-pricing/class-wdm-wusp-simple-products-usp.php');
        new WuspSimpleProduct\WdmWuspSimpleProductsUsp();

        //including file for the working of Customer Specific Pricing on Variable Products
        include_once(CSP_PLUGIN_URL.'/includes/user-specific-pricing/class-wdm-wusp-variable-products-usp.php');
        new WuspVariableProduct\WdmWuspVariableProductsUsp();

        //including file for the working of Group Based Pricing on Simple Products
        include_once(CSP_PLUGIN_URL.'/includes/group-specific-pricing/class-wdm-wusp-simple-products-gsp.php');
        new WuspSimpleProduct\WgspSimpleProduct\WdmWuspSimpleProductsGsp();

        //including file for the working of Group Based Pricing on Variable Products
        include_once(CSP_PLUGIN_URL.'/includes/group-specific-pricing/class-wdm-wusp-variable-products-gsp.php');
        new WuspVariableProduct\WgspVariableProduct\WdmWuspVariableProductsGsp();

        //including file for the working of Role Based Pricing on Simple Products
        include_once(CSP_PLUGIN_URL.'/includes/role-specific-pricing/class-wdm-wusp-simple-products-rsp.php');
        new WuspSimpleProduct\WrspSimpleProduct\WdmWuspSimpleProductsRsp();

        //including file for the working of Role Based Pricing on Variable Products
        include_once(CSP_PLUGIN_URL.'/includes/role-specific-pricing/class-wdm-wusp-variable-products-rsp.php');
        new WuspVariableProduct\WrspVariableProduct\WdmWuspVariableProductsRsp();

        //including file for the working of Customer Specific Price on Products
        include_once(CSP_PLUGIN_URL.'/includes/class-wdm-apply-usp-product-price.php');
        new WuspSimpleProduct\WuspCSPProductPrice();

        //csp for order creation from backend
        include_once(CSP_PLUGIN_URL.'/includes/dashboard-orders/class-wdm-customer-specific-pricing-new-order.php');
        new cspNewOrder\WdmCustomerSpecificPricingNewOrder();

        include_once(CSP_PLUGIN_URL.'/includes/class-wdm-single-view-tabs.php');
        new SingleView\WdmShowTabs();

        include_once(CSP_PLUGIN_URL.'/includes/class-wdm-wusp-ajax.php');
        new cspAjax\WdmWuspAjax();

        include_once(CSP_PLUGIN_URL.'/includes/class-wdm-wusp-functions.php');
    }
}

if (!function_exists('cspPHPVersionNotice')) {
    function cspPHPVersionNotice()
    {
        if (current_user_can('activate_plugins')) :
            global $wdmWuspPluginData, $php_version;
        ?>
        <div id="message" class="error">
            <p><?php printf(__('%s %s is inactive.%s requires PHP version 5.3 or greater', CSP_TD), '<strong>', $wdmWuspPluginData['pluginName'], '</strong>'); ?>

            <?php if (!empty($php_version)) {
                printf(__(' ( Current PHP version is %s %s %s)', CSP_TD), '<strong>', $php_version, '</strong>');
}
            ?>
            </p>
        </div>
        <?php
        endif;
    }
}

if (!function_exists('cspBasePluginInactiveNotice')) {
    function cspBasePluginInactiveNotice()
    {
        if (current_user_can('activate_plugins')) :
            global $wdmWuspPluginData;

        ?>
        <div id="message" class="error">
            <p><?php printf(__('%s %s is inactive.%s Install and activate %sWooCommerce%s for %s to work.', CSP_TD), '<strong>', $wdmWuspPluginData['pluginName'], '</strong>', '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', $wdmWuspPluginData['pluginName']); ?></p>
        </div>
    <?php
        endif;
    }

}//if ends -- function exists

//Function to delete records from wp_wusp_user_pricing_mapping tables if the user is deleted from.
if (!function_exists('deletePricingPairsForUser')) {
    function deletePricingPairsForUser($userId)
    {
        \WuspDeleteData\WdmWuspDeleteData::deleteCustomerMappingForUsers($userId);
    }
}//if ends -- function exists

if (!function_exists('deletePricingPairsForProduct')) {
    function deletePricingPairsForProduct($productId)
    {
        \WuspDeleteData\WdmWuspDeleteData::deleteMappingForProducts($productId);
    }
}//if ends -- function exists

if (!function_exists('deletePricingPairsForGroups')) {
    function deletePricingPairsForGroups($groupId)
    {
        \WuspDeleteData\WdmWuspDeleteData::deleteMappingForGroups($groupId);
    }
}//if ends -- function exists

if (!function_exists('deletePricingPairsForCategory')) {
    function deletePricingPairsForCategory($term, $tt_id, $deleted_term, $object_ids)
    {
        global $getCatRecords, $deleteCatRecords;

        if (empty($object_ids)) {
            return;
        }

        if ($getCatRecords->isUserCatPresent($object_ids->slug)) {
            $deleteCatRecords->deleteUserCatEntries($object_ids->slug);
        }

        if ($getCatRecords->isRoleCatPresent($object_ids->slug)) {
            $deleteCatRecords->deleteRoleCatEntries($object_ids->slug);
        }

        if ($getCatRecords->isGroupCatPresent($object_ids->slug)) {
            $deleteCatRecords->deleteGroupCatEntries($object_ids->slug);
        }
        // \WuspDeleteData\WdmWuspDeleteData::deleteMappingForGroups($groupId);
    }
}//if ends -- function exists

function cspPrintDebug($data)
{
    echo '<pre>' . print_r($data, true) . '</pre>';
}

function addBreakpoint()
{
    $fileinfo = 'no_file_info';
    $backtrace = debug_backtrace();
    if (!empty($backtrace[0]) && is_array($backtrace[0])) {
        $fileinfo = $backtrace[0]['file'] . ":" . $backtrace[0]['line'];
    }
    echo "Calling file info: $fileinfo\n";
    exit;
}
