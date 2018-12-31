<?php

namespace App\BusinessLogic;

use Illuminate\Support\Facades\Hash;

use App\DataLayer\User as DatabaseUser;
use App\BusinessLogic\Models\User;
use App\BusinessLogic\UserValidator;

class UserManager {

  private $user;
  private $user_validator;

  public function __construct(DatabaseUser $user, UserValidator $user_validator) {
    $this->user = $user;
    $this->user_validator = $user_validator;
  }

  public function get_users() {
    $users = [];

    foreach($this->user->all() as $user) {
      $new_user = $this->translate_user($user);
      array_push($users, $new_user);
    }

    return $users;
  }

  public function save_user($name, $email, $password) {
    $this->user_validator->validate($name, $email, $password);
    
    // TODO: validate email address doesn't already exist
    
    $db_user = $this->user->create([
      'name' => $name,
      'email' => $email,
      'password' => Hash::make($password)
    ]);

    try {
      return $this->translate_user($db_user);
    } catch (Exception $ex) {
      throw new Exception('Unable to translate user data to user model: ' . $ex->getMessage(), 0, $ex);
    }
  }
  
  public function translate_user($db_user) {
    $user = new User();

    $user->id = $db_user->id;
    $user->name = $db_user->name;
    $user->email = $db_user->email;
    $user->password = $db_user->password;
    $user->polling_location_address_1 = $db_user->polling_location_address_1;
    $user->polling_location_address_2 = $db_user->polling_location_address_2;
    $user->polling_location_city = $db_user->polling_location_city;
    $user->polling_location_state = $db_user->polling_location_state;
    $user->polling_location_zip = $db_user->polling_location_zip;
    $user->polling_location_time_open = $db_user->polling_location_time_open;
    $user->polling_location_time_closed = $db_user->polling_location_time_closed;

    return $user;
  }
}