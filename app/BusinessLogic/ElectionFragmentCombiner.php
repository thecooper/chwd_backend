<?php

namespace App\BusinessLogic;

use Illuminate\Support\Collection;

use App\BusinessLogic\Models\Election;
use App\DataLayer\DataSource\DataSourcePriority;

class ElectionFragmentCombiner {

  /**
   * combine
   *
   * @param App\BusinessLogic\Models\ElectionFragment[] $fragments
   * @param App\DataLayer\DataSource\DataSourcePriority[] $data_source
   * @return App\BusinessLogic\Models\Election
   */
  public function combine(array $fragments, array $priorities) {
    $fragments_collection = collect($fragments);

    $compiled_election = new Election();
    
    foreach($priorities as $priority) {
      if($fragments_collection->contains('data_source_id', $priority["data_source_id"])) {
        $priority_fragment = $fragments_collection->firstWhere('data_source_id', $priority["data_source_id"]);

        $compiled_election->state_abbreviation = $priority_fragment["state_abbreviation"];
        $compiled_election->name = $priority_fragment["name"];
        $compiled_election->primary_election_date = $priority_fragment["primary_election_date"];
        $compiled_election->general_election_date = $priority_fragment["general_election_date"];
        $compiled_election->runoff_election_date = $priority_fragment["runoff_election_date"];
      }
    }

    return $compiled_election;
  }
}