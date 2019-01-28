<?php

namespace App\BusinessLogic\Serializer;

use App\BusinessLogic\Models\Tweet;
use App\BusinessLogic\Models\TwitterUser;
use App\BusinessLogic\Models\TwitterEntity;
use App\BusinessLogic\Models\EntityUrl;

class TweetJsonSerializer implements ITweetSerializer {
  /**
   * serialize
   *
   * @param Tweet $tweet
   * @return string
   */
  public function serialize(Tweet $tweet) {
    return json_encode($tweet);
  }

  public function parse(string $input) {
    $decoded_json = json_decode($input, true);
    $tweet = new Tweet();

    $tweet->id = $decoded_json['id'];
    $tweet->created_at = $decoded_json['created_at'];
    $tweet->text = $decoded_json['text'];
    $tweet->source = $decoded_json['source'];

    $tweet->twitter_user = new TwitterUser();
    $tweet->twitter_user->id = $decoded_json['twitter_user']['id'];
    $tweet->twitter_user->name = $decoded_json['twitter_user']['name'];
    $tweet->twitter_user->screen_name = $decoded_json['twitter_user']['screen_name'];
    $tweet->twitter_user->location = $decoded_json['twitter_user']['location'];
    $tweet->twitter_user->description = $decoded_json['twitter_user']['description'];
    $tweet->twitter_user->url = $decoded_json['twitter_user']['url'];
    $tweet->twitter_user->profile_image_url = $decoded_json['twitter_user']['profile_image_url'];
    $tweet->twitter_user->profile_image_url_https = $decoded_json['twitter_user']['profile_image_url_https'];

    $tweet->entities = [];

    if(isset($decoded_json['entities']['urls'])) {
      $urls = $decoded_json['entities']['urls'];
      $twitter_entity = new TwitterEntity();

      foreach($urls as $url) {
        $entity_url = new EntityUrl();

        $entity_url->url = $url['url'];
        $entity_url->expanded_url = $url['expanded_url'];
        $entity_url->display_url = $url['display_url'];

        array_push($twitter_entity->urls, $entity_url);
      }

      array_push($tweet->entities, $twitter_entity);
    }

    return $tweet;
  }
}