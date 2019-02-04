<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\BusinessLogic\Repositories\TweetRepository;
use App\BusinessLogic\Serializer\TweetJsonSerializer;
use App\BusinessLogic\Models\Tweet;

class TweetRepositoryTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCallsTwitterDatasource()
    {
      // Arrange
      $twitter_api_mock = $this->createMock('App\DataSources\TwitterDataSource');

      $serializer = new TweetJsonSerializer();
      
      $tweet_repository = new TweetRepository($twitter_api_mock, $serializer);
      
      $handles = ['someTwitterHandle'];
      $normalizedHandles = ['sometwitterhandle'];
      
      // Assert
      $twitter_api_mock->expects($this->once())
        ->method('get_tweets_by_handles')
        ->with($this->equalTo($normalizedHandles))
        ->willReturn([
          new Tweet(),
        ]);

      // Act
      $tweet_repository->get_tweets_by_handles($handles);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testHandleBatching()
    {
      $faker = $this->app->make(\Faker\Generator::class);
      
      // Arrange
      $twitter_api_mock = $this->createMock('App\DataSources\TwitterDataSource');

      $serializer = new TweetJsonSerializer();
      
      $tweet_repository = new TweetRepository($twitter_api_mock, $serializer);
      
      $handles = [];
      $normalizedHandles = [];

      for($i = 1; $i <= 120; $i++) {
        $username = $faker->userName;
        array_push($handles, $username);
        array_push($normalizedHandles, strtolower($username));
      }
      
      // Assert
      $twitter_api_mock->expects($this->exactly(2))
        ->method('get_tweets_by_handles')
        ->withConsecutive(
          [$this->equalTo(array_slice($normalizedHandles, 0, 100))],
          [$this->equalTo(array_slice($normalizedHandles, 100, 20))]
        )
        ->will($this->onConsecutiveCalls([], []));

      // Act
      $tweet_repository->get_tweets_by_handles($handles);
    }
}
