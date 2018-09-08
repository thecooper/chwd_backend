<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\UserBallot;

class UserBallotCandidatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $ballot_id)
    {
        $ballot = UserBallot::find($ballot_id);

        $user_ballot_candidate_ids = DB::table('user_ballot_candidates')
            ->select('candidate_id')
            ->where('user_ballot_id', $ballot_id)
            ->get()
            ->map(function($candidate_id_object) {
                return $candidate_id_object->candidate_id;
            });

        return response()->json($user_ballot_candidate_ids, 200);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $ballot_id, $id)
    {
        $existing_ballot_candidate_link = $this->get_existing_user_ballot_candidate_link($ballot_id, $id)
            ->first();

        if($existing_ballot_candidate_link != null) {
            return response()->json(null, 200);
        }
        
        DB::table('user_ballot_candidates')->insert([
            'user_ballot_id'=>$ballot_id,
            'candidate_id'=>$id
        ]);

        return response()->json(null, 201);
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
