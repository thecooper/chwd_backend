<?php

namespace App\BusinessLogic\Serializer;

use App\BusinessLogic\Models\Tweet;

/**
 * ITweetSerializer
 * 
 * @name ITweetSerializer
 * @description Interface for Tweet serializer implementations
 */
interface ITweetSerializer {
  /**
   * to_string
   *
   * @param Tweet $tweet
   * @return string
   */
  function serialize(Tweet $tweet);

  /**
   * parse
   *
   * @param string $value
   * @return Tweet
   */
  function parse($value);
}