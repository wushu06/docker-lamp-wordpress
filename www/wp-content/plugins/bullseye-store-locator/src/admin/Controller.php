<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      0.0.1
 */

namespace BullseyeLocations\admin;

use BullseyeLocations\options\Account as AccountOption;
use BullseyeLocations\options\Interfaces as InterfaceOption;
use BullseyeLocations\api\RestAdmin as RestAdminAPI;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 */
class Controller {

  /**
   * The ID of this plugin.
   *
   * @since    0.0.1
   * @access   private
   * @var      string    $pluginName    The ID of this plugin.
   */
  private $pluginName;

  /**
   * The version of this plugin.
   *
   * @since    0.0.1
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /*
   *
   */
  private $accountOption;
  private $interfaceOption;

  /**
   * Initialize the class and set its properties.
   *
   * @since    0.0.1
   * @param      string    $pluginName       The name of this plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct($pluginName, $version) {

    $this->pluginName = $pluginName;
    $this->version = $version;
  }

  public function addAdminPage() {
    $accountOption = new AccountOption();
    $accountModel = $accountOption->get();

    $restAdminAPI = new RestAdminAPI();
    $interfaceOption = new InterfaceOption($restAdminAPI);


    $settingsPage = new SettingsPage($interfaceOption, $accountOption, $restAdminAPI);
    $loginPage = new LoginPage($accountOption, $restAdminAPI);

    $isValid = false;
    if ($accountModel != false) {
      $isValid = $restAdminAPI->validateClient($accountModel);
    }

    $page = $isValid ? $settingsPage : $loginPage;
    echo $page->getHTML();
  }

  public function ajaxAddInterface() {
    $nonce = $_POST['ajax-nonce'];
    if (!wp_verify_nonce($nonce, 'ajax-add-api-interfaces')) {
      status_header(400);
      exit('Failed nonce');
    }

    $accountOption = new AccountOption();
    $restAdminAPI = new RestAdminAPI();
    $interfaceOption = new InterfaceOption($restAdminAPI);

    $settingsPage = new SettingsPage($interfaceOption, $accountOption, $restAdminAPI);
    $result = $settingsPage->addInterface();
    exit(json_encode(array('result' => $result)));
  }

  public function ajaxAddPage() {
    $nonce = $_POST['ajax-nonce'];
    if (!wp_verify_nonce($nonce, 'ajax-add-api-page')) {
      status_header(400);
      exit('Failed nonce');
    }

    $filters = array('page' => FILTER_SANITIZE_STRING);

    // Sanitize data
    $postData = array_filter(filter_var_array($_POST, $filters));

    if (count($postData) !== count($filters)) {
      status_header(400);
      exit("Invalid data");
    }

    $accountOption = new AccountOption();
    $restAdminAPI = new RestAdminAPI();
    $interfaceOption = new InterfaceOption($restAdminAPI);

    $settingsPage = new SettingsPage($interfaceOption, $accountOption, $restAdminAPI);
    $result = $settingsPage->addPage($postData['page']);
    exit(json_encode(array('result' => $result)));
  }

  /**
   *
   */
  public function ajaxSaveAccount() {
    $nonce = $_POST['ajax-nonce'];
    if (!wp_verify_nonce($nonce, 'ajax-save-account-nonce')) {
      status_header( 400 );
      exit("Failed nonce");
    }

    $accountOption = new AccountOption();
    $restAdminAPI = new RestAdminAPI();
    $loginPage = new LoginPage($accountOption, $restAdminAPI);

    $filters = array(
      'email' => FILTER_SANITIZE_STRING,
      'password' => FILTER_SANITIZE_STRING,
    );

    // Sanitize data
    $postData = filter_var_array($_POST, $filters);

    // Delete false or null data
    $postData = array_filter($postData);

    if(count($postData) !== count($filters) ) {
      status_header(400);
      exit("Invalid data");
    }

    $result = $loginPage->saveAccount($postData);

    echo json_encode(compact(array('result')));
    exit;
  }

  /**
   *
   */
  public function ajaxUserLogout() {
    $nonce = $_POST['ajax-nonce'];

    if (!wp_verify_nonce($nonce, 'ajax-user-logout-nonce')) {
      status_header( 400 );
      exit("Failed nonce");
    }

    $accountOption = new AccountOption();
    $restAdminAPI = new RestAdminAPI();
    $loginPage = new LoginPage($accountOption, $restAdminAPI);

    $result = $loginPage->userLogout();

    echo json_encode(compact(array('result')));
    exit;
  }

  /**
   *
   */
  public function ajaxGetInterfaces() {
    $nonce = $_POST['ajax-nonce'];

    if (!wp_verify_nonce($nonce, 'ajax-get-interfaces')) {
      status_header( 400 );
      exit("Failed nonce");
    }

    $accountOption = new AccountOption();
    $restAdminAPI = new RestAdminAPI();
    $interfaceOption = new InterfaceOption($restAdminAPI);

    $settingsPage = new SettingsPage($interfaceOption, $accountOption, $restAdminAPI);

    $interfaces = $settingsPage->getInterfaces();

    echo json_encode(array('result' => $interfaces));
    exit;
  }

