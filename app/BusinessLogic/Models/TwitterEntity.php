<?php

namespace App\BusinessLogic\Models;

class TwitterEntity {
  /**
   * urls
   *
   * @var EntityUrl[]
   */
  public $urls;

  public function __construct() {
    $this->urls = [];
  }
}