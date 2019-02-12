<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\BusinessLogic\Repositories\ElectionRepository;

class ElectionsController extends Controller
{

    protected $repository;

    public function __construct(ElectionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $state_abbreviation = $request->query('state', null);
        
        $elections = null;
        
        if($state_abbreviation != null) {
          $elections = $this->repository->allByState($state_abbreviation);
          // $elections = $elections->where('state_abbreviation', $state_abbreviation);
        } else {
          $elections = $this->repository->all();
        }
        
        array_map(function($election) {
          unset($election->candidates);
          return $election;
        }, $elections);
        
        return response()->json($elections, 200);
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
      
        $election = $this->repository->get(intval($id));

        if($election == null) {
            return response()->make("", 404);
        }
        
        unset($election->candidates);
        
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
