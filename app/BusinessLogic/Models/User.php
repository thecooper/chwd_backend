<?php

namespace App\BusinessLogic\Models;

class User {
  public $id;
  public $name;
  public $email;
  public $password;
  public $polling_location_address_1;
  public $polling_location_address_2;
  public $polling_location_city;
  public $polling_location_state;
  public $polling_location_zip;
  public $polling_location_time_open;
  public $polling_location_time_closed;
}