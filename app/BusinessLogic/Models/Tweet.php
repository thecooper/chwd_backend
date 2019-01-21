<?php

namespace App\BusinessLogic\Models;

use App\BusinessLogic\Models\TwitterEntity;
use App\BusinessLogic\Models\TwitterUser;

class Tweet {
  /**
   * id
   *
   * @var number
   */
  public $id;

  /**
   * created_at
   *
   * @var datetime Format: ???
   */
  public $created_at;
  
  /**
   * text
   *
   * @var string
   */
  public $text;
  
  /**
   * source
   *
   * @var string
   */
  public $source;
  
  /**
   * entities
   *
   * @var TwitterEntity
   */
  public $entities;
  
  /**
   * twitter_user
   *
   * @var TwitterUser
   */
  public $twitter_user;

  public function __construct() {
    $this->entities = new TwitterEntity();
    $this->twitter_user = new TwitterUser();
  }
}
