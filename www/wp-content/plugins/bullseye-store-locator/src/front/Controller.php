<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      0.0.1
 */

namespace BullseyeLocations\front;

use BullseyeLocations\models\Account as AccountModel;
use BullseyeLocations\options\Interfaces as InterfaceOption;
use BullseyeLocations\api\RestAdmin as RestAdminAPI;

/**
 * The public-facing functionality of the plugin.
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
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;


  /**
   * The version of this plugin.
   *
   * @since    0.0.1
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;


  /**
   * Initialize the class and set its properties.
   *
   * @since    0.0.1
   * @param      string    $plugin_name       The name of the plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct( $plugin_name, $version ) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

  }

  public function ajaxDeleteInterface() {
    // This AJAX endpoint does not have wp_nonce because is for public access
    // outside Wordpress

    $filters = array(
        'email' => FILTER_SANITIZE_STRING,
        'password' => FILTER_SANITIZE_STRING,
        'interfaceId' => FILTER_SANITIZE_NUMBER_INT,
    );

    // Sanitize data
    $postData = array_filter(filter_var_array($_POST, $filters));

    if (count($postData) !== count($filters)) {
      status_header(400);
      wp_die(json_encode(array('DeleteInterface' => 'Invalid data')));
    }

    $restAdminAPI = new RestAdminAPI();
    $account = new AccountModel($postData);

    $valid = $restAdminAPI->validateClient($account);
    if (!$valid) {
      status_header(400);
      wp_die(json_encode(array('DeleteInterface' => 'Invalid credentials')));
    }

    $interfaceOption = new InterfaceOption($restAdminAPI);

    $interfaceFound = $interfaceOption->getById(intval($postData['interfaceId']));
    if (!$interfaceFound) {
      status_header(404);
      wp_die(json_encode(array('DeleteInterface' => 'Interface not found')));
    }

    $result = $interfaceOption->remove($interfaceFound->getInterfaceId());
    wp_die(json_encode(array('DeleteInterface' => $result)));
  }

  public function ajaxUpdateInterface() {
    // This AJAX endpoint does not have wp_nonce because is for public access
    // outside Wordpress
    $filters = array(
        'publishCode' => FILTER_UNSAFE_RAW, // Unsafe because we accept HTML or JS code
        'email' => FILTER_SANITIZE_STRING,
        'password' => FILTER_SANITIZE_STRING,
        'interfaceId' => FILTER_SANITIZE_NUMBER_INT,
    );

    // Sanitize data
    $postData = array_filter(filter_var_array($_POST, $filters));

    if (count($postData) !== count($filters)) {
      status_header(400);
      wp_die(json_encode(array('UpdateInterface' => 'Invalid data')));
    }

    $restAdminAPI = new RestAdminAPI();
    $account = new AccountModel($postData);

    $valid = $restAdminAPI->validateClient($account);

    if (!$valid) {
      status_header(400);
      wp_die(json_encode(array('UpdateInterface' => 'Invalid credentials')));
    }

    $interfaceOption = new InterfaceOption($restAdminAPI);

    $interfaceFound = $interfaceOption->getById(intval($postData['interfaceId']));
    if (!$interfaceFound) {
      status_header(404);
      wp_die(json_encode(array('UpdateInterface' => 'Interface not found')));
    }

    $publishCode = stripslashes(stripslashes($postData['publishCode']));
    $interfaceFound->setPublishCode($publishCode);
    $result = $interfaceOption->updateOne($interfaceFound);
    wp_die(json_encode(array('UpdateInterface' => $result)));
  }

  public function beShortcode($atts) {
    // Attributes
    extract(shortcode_atts(array('interface' => null), $atts));
    $restAdminAPI = new RestAdminAPI(false); // In public side, there is not connection to API
    $interfaceOption = new InterfaceOption($restAdminAPI);

    $interfaceFound = $interfaceOption->getByName($interface);
    if ($interfaceFound) {
      return $interfaceFound->getPublishCode();
    } else {
      return "Bullseye interface $interface not found";
    }
  }

}
