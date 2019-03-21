<?php

namespace App\BusinessLogic;

use App\DataLayer\Ballot\Ballot;

class BallotCandidateFilter {
  /**
   * filter_candidates_by_ballot_location
   *
   * @param Candidate[] $candidates
   * @param Ballot $ballot
   * @return Candidate[]
   */
  public function filter_candidates_by_ballot_location(array $candidates, Ballot $ballot) {
    $candidates_collection = collect($candidates);
    return collect(
      array_merge(
        $this->get_candidates_from_local($candidates_collection, $ballot->county),
        $this->get_candidates_from_congressional_district($candidates_collection, $ballot->congressional_district),
        $this->get_candidates_from_state($candidates_collection),
        $this->get_candidates_from_state_senate($candidates_collection, $ballot->state_legislative_district),
        $this->get_candidates_from_state_house($candidates_collection, $ballot->state_house_district),
        $this->get_candidates_from_county($candidates_collection, $ballot->county),
        $this->get_candidates_from_city($candidates_collection, $ballot->city)
      )
    )->toArray();
  }
  
  /**
   * @param Collection<Candidate>
   * @return Candidate[]
   */
  public function get_candidates_without_district($candidates_collection) {
    return $candidates_collection
        ->where('district_identifier', null)
        ->where('district_type', '<>', 'City')
        ->toArray();
  }

  public function get_candidates_from_local($candidates_collection, $district_id) {
    return $candidates_collection
          ->where('office_level', 'Local')
          ->filter(function($value, $key) use ($district_id) {
              return strpos($value->district, $district_id) !== false;
          })
          ->toArray();
  }

  public function get_candidates_from_state($candidates_collection) {
    return $candidates_collection
          ->where('office_level', 'State')
          ->where('district_type', 'State')
          ->toArray();
  }

  /**
   * @param Collection<Candidate>
   * @return Candidate[]
   */
  public function get_candidates_from_congressional_district($candidates_collection, $district_id) {
      if($district_id == null) { return []; }
      
      return $candidates_collection
          ->where('district_type', 'Congress')
          ->where('office_level', 'Federal')
          ->where('district_identifier', $district_id)->toArray();
  }

  /**
   * @param Collection<Candidate>
   * @return Candidate[]
   */
  public function get_candidates_from_state_senate($candidates_collection, $district_id) {
      if($district_id == null) { return []; }
      
      return $candidates_collection
          ->where('office_level', 'State')
          ->where('district_type', 'State Legislative (Upper)')
          ->where('district_identifier', $district_id)
          ->toArray();
  }

  /**
   * @param Collection<Candidate>
   * @return Candidate[]
   */
  public function get_candidates_from_state_house($candidates_collection, $district_id) {
      if($district_id == null) { return []; }
      
      return $candidates_collection
          ->where('office_level', 'State')
          ->where('district_type', 'State Legislative (Lower)')
          ->where('district_identifier', $district_id)
          ->toArray();
  }

  /**
   * @param Collection<Candidate>
   * @return Candidate[]
   */
  public function get_candidates_from_city($candidates_collection, $city) {
      return $candidates_collection
          ->where('district_type', 'City')
          ->where('district', $city)
          ->toArray();
  }

  /**
   * @param Collection<Candidate>
   * @return Candidate[]
   */
  public function get_candidates_from_county($candidates_collection, $district_id) {
      if($district_id == null) { return []; }
      
      return $candidates_collection
          ->where('office_level', 'Local')
          ->filter(function($value, $key) use ($district_id) {
              return strpos(trim(str_replace("County", "", $value->district)), $district_id) !== false;
          })
          ->toArray();
  }

  /**
   * get_winner_candidates
   *
   * @param Candidate[] $candidates
   * @return Candidate[]
   */
  public function get_winner_candidates(array $candidates) {
    return collect($candidates)->where('election_status', 'Won')->toArray();
  }
}