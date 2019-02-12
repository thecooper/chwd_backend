<?php

namespace App\DataSources\Ballotpedia;

class ElectionNameGenerator {
  public static function generate($state_abbreviation, $general_election_date) {
    $district_name = StateLookup::lookup($state_abbreviation);

    $general_election_date = null;
    $general_election_date_value = $general_election_date;

    try {
        $general_election_date = new \DateTime($general_election_date_value);
    } catch (\Exception $ex) {
        throw new \Exception('Unable to parse date for field general_election_date: ' . $general_election_date_value);
    }

    $election_year = date_format($general_election_date, 'Y');
    return "{$district_name} General Election {$election_year}";
  }
}