  public function ajaxDeleteInterface() {
    $nonce = $_POST['ajax-nonce'];
    $result = false;


    if (!wp_verify_nonce($nonce, 'ajax-delete-api-interfaces')) {
      status_header( 400 );
      exit("Failed nonce");
    }

    $accountOption = new AccountOption();
    $restAdminAPI = new RestAdminAPI();
    $interfaceOption = new InterfaceOption($restAdminAPI);

    $settingsPage = new SettingsPage($interfaceOption, $accountOption, $restAdminAPI);

    $result = false;
    if (isset($_POST['deleteinterface']) && !empty($_POST['deleteinterface'])) {
      $interfaceId = filter_var($_POST['deleteinterface'], FILTER_SANITIZE_NUMBER_INT);
      $deletePage = $_POST['deletePage'] === 'true';
      $result = $settingsPage->deleteInterface($interfaceId, $deletePage);
    }
    echo json_encode(array('result' => $result));
    exit;
  }

  /**
   * Register the JavaScript for the admin area.
   *
   * @since    0.0.1
   */
  public function enqueue_scripts() {

    /**
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in PluginName_Loader as all of the hooks are defined
     * in that particular class.
     *
     * The PluginName_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     */
    wp_enqueue_script('jquery-form');
    wp_enqueue_script('underscore');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style('jquery-ui-style', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css', array(), '1.11.4');

    wp_enqueue_script(
      $this->pluginName, plugin_dir_url(__FILE__) . '../../assets/admin/js/bullseye-locations-admin.min.js', array( 'jquery', 'jquery-form' ),
      $this->version,
      false
    );

    $accountOption = new AccountOption();
    $accountModel = $accountOption->get();

    if (!is_null($accountModel)) {

      $isValidAccount = false;
      $restAdminAPI = new RestAdminAPI();
      if ($accountModel != false) {
        $isValidAccount = $restAdminAPI->validateClient($accountModel);
      }

      if ($isValidAccount) {
        $interfaceOption = new InterfaceOption($restAdminAPI);

        $settingsPage = new SettingsPage($interfaceOption, $accountOption, $restAdminAPI);
        $interfaces = $settingsPage->getInterfaces();

        wp_localize_script(
                $this->pluginName, 'BullseyeSettingsData', array(
                  'email'      => $accountModel->getEmail(),
                  'interfaces' => $interfaces
                )
        );
      }
    }

    wp_localize_script(
      $this->pluginName, 'BullseyeLogin',
       array(
        'action'  => 'save_account',
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce('ajax-save-account-nonce')
       )
    );

    wp_localize_script(
      $this->pluginName, 'BullseyeUserLogout',
       array(
        'action'  => 'user_logout',
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce('ajax-user-logout-nonce')
       )
    );
    $iconUrl = plugin_dir_url(__FILE__) . '../../assets/images/';
    $hrefIconUrl = 'http://app.bullseyelocations.com/Admin/';
    // Check envoriment variables to change API URL ( Useful to develop or test)
    if (getenv('DASHBOARD_URL')) {
      $hrefIconUrl = getenv('DASHBOARD_URL');
    }

    wp_localize_script(
      $this->pluginName, 'BullseyeInterfaceSetting',
       array(
        'action'  => 'interface_get_model',
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce('ajax-get-interfaces'),
        'img_url' => array(
            'img_url_dashboard' => $iconUrl . 'icon-dashboard.png',
            'img_url_locations' => $iconUrl . 'icon-locations.png',
            'img_url_reports' => $iconUrl . 'icon-reports.png',
        ),
        'action_url' => array(
            'action_url_dashboard' => $hrefIconUrl . 'Dashboard.aspx',
            'action_url_locations' => $hrefIconUrl . 'Locations',
            'action_url_reports' => $hrefIconUrl . 'Reports/Default.aspx',
        )
       )
    );
    wp_localize_script(
      $this->pluginName, 'BullseyeDeleteApiInterface',
       array(
        'action'  => 'delete_api_interface',
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce('ajax-delete-api-interfaces')
       )
    );
    wp_localize_script(
      $this->pluginName, 'BullseyeAddApiInterface', array(
        'action' => 'add_api_interface',
        'nonce' => wp_create_nonce('ajax-add-api-interfaces'))
    );
    wp_localize_script(
            $this->pluginName, 'BullseyeAddApiPage', array(
        'action' => 'add_api_page',
        'nonce' => wp_create_nonce('ajax-add-api-page'))
    );
  }

  /**
   * Register the stylesheets for the admin area.
   *
   * @since    0.0.1
   */
  public function enqueue_styles() {

    /**
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in PluginName_Loader as all of the hooks are defined
     * in that particular class.
     *
     * The PluginName_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     */

    wp_enqueue_style($this->pluginName, plugin_dir_url(__FILE__) . '../../assets/admin/css/bullseye-locations-admin.min.css', array(), $this->version, 'all');
  }

  public function menu() {
    $img_url_icons = plugin_dir_url(__FILE__) . '../../assets/images/';

    add_menu_page(
      'Bullseye Store Locator for WP',
      'Bullseye',
      'manage_options',
      'bullseye_settings',
      array($this, 'addAdminPage'),
      $img_url_icons . 'be-wp-icon-20x20.png'
    );
  }

  /**
   * Delete an WordPress integration for an account in Bullseye.
   *
   * @return true on success, otherwise false.
   */
  public function deleteBullseyeIntegration(){
    $accountOption = new AccountOption();
    $accountModel = $accountOption->get();
    $restAdminAPI = new RestAdminAPI();

    if ($accountModel != false) {
      $clientId = $restAdminAPI->validateClient($accountModel);
      if($clientId){
        return false !== $restAdminAPI->unlinkBullseyeAccount($clientId, $accountModel->getEmail());
      }
    }

    return false;
  }
}
