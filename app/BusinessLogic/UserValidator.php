<?php

namespace App\BusinessLogic;

use \Exception;

class UserValidator {
  public function validate($name, $email, $password) {
    // 'name' => 'required|string|max:255',
    if(gettype($name) != 'string') {
      throw new Exception('name field must be a string type');
    } else if($name == '') {
      throw new Exception('name field is required');
    } else if(strlen($name) > 255) {
      throw new Exception('name field must be less than 255 characters');
    }

    // 'email' => 'required|string|email|max:255|unique:users',
    if(gettype($email) != 'string') {
      throw new Exception('email field must be a string type');
    } else if($email == '') {
      throw new Exception('email field is required');
    } else if(strlen($email) > 255) {
      throw new Exception('email field must be less than 255 characters');
    }
    
    // 'password' => 'required|string|min:6|confirmed'
    if(gettype($password) != 'string') {
      throw new Exception('password field must be a string type');
    } else if($password == '') {
      throw new Exception('password field is required');
    } else if(strlen($password) < 6) {
      throw new Exception('password field must be at least 6 characters');
    }
  }
}