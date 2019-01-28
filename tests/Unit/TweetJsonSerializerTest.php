<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\BusinessLogic\Serializer\TweetJsonSerializer;
use App\BusinessLogic\Models\Tweet;
use App\BusinessLogic\Models\TwitterEntity;
use App\BusinessLogic\Models\TwitterUser;
use App\BusinessLogic\Models\EntityUrl;

class TweetJsonSerializerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCanSerializeTweet()
    {
      // Arrange
      $tweet = $this->generate_tweet();
      
      $serializer = new TweetJsonSerializer();

      // Act
      $result = $serializer->serialize($tweet);

      // Assert
      // Assert base properties serialized
      $this->assertNotEmpty($result);
      $this->assertEquals(preg_match("/\"id\"\:1234/", $result), 1, 'Expected id to equal 1234');
      $this->assertEquals(preg_match("/\"text\"\:\"Some random tweet text\"/", $result), 1, 'Expected text to be \"Some random tweet text\"');
      $this->assertEquals(preg_match("/\"source\"\:\"source text here\"/", $result), 1, 'Expected source to be \"source text here\"');
      $this->assertEquals(preg_match('%\"created_at\"\:\"1\\\/18\\\/1988\"%', $result), 1, 'Expected created_at to be "1\/18\/1988"');

      // Assert twitter user properties serialized
      $this->assertContains('"id":4321', $result);
      $this->assertContains('"name":"John Smith"', $result);
      $this->assertContains('"screen_name":"jsmithy"', $result);
      $this->assertContains('"location":"Global"', $result);
      $this->assertContains('"description":"Here is a description"', $result);
      $this->assertContains('"url":"https:\/\/bit.ly\/1G8G8efz1"', $result);
      $this->assertContains('"profile_image_url":"http:\/\/imgur.com\/somelink"', $result);
      $this->assertContains('"profile_image_url_https":"https:\/\/imgur.com\/somelink"', $result);
      
      // Assert entities properties serialized
      $this->assertContains('"entities":{"urls":[{"url":"url1","expanded_url":"url2","display_url":"url3"}]}', $result);
    }

    public function testCanDeserializeTweet() {
      // Arrange
      $serialized_tweet = '{'.
        '"id":"4587",'.
        '"created_at":"1\/19\/2018",'.
        '"text":"Some placeholder text here",'.
        '"source":"example source",'.
        '"entities":{'.
          '"urls":['.
            '{'.
              '"url":"URL1",'.
              '"expanded_url":"URL2",'.
              '"display_url":"URL3"'.
            '}'.
          ']'.
        '},'.
        '"twitter_user":{'.
          '"id": 4321,'.
          '"name":"Benjamin Franklin",'.
          '"screen_name":"bfranklin",'.
          '"location":"Global",'.
          '"description":"A very good description lies here",'.
          '"url":"https:\/\/bit.ly\/1G8G8efz1",'.
          '"profile_image_url":"http:\/\/imgur.com\/somelink",'.
          '"profile_image_url_https":"https:\/\/imgur.com\/somelink"'.
        '}'.
      '}';

      $serializer = new TweetJsonSerializer();

      // Act
      $parsed_tweet = $serializer->parse($serialized_tweet);

      // Assert
      $this->assertNotEmpty($parsed_tweet);
      $this->assertEquals($parsed_tweet->id, 4587);
      $this->assertEquals($parsed_tweet->created_at, '1/19/2018');
      $this->assertEquals($parsed_tweet->text, 'Some placeholder text here');
      $this->assertEquals($parsed_tweet->source, 'example source');
      
      $this->assertNotEmpty($parsed_tweet->twitter_user);
      $this->assertEquals($parsed_tweet->twitter_user->id, 4321);
      $this->assertEquals($parsed_tweet->twitter_user->name, 'Benjamin Franklin');
      $this->assertEquals($parsed_tweet->twitter_user->screen_name, 'bfranklin');
      $this->assertEquals($parsed_tweet->twitter_user->location, 'Global');
      $this->assertEquals($parsed_tweet->twitter_user->description, 'A very good description lies here');
      $this->assertEquals($parsed_tweet->twitter_user->url, 'https://bit.ly/1G8G8efz1');
      $this->assertEquals($parsed_tweet->twitter_user->profile_image_url, 'http://imgur.com/somelink');
      $this->assertEquals($parsed_tweet->twitter_user->profile_image_url_https, 'https://imgur.com/somelink');

      $this->assertNotEmpty($parsed_tweet->entities);
      $this->assertEquals(count($parsed_tweet->entities->urls), 1);

      $this->assertEquals($parsed_tweet->entities->urls[0]->url, 'URL1');
      $this->assertEquals($parsed_tweet->entities->urls[0]->expanded_url, 'URL2');
      $this->assertEquals($parsed_tweet->entities->urls[0]->display_url, 'URL3');
    }

    public function testCanDeserialzeTweetWithNoEntities() {
      // Arrange
      $serialized_tweet = '{'.
        '"id":"4587",'.
        '"created_at":"1\/19\/2018",'.
        '"text":"Some placeholder text here",'.
        '"source":"example source",'.
        '"entities":[],'.
        '"twitter_user":{'.
          '"id": 4321,'.
          '"name":"Benjamin Franklin",'.
          '"screen_name":"bfranklin",'.
          '"location":"Global",'.
          '"description":"A very good description lies here",'.
          '"url":"https:\/\/bit.ly\/1G8G8efz1",'.
          '"profile_image_url":"http:\/\/imgur.com\/somelink",'.
          '"profile_image_url_https":"https:\/\/imgur.com\/somelink"'.
        '}'.
      '}';

      $serializer = new TweetJsonSerializer();

      // Act
      $parsed_tweet = $serializer->parse($serialized_tweet);

      // Assert
      $this->assertNotEmpty($parsed_tweet);
      $this->assertEquals($parsed_tweet->id, 4587);
      $this->assertEquals($parsed_tweet->created_at, '1/19/2018');
      $this->assertEquals($parsed_tweet->text, 'Some placeholder text here');
      $this->assertEquals($parsed_tweet->source, 'example source');
      
      $this->assertNotEmpty($parsed_tweet->twitter_user);
      $this->assertEquals($parsed_tweet->twitter_user->id, 4321);
      $this->assertEquals($parsed_tweet->twitter_user->name, 'Benjamin Franklin');
      $this->assertEquals($parsed_tweet->twitter_user->screen_name, 'bfranklin');
      $this->assertEquals($parsed_tweet->twitter_user->location, 'Global');
      $this->assertEquals($parsed_tweet->twitter_user->description, 'A very good description lies here');
      $this->assertEquals($parsed_tweet->twitter_user->url, 'https://bit.ly/1G8G8efz1');
      $this->assertEquals($parsed_tweet->twitter_user->profile_image_url, 'http://imgur.com/somelink');
      $this->assertEquals($parsed_tweet->twitter_user->profile_image_url_https, 'https://imgur.com/somelink');

      $this->assertEmpty($parsed_tweet->entities->urls);
    }

    public function testCanDeserializeTweetWithNoUrls() {
      // Arrange
      $serialized_tweet = '{'.
        '"id":"4587",'.
        '"created_at":"1\/19\/2018",'.
        '"text":"Some placeholder text here",'.
        '"source":"example source",'.
        '"entities":{'.
          '"urls":[],'.
          '"somethingElse":[{"property":"abcd"}]'.
        '},'.
        '"twitter_user":{'.
          '"id": 4321,'.
          '"name":"Benjamin Franklin",'.
          '"screen_name":"bfranklin",'.
          '"location":"Global",'.
          '"description":"A very good description lies here",'.
          '"url":"https:\/\/bit.ly\/1G8G8efz1",'.
          '"profile_image_url":"http:\/\/imgur.com\/somelink",'.
          '"profile_image_url_https":"https:\/\/imgur.com\/somelink"'.
        '}'.
      '}';

      $serializer = new TweetJsonSerializer();

      // Act
      $parsed_tweet = $serializer->parse($serialized_tweet);

      // Assert
      $this->assertNotEmpty($parsed_tweet);
      $this->assertEquals($parsed_tweet->id, 4587);
      $this->assertEquals($parsed_tweet->created_at, '1/19/2018');
      $this->assertEquals($parsed_tweet->text, 'Some placeholder text here');
      $this->assertEquals($parsed_tweet->source, 'example source');
      
      $this->assertNotEmpty($parsed_tweet->twitter_user);
      $this->assertEquals($parsed_tweet->twitter_user->id, 4321);
      $this->assertEquals($parsed_tweet->twitter_user->name, 'Benjamin Franklin');
      $this->assertEquals($parsed_tweet->twitter_user->screen_name, 'bfranklin');
      $this->assertEquals($parsed_tweet->twitter_user->location, 'Global');
      $this->assertEquals($parsed_tweet->twitter_user->description, 'A very good description lies here');
      $this->assertEquals($parsed_tweet->twitter_user->url, 'https://bit.ly/1G8G8efz1');
      $this->assertEquals($parsed_tweet->twitter_user->profile_image_url, 'http://imgur.com/somelink');
      $this->assertEquals($parsed_tweet->twitter_user->profile_image_url_https, 'https://imgur.com/somelink');

      $this->assertNotEmpty($parsed_tweet->entities);
      $this->assertEquals(count($parsed_tweet->entities), 1);
      $this->assertEmpty($parsed_tweet->entities->urls);
    }


    public function testSerializeTweetRoundTrip() {
      // Arrange
      $tweet = $this->generate_tweet();
      $serializer = new TweetJsonSerializer();
      
      // Act
      $serialized_tweet = $serializer->serialize($tweet);

      $result = $serializer->parse($serialized_tweet);

      // Assert
      // Assert base properties serialized
      $this->assertNotEmpty($result);
      $this->assertEquals($result->id, $tweet->id);
      $this->assertEquals($result->text, $tweet->text);
      $this->assertEquals($result->source, $tweet->source);
      $this->assertEquals($result->created_at, $tweet->created_at);

      // Assert twitter user properties serialized
      $this->assertEquals($result->twitter_user->id, $tweet->twitter_user->id);
      $this->assertEquals($result->twitter_user->name, $tweet->twitter_user->name);
      $this->assertEquals($result->twitter_user->screen_name, $tweet->twitter_user->screen_name);
      $this->assertEquals($result->twitter_user->location, $tweet->twitter_user->location);
      $this->assertEquals($result->twitter_user->description, $tweet->twitter_user->description);
      $this->assertEquals($result->twitter_user->url, $tweet->twitter_user->url);
      $this->assertEquals($result->twitter_user->profile_image_url, $tweet->twitter_user->profile_image_url);
      $this->assertEquals($result->twitter_user->profile_image_url_https, $tweet->twitter_user->profile_image_url_https);
      
      // Assert entities properties serialized
      $this->assertEquals(count($result->entities), 1);
      $this->assertEquals(count($result->entities->urls), 1);

      $this->assertEquals($result->entities->urls[0]->url, $tweet->entities->urls[0]->url);
      $this->assertEquals($result->entities->urls[0]->expanded_url, $tweet->entities->urls[0]->expanded_url);
      $this->assertEquals($result->entities->urls[0]->display_url, $tweet->entities->urls[0]->display_url);
    }

    private function generate_tweet() {
      $twitter_user = new TwitterUser();
      $twitter_user->id = 4321;
      $twitter_user->name = 'John Smith';
      $twitter_user->screen_name = 'jsmithy';
      $twitter_user->location = 'Global';
      $twitter_user->description = 'Here is a description';
      $twitter_user->url = 'https://bit.ly/1G8G8efz1';
      $twitter_user->profile_image_url = 'http://imgur.com/somelink';
      $twitter_user->profile_image_url_https = 'https://imgur.com/somelink';
      
      $twitter_entity = new TwitterEntity();
      $twitter_entity_url = new EntityUrl();
      $twitter_entity_url->url = 'url1';
      $twitter_entity_url->expanded_url = 'url2';
      $twitter_entity_url->display_url = 'url3';
      $twitter_entity->urls = [$twitter_entity_url];
      
      $tweet = new Tweet();
      $tweet->id = 1234;
      $tweet->created_at = '1/18/1988';
      $tweet->text = 'Some random tweet text';
      $tweet->source = 'source text here';
      $tweet->entities = $twitter_entity;
      $tweet->twitter_user = $twitter_user;

      return $tweet;
    }
}
