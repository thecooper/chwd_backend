<?php

namespace App\BusinessLogic;

use Illuminate\Support\Collection;

use App\BusinessLogic\Models\Candidate;
use App\DataLayer\DataSource\DataSourcePriority;

use \Exception;

class CandidateFragmentCombiner {

  /**
   * combine
   *
   * @param App\BusinessLogic\Models\CandidateFragment[] $fragments
   * @param App\DataLayer\DataSource\DataSourcePriority[] $data_source
   * @return App\BusinessLogic\Models\Candidate
   */
  public function combine(array $fragments, array $priorities) {
    $fragments_collection = collect($fragments);

    $compiled_candidate = new Candidate();
    
    if(count($priorities) < 1) {
      throw new Exception("Cannot combine candidate fragments because no priority order exists");
    } 
    
    foreach($priorities as $priority) {
      if($fragments_collection->contains('data_source_id', $priority["data_source_id"])) {
        $priority_fragment = $fragments_collection->firstWhere('data_source_id', $priority["data_source_id"]);

        if(isset($priority_fragment["name"])) {
          $compiled_candidate->name = $priority_fragment["name"];
        }

        if(isset($priority_fragment["party_affiliation"])) {
          $compiled_candidate->party_affiliation = $priority_fragment["party_affiliation"];
        }

        if(isset($priority_fragment["election_status"])) {
          $compiled_candidate->election_status = $priority_fragment["election_status"];
        }

        if(isset($priority_fragment["office"])) {
          $compiled_candidate->office = $priority_fragment["office"];
        }

        if(isset($priority_fragment["office_level"])) {
          $compiled_candidate->office_level = $priority_fragment["office_level"];
        }

        if(isset($priority_fragment["is_incumbent"])) {
          $compiled_candidate->is_incumbent = $priority_fragment["is_incumbent"];
        }

        if(isset($priority_fragment["district_type"])) {
          $compiled_candidate->district_type = $priority_fragment["district_type"];
        }

        if(isset($priority_fragment["district"])) {
          $compiled_candidate->district = $priority_fragment["district"];
        }

        if(isset($priority_fragment["district_identifier"])) {
          $compiled_candidate->district_identifier = $priority_fragment["district_identifier"];
        }

        if(isset($priority_fragment["ballotpedia_url"])) {
          $compiled_candidate->ballotpedia_url = $priority_fragment["ballotpedia_url"];
        }

        if(isset($priority_fragment["website_url"])) {
          $compiled_candidate->website_url = $priority_fragment["website_url"];
        }

        if(isset($priority_fragment["donate_url"])) {
          $compiled_candidate->donate_url = $priority_fragment["donate_url"];
        }

        if(isset($priority_fragment["facebook_profile"])) {
          $compiled_candidate->facebook_profile = $priority_fragment["facebook_profile"];
        }

        if(isset($priority_fragment["twitter_handle"])) {
          $compiled_candidate->twitter_handle = $priority_fragment["twitter_handle"];
        }

        if(isset($priority_fragment["election_id"])) {
          $compiled_candidate->election_id = $priority_fragment["election_id"];
        }
      }
    }

    return $compiled_candidate;
  }
}