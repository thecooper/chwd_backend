<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\DataSources\Ballotpedia\ElectionNameGenerator;

class ElectionNameGeneratorTest extends TestCase
{
    public function testCanGenerateName() {
      // Arrange
      $abbreviation = 'CO';
      $general_election_date = '2018-11-06';

      // Act
      $result = ElectionNameGenerator::generate($abbreviation, $general_election_date);

      // Assert
      $this->assertEquals($result, 'Colorado General Election 2018');
    }

    /**
     * testNullAbbreviationValue
     *
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Could not generate election name because state abbreviation value is null.
     */
    public function testNullAbbreviationValue() {
      // Arrange
      $abbreviation = null;
      $general_election_date = '2018-11-06';

      // Act
      $result = ElectionNameGenerator::generate($abbreviation, $general_election_date);
    }

    /**
     * testNullAbbreviationValue
     *
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Could not generate election name because state abbreviation value could not be translated.
     */
    public function testAbbreviationCannotBeTranslated() {
      // Arrange
      $abbreviation = 'HZI';
      $general_election_date = '2018-11-06';

      // Act
      $result = ElectionNameGenerator::generate($abbreviation, $general_election_date);
    }

    /**
     * testNullElectionDateValue
     *
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Could not generate election name because generate election date value is null.
     */
    public function testNullElectionDateValue() {
      // Arrange
      $abbreviation = 'CA';
      $general_election_date = null;

      // Act
      $result = ElectionNameGenerator::generate($abbreviation, $general_election_date);
    }

    /**
     * testCannotParseDateValue
     *
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Unable to parse date for field general_election_date: 1234abcd
     */
    public function testCannotParseDateValue() {
      // Arrange
      $abbreviation = 'CA';
      $general_election_date = '1234abcd';

      // Act
      $result = ElectionNameGenerator::generate($abbreviation, $general_election_date);
    }
}
