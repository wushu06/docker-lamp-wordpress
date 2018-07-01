<?php

/**
 * Page for manage all Bullseye options
 *
 * @since      0.0.1
 */

namespace BullseyeLocations\admin;

use BullseyeLocations\api\RestAdmin as RestAdminAPI;
use BullseyeLocations\models\Interfaces as InterfaceModel;
use BullseyeLocations\options\Account as AccountOption;
use BullseyeLocations\options\Interfaces as InterfaceOption;

/**
 * Page for manage all Bullseye options
 *
 * Require Bullseye credentials for access
 *
 */
class SettingsPage {

  private $interfaceOption = null;
  private $accountOption = null;
  private $restAdminAPI = null;

  /**
   * constructor, declare an object "interfaceOption"
   */
  public function __construct(InterfaceOption $io, AccountOption $ao, RestAdminAPI $api) {
    $this->interfaceOption = $io;
    $this->accountOption = $ao;
    $this->restAdminAPI = $api;
  }

  public function addInterface() {
    $accountModel = $this->accountOption->get();
    $clientId = $accountModel->getClientId();
    return $this->restAdminAPI->createDefaultInterface($clientId);
  }

  /**
   * Create a new interface and link with a page
   */
  public function addPage($pageTitle) {
    $accountModel = $this->accountOption->get();
    $clientId = $accountModel->getClientId();

    // Create interface with API
    $newInterfaceName = $this->restAdminAPI->createDefaultInterface($clientId);
    if (!$newInterfaceName) {
      return false;
    }

    // Create new page with shortcode
    $newPage = array(
      'post_content' => '[BE interface="' . $newInterfaceName . '"]',
      'post_status' => 'publish',
      'post_title' => wp_strip_all_tags($pageTitle),
      'post_type' => 'page'
    );
    $idPage = wp_insert_post($newPage);
    if (!$idPage) {
      return false;
    }
    // Update WP info
    $this->syncInterfaceList();
    $interfaces = $this->interfaceOption->getArray();
    foreach ($interfaces as &$interfaz) {
      if ($interfaz->getName() === $newInterfaceName) {
        $interfaz->setPageId($idPage);
      }
    }
    $updated = $this->interfaceOption->update($interfaces);
    return $updated;
  }

  /**
   * delete interface from api and wp
   */
  public function deleteInterface($interfaceId, $deletePage) {
    // Delete from API
    $result = $this->restAdminAPI->deleteApiInterfaces($interfaceId);
    if ($result) {
      $interface = $this->interfaceOption->getById(intval($interfaceId));
      // Delete from WP option
      $result = $this->interfaceOption->remove($interfaceId);
      // Delete page
      if ($interface && $deletePage) {
        $pageId = $interface->getPageId();
        if ($pageId) {
          wp_delete_post($pageId);
        }
      }
    }
    return $result;
  }

  /**
   * return: all information from bullseye-interface options
   */
  public function getInterfaces() {
    $this->syncInterfaceList();
    // get account options
    $account = $this->accountOption->get();
    $email = $account->getEmail();
    // interface options
    $interfaces = $this->interfaceOption->getArray();

    $results = array();
    foreach ($interfaces as $interface) {
      $result = array();
      $result['viewLink'] = $interface->getViewLink();
      $result['editLink'] = $interface->getEditLink();
      $result['name'] = $interface->getName();
      $result['pageId'] = $interface->getPageId();
      $result['createdAt'] = date('F j, Y', $interface->getCreatedAt());
      $result['interfaceId'] = $interface->getInterfaceId();
      $result['publishCode'] = '[BE interface="' . $interface->getName() . '"]';

      /// Verify interfaces saved owns current user
      if ($email === $interface->getEmail()) {
        $results[] = $result;
      }
    }
    return $results;
  }

  public function getHTML() {
    $assetsDir = __DIR__ . './../../assets';
    $file = "$assetsDir/admin/html/bullseye-locations-admin-settings.html";
    return file_get_contents($file);
  }

  public function syncInterfaceList() {
    $accountModel = $this->accountOption->get();
    $apiInterfaces = $this->restAdminAPI->getPluginInterfaces($accountModel->getClientId());
    $wpInterfaces = $this->interfaceOption->getArray();
    $newInterfaces = array();
    //TODO: Improve this, maybe sorting arrays, it's fine in this moment
    //      because are few elements
    foreach ($apiInterfaces as $apiInterfaz) {
      $found = false;
      foreach ($wpInterfaces as $wpInterfaz) {
        if ($wpInterfaz->getInterfaceId() === $apiInterfaz['InterfaceId']) {
          // Same ID, so going to update info
          $found = true;
          $wpInterfaz->setPublishCode($apiInterfaz['PublishCode']);
          $wpInterfaz->setAdminUrl($apiInterfaz['AdminUrl']);
          $wpInterfaz->setInterfaceUrl($apiInterfaz['InterfaceUrl']);
          $newInterfaces[] = $wpInterfaz;
        }
      }
      if (!$found) {
        // ID not found, so is a new Interfaz
        $new = new InterfaceModel($apiInterfaz);
        $new->setEmail($accountModel->getEmail());
        $newInterfaces[] = $new;
      }
    }
    $this->interfaceOption->update($newInterfaces);
  }
}
