<?php
namespace BullseyeLocations\options;

use BullseyeLocations\api\RestAdmin as RestAdminAPI;

/*
 *
 */
class Interfaces
{

  private $optionName = "bullseye-interface";
  private $model = array();

  /**
  * Load data from Wordpess Options
  */
  public function __construct(RestAdminAPI $api) {
    $this->api = $api;
    $option = get_option($this->optionName);

    if ($option !== false) {
      $this->model = $option;
    }
  }

  /**
   *
   * @return InterfaceModel[]
   */
  public function getArray() {
    return $this->model;
  }

  public function getById($interfaceId) {
    foreach ($this->model as $interface) {
      if ($interface->getInterfaceId() === $interfaceId) {
        return $interface;
      }
    }
    return false;
  }

  public function getByName($interfaceName) {
    foreach ($this->model as $interface) {
      if ($interface->getName() === $interfaceName) {
        return $interface;
      }
    }
    return false;
  }

  public function update($interfaces) {
    $updated = update_option($this->optionName, $interfaces);
    if ($updated) {
      $this->model = $interfaces;
    }
    return $updated;
  }

  public function updateOne($newInterface) {
    $list = $this->model;
    foreach ($this->model as $key => $interface) {
      if ($interface->getInterfaceId() === $newInterface->getInterfaceId()) {
        $list[$key] = $newInterface;
      }
    }
    $updated = update_option($this->optionName, $list);
    if ($updated) {
      $this->model = $list;
    }
    return $updated;
  }

  public function remove($interfaceid) {
    foreach ($this->model as $key => $interface) {
      if ( $interface->getInterfaceId() == $interfaceid) {
        unset($this->model[$key]);
        $this->update($this->model);
        return true;
      }
    }
    return false;
  }

}
