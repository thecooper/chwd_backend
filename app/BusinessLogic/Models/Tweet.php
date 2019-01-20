<?php

namespace App\BusinessLogic\Models;

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
   * entities
   *
   * @var TwitterEntity[]
   */
  public $entities;
  
  /**
   * source
   *
   * @var string
   */
  public $source;
  
  /**
   * twitter_user
   *
   * @var TwitterUser
   */
  public $twitter_user;
}
