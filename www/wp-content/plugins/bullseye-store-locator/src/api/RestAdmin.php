<?php

/**
 *
 *
 * @since      0.0.2
 */

namespace BullseyeLocations\api;

use BullseyeLocations\models\Account as AccountModel;

/**
 *
 */
class RestAdmin {

  /**
   * URL API
   * @var url
   */
  private $url;
  private $adminKey = '70ABCF39-D794-48D8-BBCF-F413B1AEA64C';

  /**
   * Allow enable o disable API connections
   * Useful to disable API connections from de public side
   * @var bool
   */
  private $enable = true;

  const HTTP_OK = 200;
  const HTTP_GET = 1;
  const HTTP_POST = 2;

  public function __construct($enable = true) {
    $this->enable = $enable;

    // Check envoriment variables to change API URL ( Useful to develop or test)
    $this->url = 'http://ws.bullseyelocations.com/RestAdmin.svc/';
    if (getenv('API_URL')) {
      $this->url = getenv('API_URL');
    }
  }

  /**
   * Returns true if the client has configured an account
   * @return boolean
   */
  public function getPluginAccount() {
    $args = array();
    $args['ShopName'] = get_site_url();
    list($httpcode, $jsonResponse) = $this->query(self::HTTP_GET, 'getpluginaccount', $args);
    if ($httpcode !== self::HTTP_OK) {
      return false;
    }

    // $objResponse = json_decode($jsonResponse, true);
    return true;
  }

  public function getPluginInterfaces($clientId)
  {
    $args = array();
    $args['template'] = 'wordpress';
    $args['ClientId'] = $clientId;
    list($httpcode, $jsonResponse) = $this->query(self::HTTP_GET, 'getplugininterfaces', $args);
    if($httpcode !== self::HTTP_OK) {
      return false;
    }

    $objResponse = json_decode($jsonResponse, true);
    return $objResponse['ResultList'];
  }

  /**
   *
   */
  private function query($method, $action, $args = array())
  {
    if (!$this->enable) {
      return false;
    }

    $args['AdminKey'] = $this->adminKey;

    $fullUrl = $this->url . $action ;
    if( $method === self::HTTP_GET) {
      $fullUrl .= '?'. http_build_query($args);
    }

    $curl = curl_init();
    $options = array();
    $options[CURLOPT_URL] = $fullUrl;
    $options[CURLOPT_RETURNTRANSFER] = true;

    if( $method === self::HTTP_POST) {
      $data_string = json_encode($args);

      $options[CURLOPT_POST] = 1;
      $options[CURLOPT_CUSTOMREQUEST] = "POST";
      $options[CURLOPT_POSTFIELDS] = $data_string;
      $options[CURLOPT_HTTPHEADER] = array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string));

    }

    curl_setopt_array($curl, $options);
    $response = curl_exec($curl);
    $err = curl_error($curl);

    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($err) {
      return array($httpcode, $err);
    }
    return array($httpcode, $response);
  }

  /**
    *
    */
  public function validateClient(AccountModel $account) {
    $userName = $account->getEmail();
    $password = $account->getPassword();
    $role = 'client';
    $args = compact('userName', 'password', 'role');
    list($httpcode, $jsonResponse) = $this->query(self::HTTP_GET, 'validateclient', $args);

    if($httpcode !== self::HTTP_OK) {
      return false;
    }

    $objResponse = json_decode($jsonResponse, true);

    if (!isset($objResponse['ClientId'])) {
      return false;
    }

    return $objResponse['ClientId'];
  }

  public function createDefaultInterface($clientId) {
    $args = array();
    $args['StoreName'] = get_site_url();
    $args['Template'] = 'wordpress';
    $args['ClientID'] = $clientId;

    list($httpcode, $jsonResponse) = $this->query(self::HTTP_POST, 'createdefaultinterface', $args);

    if ($httpcode !== self::HTTP_OK) {
      return false;
    }

    $objResponse = json_decode($jsonResponse, true);

    return $objResponse['CreateDefaultInterfaceResult'];
  }

  public function linkBullseyeAccount($clientId, $userName, $defaultInterface = null) {
    $args = array();
    $args['ClientID'] = $clientId;
    $args['PluginType'] = 2;
    $args['UserName'] = $userName;
    $args['ShopName'] = get_site_url();
    //$args['InterfaceName'] = $defaultInterface;
    list($httpcode, $jsonResponse) = $this->query(self::HTTP_POST, 'linkbullseyeaccount', $args);
    if ($httpcode !== self::HTTP_OK) {
      return false;
    }
    $objResponse = json_decode($jsonResponse, true);
    return $objResponse;
  }

  /**
   * Delete an integration record between an account and WordPress in the Bullseye platform.
   */
  public function unlinkBullseyeAccount($clientId, $userName){
    $args = array();
    $args['ClientID'] = $clientId;
    $args['UserName'] = $userName;
    $args['Url'] = get_site_url();
    list($httpcode, $jsonResponse) = $this->query(self::HTTP_POST, 'DeleteBullseyePluginAccount', $args);
    if ($httpcode !== self::HTTP_OK) {
      return false;
    }
    $objResponse = json_decode($jsonResponse, true);
    return $objResponse;
  }

  // delete interface from the api
  public function deleteApiInterfaces($interfaceId) {
    $args['InterfaceId'] = $interfaceId;
    list($httpcode, $jsonResponse) = $this->query(self::HTTP_POST, 'deleteinterface', $args);

    if ($httpcode !== self::HTTP_OK) {
      return false;
    }

    $response = json_decode($jsonResponse, true);
    if (isset($response['DeleteInterfaceResult'])) {
      return $response['DeleteInterfaceResult'];
    }
    return false;
  }

}
