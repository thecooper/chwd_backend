<?php

namespace App\DataSources\Ballotpedia;

use \InvalidArgumentException;
use \Exception;

class ElectionNameGenerator {
  public static function generate($state_abbreviation, $general_election_date) {
    if($general_election_date === null) {
      throw new InvalidArgumentException("Could not generate election name because generate election date value is null.");
    }
    
    if($state_abbreviation === null) {
      throw new InvalidArgumentException("Could not generate election name because state abbreviation value is null.");
    }

    $district_name = StateLookup::lookup($state_abbreviation);

    if($district_name === null) {
      throw new Exception("Could not generate election name because state abbreviation value could not be translated.");
    }
    
    $general_election_parsed_date = null;

    try {
        $general_election_parsed_date = new \DateTime($general_election_date);
    } catch (Exception $ex) {
        throw new Exception('Unable to parse date for field general_election_date: ' . $general_election_date);
    }

    $election_year = date_format($general_election_parsed_date, 'Y');
    return "{$district_name} General Election {$election_year}";
  }
}