<?php
namespace BullseyeLocations\models;

/*
 *
 */
class Account
{
    private $email = null;
    private $password = null;
    private $clientId = null;

    public function __construct($data)
    {
      if(isset($data['email'])) {
        $this->email = $data['email'];
      }

      if(isset($data['password'])) {
        $this->password = $data['password'];
      }
    }
    
    public function getClientId()
    {
      return $this->clientId;
    }
    
    /**
     *
     * @return string
     */
    public function getEmail()
    {
      return $this->email;
    }

    /**
     *
     * @return string
     */
    public function getPassword()
    {
      return $this->password;
    }

    public function setClientId($clientId)
    {
      $this->clientId = $clientId;
    }
}
