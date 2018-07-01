<?php

/**
 * Page for validate Bullseye credentials
 *
 * @since      0.0.1
 */

namespace BullseyeLocations\admin;

use BullseyeLocations\models\Account as AccountModel;

/**
 * Page for validate Bullseye credentials
 *
 * Prints HTML form to login, saves credentials in database
 *
 */
class LoginPage {

  public function __construct($accountOption, $api) {
    $this->accountOption = $accountOption;
    $this->restAdminAPI = $api;
  }

  public function saveAccount($data) {

    $anAccount = new AccountModel($data);
    $clientId = $this->restAdminAPI->validateClient($anAccount);

    if($clientId !== false) {
      $anAccount->setClientId($clientId);

      $response = $this->restAdminAPI->getPluginAccount();
      if (!$response) {
        $this->restAdminAPI->linkBullseyeAccount($clientId, $anAccount->getEmail());
      }

      $this->accountOption->save($anAccount);
      return true;
    }

    return false;
  }

  /*
   * function ajaxUserLogout: check the nonce and
   *  call remove function from Account class to remove seccion.
   * return bool
   */
  public function userLogout() {
    return $this->accountOption->remove();
  }

  public function getHTML() {
    $assetsDir = __DIR__ . './../../assets';
    $file = "$assetsDir/admin/html/bullseye-locations-admin-login.html";
    return file_get_contents($file);
  }
}
