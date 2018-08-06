<?php

namespace App\Http\Controllers;

use App\Consolidators\CandidateConsolidator;
use Illuminate\Http\Request;

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
            // $user = Auth::user();

            $consolidator = new CandidateConsolidator();

            $consolidator->Consolidate('Al Carlson');

            //TODO: Use Consolidator to get flattened version of data

            return response()->html("Made it here!", 200);
        } else {
            return "Could not find request object";
        }
    }

    public function show($id)
    {
        //
    }
}
