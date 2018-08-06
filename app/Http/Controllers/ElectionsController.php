<?php

namespace App\Http\Controllers;

use App\Models\Election\ConsolidatedElection;
use App\Models\Election\Election;
use App\Models\Election\ElectionConsolidator;
use Illuminate\Http\Request;

class ElectionsController extends Controller
{

    protected $consolidator;

    public function __construct(ElectionConsolidator $consolidator)
    {
        $this->consolidator = $consolidator;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (isset($request)) {
            $elections = ConsolidatedElection::all();

            return response()->json($elections, 200);
        } else {
            return "Could not find request object";
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->has('name') && $request->has('data_source_id')) {
            $election_name = $request->input('name');
            $election_data_source_id = $request->input('data_source_id');

            $election = Election::createOrUpdate($request->all());

            $updated_election = $this->consolidator->consolidate($election_name);

            if (is_array($updated_election) && isset($updated_election->error)) {
                return response()->text($updated_election->error, 404);
            }

            return response()->json($updated_election, 201);
        } else {
            return response()->json(array("error" => "Missing required parameter"), 400);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $election = ConsolidatedElection::find($id);
        if ($election != null) {
            return response()->json($election, 200);
        } else {
            return response()->make("", 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
