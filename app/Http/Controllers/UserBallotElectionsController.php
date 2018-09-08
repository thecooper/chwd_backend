<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserBallot;
use App\Models\Election\ConsolidatedElection;

class UserBallotElectionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, UserBallot $ballot)
    {
        $elections = ConsolidatedElection::where('state_abbreviation', $ballot->state_abbreviation)->get();

        $election_candidates = array();

        foreach($elections as $election) {
            $election_candidates = array_merge($election_candidates, 
                $this->get_candidates_from_state_level($election->candidates),
                $this->get_candidates_from_senate($election->candidates),
                $this->get_candidates_from_congressional_district($election->candidates, $ballot->congressional_district),
                $this->get_candidates_from_state_senate($election->candidates, $ballot->state_legislative_district),
                $this->get_candidates_from_state_house($election->candidates, $ballot->state_house_district)
            );
        }

        // dd($election_candidates);
        return response()->json($election_candidates, 200);
    }

    private function get_candidates_from_state_level($candidates) {
        return $candidates->where('district_type', 'State')->toArray();
    }

    private function get_candidates_from_senate($candidates) {
        return $candidates->where('office_level', 'Federal')->where('district_type', 'State')->toArray();
    }

    private function get_candidates_from_congressional_district($candidates, $district_id) {
        return $candidates
            ->where('district_type', 'Congress')
            ->where('office_level', 'Federal')
            ->where('district_identifier', $district_id)->toArray();
    }

    private function get_candidates_from_state_senate($candidates, $district_id) {
        return $candidates
            ->where('office_level', 'State')
            ->where('district_type', 'State Legislative (Upper)')
            ->where('district_identifier', $district_id)
            ->toArray();
    }

    private function get_candidates_from_state_house($candidates, $district_id) {
        return $candidates
            ->where('office_level', 'State')
            ->where('district_type', 'State Legislative (Lower)')
            ->where('district_identifier', $district_id)
            ->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
