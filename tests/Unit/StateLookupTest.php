<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\DataSources\Ballotpedia\StateLookup;

class StateLookupTest extends TestCase
{
    public function testCanParseStateAbbreviation() {
      // Arrange
      $abbreviation = 'DE';

      // Act
      $result = StateLookup::lookup($abbreviation);

      // Assert
      $this->assertEquals($result, 'Delaware');
    }

    public function testCanHandleNullValue() {
      // Arrange
      $abbreviation = null;

      // Act
      $result = StateLookup::lookup($abbreviation);

      // Assert
      $this->assertEquals($result, null);
    }

    public function testReturnNullWhenNotFound() {
      // Arrange
      $abbreviation = 'NZ';

      // Act
      $result = StateLookup::lookup($abbreviation);

      // Assert
      $this->assertEquals($result, null);
    }
}
