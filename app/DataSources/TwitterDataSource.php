<?php

namespace App\DataSources;

use App\BusinessLogic\Models\Tweet;
use App\BusinessLogic\Models\TwitterUser;
use App\BusinessLogic\Models\TwitterEntity;
use App\BusinessLogic\Models\EntityUrl;
use App\BusinessLogic\Serializer\TweetJsonSerializer;
use App\BusinessLogic\Serializer\ITweetSerializer;

use Abraham\TwitterOAuth\TwitterOAuth;

use Exception;

/**
 * TwitterDataSource
 * 
 * @description This class is used to get data from the Twitter API
 */
class TwitterDataSource {
  private $twitter_oauth;

  private $bearer_token = '';

  public function __construct() {
    $twitter_api_key = env('TWITTER_API_KEY');
    $twitter_api_secret = env('TWITTER_API_SECRET');
    $access_token = env('TWITTER_ACCESS_TOKEN');
    $access_token_secret = env('TWITTER_ACCESS_TOKEN_SECRET');
    
    if($twitter_api_key == null) {
      throw new Exception('Instantiation of TwitterDataSource failed: need to provide TWITTER_API_KEY in environment configuration');
    }

    if($twitter_api_secret == null) {
      throw new Exception('Instantiation of TwitterDataSource failed: need to provide TWITTER_API_SECRET in environment configuration');
    }

    if($access_token === null) {
      throw new Exception('Instantiation of TwitterDataSource failed: need to provide TWITTER_ACCESS_TOKEN in environment configuration');
    }

    if($access_token_secret === null) {
      throw new Exception('Instantiation of TwitterDataSource failed: need to provide TWITTER_ACCESS_TOKEN_SECRET in environment configuration');
    }

    $this->twitter_oauth = new TwitterOAuth($twitter_api_key, $twitter_api_secret, $access_token, $access_token_secret);
  }

  /**
   * get_tweets_by_handle
   *
   * @param string[] $handles
   * @return Tweet[]
   */
  public function get_tweets_by_handles(array $handles) {
    $batch_handles = $this->batch_handles($handles);
    
    $list_id = $this->create_temporary_list();

    foreach($batch_handles as $handles) {
      $this->add_members_to_list($handles, $list_id);
    }

    $tweets = $this->get_statuses_from_list($list_id);
    
    $this->destroy_list($list_id);
    
    return $this->parse_tweets($tweets);
  }

  private function add_members_to_list($handles, $list_id) {
    $serialized_handles = implode(',', $handles);

    $response = $this->twitter_oauth->post('lists/members/create_all', [
      'list_id' => $list_id,
      'user_id' => $serialized_handles
    ]);

    $this->check_status_code('Unable to load members into list ' . $list_id);
  }
  
  private function get_statuses_from_list($list_id) {
    $tweets = $this->twitter_oauth->get('lists/statuses', [
      'list_id' => $list_id,
      'include_rts' => 'false'
    ]);

    $this->check_status_code('Unable to get statuses from members of list ' . $list_id);

    return $tweets;
  }
  
  private function parse_tweets($tweet_array) {
    $parsed_tweets = [];

    foreach($tweet_array as $tweet) {
      $parsed_tweet = $this->transfer_tweet_data($tweet);
      array_push($parsed_tweets, $parsed_tweet);
    }

    return $parsed_tweets;
  }

  private function check_status_code($error_message) {
    $status_code = $this->twitter_oauth->getLastHttpCode();

    if($status_code !== 200) {
      throw new Exception($error_message . '; Status code: ' . $status_code);
    }
  }

  private function batch_handles($handles, $count = 100) {
    $batch_handles = [];
    $batch_handle_count = 0;
    $batch_handle_group = [];

    foreach($handles as $handle) {
      array_push($batch_handle_group, $handle);
      $batch_handle_count++;

      if($batch_handle_count === 100) {
        array_push($batch_handles, $batch_handle_group);
        $batch_handle_count = 0;
        $batch_handle_group = [];
      }
    }

    if(count($batch_handle_group) > 0) {
      array_push($batch_handles, $batch_handle_group);
    }

    return $batch_handles;
  }

  /**
   * create_temporary_list
   *
   * @description Creates a Twitter list for the default application user and returns the list id
   * @return string
   */
  private function create_temporary_list() {
    $list_response = $this->twitter_oauth->post('lists/create', [
      'name' => 'temporary_list_' . $this->generateRandomString()
    ]);
    
    $this->check_status_code('Unable to create Twitter list');

    return $list_response->id;
  }

  public function destroy_list($list_id) {
    $response = $this->twitter_oauth->post('lists/destroy', [
      'list_id' => $list_id
    ]);

    $this->check_status_code('Unable to delete Twitter list');
  }

  private function generateRandomString($length = 10) {
    // This function was stolen from somewhere from StackOverflow. Sorry :(
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  public function transfer_tweet_data($input) {
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

    foreach($input->entities->urls as $url) {
      $entity_url = new EntityUrl();
      $entity_url->url = $url->url;
      $entity_url->expanded_url = $url->expanded_url;
      $entity_url->display_url = $url->display_url;

      array_push($tweet->entities->urls, $entity_url);
    }

    return $tweet;
  }

}