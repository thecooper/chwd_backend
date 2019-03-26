<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BallotCandidatesWinnersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Ballot $ballot)
    {   
        $candidates = collect($this->ballot_manager->get_winners_of_last_elections($ballot))
            ->groupBy('district_type')
            ->map(function($value) {
                return $value->groupBy('office');
            });

        return response()->json($candidates, 200);
    }
}
