<?php

namespace App\BusinessLogic\Repositories;

use App\BusinessLogic\Models\Tweet;
use App\DataSources\TwitterDataSource;
use Illuminate\Support\Facades\Cache;
use App\BusinessLogic\Serializer\TweetJsonSerializer;

class TweetRepository {

  private $twitter_data_source;
  private $serializer;
  
  public function __construct(TwitterDataSource $twitter_data_source, TweetJsonSerializer $serializer) {
    $this->twitter_data_source = $twitter_data_source;
    $this->serializer = $serializer;
  }

  public function get_tweets_by_handles(array $handles) {
    $tweets = [];
    $uncached_handles = [];
    
    foreach($handles as $handle) {
      $cache_key = 'twitter_handle_' . $handle;
      
      if(!Cache::has($cache_key)) {
        array_push($uncached_handles, $handle);
      } else {
        $serialized_tweets = Cache::get($cache_key);
        $deserialized_tweets = explode('&|', $serialized_tweets);

        foreach($deserialized_tweets as $serialized_tweet) {
          $parsed_tweet = $this->serializer->parse($serialized_tweet);
          array_push($tweets, $parsed_tweet);
        }
      }
    }

    $fresh_tweets = $this->twitter_data_source->get_tweets_by_handles($uncached_handles);

    if(count($fresh_tweets) > 0) {
      $tweets = array_merge($tweets, $fresh_tweets);
      $this->cache_tweets($fresh_tweets);
    }

    return $tweets;
  }

  /**
   * cache_tweets
   *
   * @param Tweet[] $tweets
   * @return void
   */
  private function cache_tweets(array $tweets) {
    $tweet_collection = collect($tweets);

    $tweets_grouped_by_screen_name = $tweet_collection->groupBy('twitter_user.screen_name');
    
    
    foreach($tweets_grouped_by_screen_name as $screen_name => $tweets) {
      $serialized_tweets = [];
      
      foreach($tweets as $tweet) {
        $serialized_tweet = $this->serializer->serialize($tweet);
        array_push($serialized_tweets, $serialized_tweet);
      }

      $serialized_tweets_string = implode('&|', $serialized_tweets);

      $cache_key = 'twitter_handle_' . $screen_name;
      Cache::put($cache_key, $this->serializer->serialize($serialized_tweets_string), 1440);
    }
    
  }
}