<?php

namespace App\BusinessLogic;

use App\DataLayer\Ballot\Ballot;

class BallotCandidateFilter {
  public function filter_candidates_by_ballot_location($candidates, Ballot $ballot) {
    return collect(
        array_merge(
          $this->get_candidates_from_local($candidates, $ballot->county),
          $this->get_candidates_from_congressional_district($candidates, $ballot->congressional_district),
          $this->get_candidates_from_state($candidates),
          $this->get_candidates_from_state_senate($candidates, $ballot->state_legislative_district),
          $this->get_candidates_from_state_house($candidates, $ballot->state_house_district),
          $this->get_candidates_from_county($candidates, $ballot->county),
          $this->get_candidates_from_city($candidates, $ballot->city)
        )
    );
  }
  
  /**
   * @param Collection<ConsolidatedCandidate>
   * @return Collection<ConsolidatedCandidate>
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
   * @param Collection<ConsolidatedCandidate>
   * @return Collection<ConsolidatedCandidate>
   */
  public function get_candidates_from_congressional_district($candidates_collection, $district_id) {
      if($district_id == null) { return []; }
      
      return $candidates_collection
          ->where('district_type', 'Congress')
          ->where('office_level', 'Federal')
          ->where('district_identifier', $district_id)->toArray();
  }

  /**
   * @param Collection<ConsolidatedCandidate>
   * @return Collection<ConsolidatedCandidate>
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
   * @param Collection<ConsolidatedCandidate>
   * @return Collection<ConsolidatedCandidate>
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
   * @param Collection<ConsolidatedCandidate>
   * @return Collection<ConsolidatedCandidate>
   */
  public function get_candidates_from_city($candidates_collection, $city) {
      return $candidates_collection
          ->where('district_type', 'City')
          ->where('district', $city)
          ->toArray();
  }

  /**
   * @param Collection<ConsolidatedCandidate>
   * @return Collection<ConsolidatedCandidate>
   */
  public function get_candidates_from_county($candidates_collection, $district_id) {
      if($district_id == null) { return []; }
      
      return $candidates_collection
          ->where('office_level', 'Local')
          ->filter(function($value, $key) use ($district_id) {
              return strpos(trim(str_replace("County", "", $value->district_name)), $district_id) !== false;
          })
          ->toArray();
  }
}