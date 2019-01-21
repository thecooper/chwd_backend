<?php

namespace App\BusinessLogic\Repositories;

use App\BusinessLogic\Models\Tweet;
use App\DataSources\TwitterDataSource;
use Illuminate\Support\Facades\Cache;

class TweetRepository {

  private $twitter_data_source;
  
  public function __construct(TwitterDataSource $twitter_data_source) {
    $this->twitter_data_source = $twitter_data_source;
  }

  public function get_tweets_by_handles(array $handles) {
    $tweets = [];
    $uncached_handles = [];
    
    foreach($handles as $handle) {
      $cache_key = 'twitter_handle_' . $handle;
      
      if(!Cache::has($cache_key)) {
        array_push($uncached_handles, $handle);
      } else {
        array_push($tweets, Cache::get($cache_key));
      }
    }

    $fresh_tweets = $this->twitter_data_source->get_tweets_by_handles($uncached_handles);

    if(count($fresh_tweets) > 0) {
      $tweets = array_merge($tweets, $fresh_tweets);
      $this->cache_tweets($fresh_tweets);
    }

    dd($uncached_handles);

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
      $cache_key = 'twitter_handle_' . $screen_name;
      Cache::put($cache_key, json_encode($tweets), 1440);
    }
  }
}