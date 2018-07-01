<?php
namespace BullseyeLocations\options;

use BullseyeLocations\models\Account as AccountModel;
/*
 *
 */
class Account
{
    private $optionName = "bullseye-account";
    /*
     * AccountModel
     */
    private $model = NULL;

    /**
     * Load data from Wordpess Options
     */
    public function __construct() {
      $option = get_option($this->optionName);

      if($option !== false) {
        $this->model = $option;
      }
    }

    public function get(){
      return $this->model;
    }

    /**
    *
    *
    * @since     0.0.1
    * @return    bool
    */
    public function save(AccountModel $model)
    {
      return update_option($this->optionName, $model);
    }

    /**
    * @since     0.0.1
    * @return    bool
    */
    public function remove()
    {
      return delete_option($this->optionName);
    }
}
