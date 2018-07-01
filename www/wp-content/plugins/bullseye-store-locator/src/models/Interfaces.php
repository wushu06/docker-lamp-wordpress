<?php
namespace BullseyeLocations\models;

/*
 *
 */
class Interfaces
 {

  private $name;
  private $createdAt;
  private $interfaceId;
  private $publishCode;
  private $pageId;
  private $email;
  private $adminUrl;
  private $interfaceUrl;

  /*
   * @param data Data from API
   */

  public function __construct($data = array()) {
    $this->name = isset($data['InterfaceName']) ? $data['InterfaceName'] : NULL;

    $this->interfaceId = isset($data['InterfaceId']) ? $data['InterfaceId'] : NULL;
    $this->publishCode = isset($data['PublishCode']) ? $data['PublishCode'] : NULL;
    $this->adminUrl = isset($data['AdminUrl']) ? $data['AdminUrl'] : NULL;
    $this->interfaceUrl = isset($data['InterfaceUrl']) ? $data['InterfaceUrl'] : NULL;

    // Expect format date = /Date(1450304956193-0500)/
    $dateString = isset($data['DateCreated']) ? $data['DateCreated'] : '';

    $dateNumbersIn = array();
    preg_match_all("/([0-9]+)/", $dateString, $dateNumbersIn);

    // TODO: improve this
    $dateNumbers = $dateNumbersIn[0];

    if (count($dateNumbers) === 2) {

      $unixTime = intval(intval($dateNumbers[0]) / 1000);

      // TODO: save time zone
      //$timeZone = $dateNumbers[1];

      $this->createdAt = $unixTime;
    }
  }

  public function getCreatedAt() {
    return $this->createdAt;
  }

  public function getEditLink() {
    return $this->adminUrl;
  }

  public function getEmail() {
    return $this->email;
  }

  public function getInterfaceId() {
    return $this->interfaceId;
  }

  public function getName() {
    return $this->name;
  }

  public function getPageId() {
    return $this->pageId;
  }

  public function getPublishCode() {
    return $this->publishCode;
  }

  /**
   * Returns a link can be :
   *   - A Wordpress page if the interface was created with the page
   *   - Bullseye link in other case
   *
   * @return string URL to edit
   */
  public function getViewLink() {
    // Verify if the page exists
    if (is_null($this->pageId) || !get_post_status($this->pageId)) {
      return $this->interfaceUrl;
    }
    return get_page_link($this->pageId);
  }

  public function setAdminUrl($url) {
    $this->adminUrl = $url;
  }

  public function setEmail($email) {
    $this->email = $email;
  }

  public function setInterfaceUrl($url) {
    $this->interfaceUrl = $url;
  }

  public function setPageId($page) {
    $this->pageId = $page;
  }

  public function setPublishCode($code) {
    $this->publishCode = $code;
  }

}
