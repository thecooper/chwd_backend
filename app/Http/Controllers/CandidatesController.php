<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidate\ConsolidatedCandidate;

class CandidatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (isset($request)) {
            $candidates = ConsolidatedCandidate::take(100)->get();
            return response()->json($candidates, 200);
        } else {
            return "Could not find request object";
        }
    }

    public function show($election_id)
    {
        if(isset($request)) {

        }
    }
}
