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
        $state_abbreviation = $request->query('state', null);
        
        $elections = new ConsolidatedElection();
        
        if($state_abbreviation != null) {
            $elections = $elections->where('state_abbreviation', $state_abbreviation);
        }
        
        return response()->json($elections->get(), 200);
    }

    public function races(Request $request, $id) {
        $election = ConsolidatedElection::find($id);
        
        // var_dump($election->candidates);
        return response()->json($this->aggregate_races_from_candidates($election->candidates), 200);
    }

    /**
     * @param Builder builder: a Laravel Builder sourced from the ConsolidatedElection model
     * @return Builder builder: the same laravel builder passed in but with additional qualifiers appended on
     */
    private function aggregate_races_from_candidates($candidates_collection) {
        return $candidates_collection
            ->map(function($candidate) {
                return preg_replace('/ District ([\d]+|\w\s)/', '', $candidate->office);
            })
            ->unique();
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
        // if ($request->has('name') && $request->has('data_source_id')) {
        //     $election_name = $request->input('name');
        //     $election_data_source_id = $request->input('data_source_id');

        //     $election = Election::createOrUpdate($request->all());

        //     $updated_election = $this->consolidator->consolidate($election_name);

        //     if (is_array($updated_election) && isset($updated_election->error)) {
        //         return response()->text($updated_election->error, 404);
        //     }

        //     return response()->json($updated_election, 201);
        // } else {
        //     return response()->json(array("error" => "Missing required parameter"), 400);
        // }

        throw new Exception("Not implemented");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $election = ConsolidatedElection::find($id);

        if($election == null) {
            return response()->make("", 404);
        }
        
        $include_fields = $request->query('include_fields', null);

        if($include_fields != null) {
            $include_fields = explode(",", $include_fields);

            if(!is_array($include_fields)) {
                $include_fields = array($include_fields);
            }

            if(array_search("races", $include_fields) !== false) {
                print_r("Here");
                $election->races = $this->aggregate_races_from_candidates($election->candidates);
                unset($election->candidates);
            }
        }
        
        return response()->json($election, 200);
    }

    /**
     * Display candidates that belong to the election that corresponds to the provided $id
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function election_candidates(Request $request, $id) {
        return response()->json(
            ConsolidatedElection::find($id)->candidates
        ,200);
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
