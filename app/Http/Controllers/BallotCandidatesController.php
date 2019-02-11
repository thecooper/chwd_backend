<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

use App\DataLayer\Ballot\Ballot;
use App\BusinessLogic\BallotManager;
use App\BusinessLogic\Repositories\CandidateRepository;

class BallotCandidatesController extends Controller
{
    private $ballot_manager;
    private $candidate_repository;
    
    public function __construct(BallotManager $ballot_manager, CandidateRepository $candidate_repository) {
        $this->ballot_manager = $ballot_manager;
        $this->candidate_repository = $candidate_repository;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Ballot $ballot_id)
    {   
        $candidates = collect($this->ballot_manager->get_candidates_from_ballot($ballot_id))
            ->groupBy('district_type')
            ->map(function($value) {
                return $value->groupBy('office');
            });

        return response()->json($candidates, 200);
    }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function store(Request $request)
    // {
    //     //
    // }

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function show($id)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Ballot $ballot
     * @param  int $id - ID of the candidate to select
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $ballot_id, $id)
    {
      $ballot = Ballot::find($ballot_id);
      $candidate = $this->candidate_repository->get($id);
      
      if($candidate == null) {
          return response()->json("Candidate not found", 404);
      }

      $this->ballot_manager->select_candidate($ballot, $candidate);

      return Response::make(null, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $ballot_id, $id)
    {
        $existing_ballot_candidate_link_query = $existing_ballot_candidate_link = $this->get_existing_user_ballot_candidate_link($ballot_id, $id);
        $existing_ballot_candidate_link = $existing_ballot_candidate_link_query->first();

        if($existing_ballot_candidate_link == null) {
            return response()->json('specified link does not exist', 404);
        }

        $existing_ballot_candidate_link_query->delete();

        return response()->json(null, 202);
    }

    private function get_existing_user_ballot_candidate_link($ballot_id, $candidate_id) {
        return DB::table('user_ballot_candidates')
            ->where('user_ballot_id', $ballot_id)
            ->where('candidate_id', $candidate_id);
    }
}
