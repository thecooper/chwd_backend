<?php

namespace App\BusinessLogic\Serializer;

use App\BusinessLogic\Models\Tweet;
use App\BusinessLogic\Models\TwitterUser;
use App\BusinessLogic\Models\TwitterEntity;
use App\BusinessLogic\Models\EntityUrl;

class TweetJsonSerializer implements ITweetSerializer {
  public function serialize(Tweet $tweet) {
    return json_encode($tweet);
  }

  public function parse($input) {
    $tweet = new Tweet();

    $tweet->id = $input->id;
    $tweet->created_at = $input->created_at;
    $tweet->text = $input->text;
    $tweet->source = $input->source;

    $tweet->twitter_user = new TwitterUser();
    $tweet->twitter_user->id = $input->user->id;
    $tweet->twitter_user->name = $input->user->name;
    $tweet->twitter_user->screen_name = $input->user->screen_name;
    $tweet->twitter_user->location = $input->user->location;
    $tweet->twitter_user->description = $input->user->description;
    $tweet->twitter_user->url = $input->user->url;
    $tweet->twitter_user->profile_image_url = $input->user->profile_image_url;
    $tweet->twitter_user->profile_image_url_https = $input->user->profile_image_url_https;

    $tweet->entities = [];

    foreach($input->entities->urls as $url) {
      $twitter_entity = new TwitterEntity();

      $entity_url = new EntityUrl();
      $entity_url->url = $url->url;
      $entity_url->expanded_url = $url->expanded_url;
      $entity_url->display_url = $url->display_url;

      array_push($twitter_entity->urls, $entity_url);

      array_push($tweet->entities, $twitter_entity);
    }

    return $tweet;
  }
}