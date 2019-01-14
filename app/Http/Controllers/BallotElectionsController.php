<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataLayer\Ballot\Ballot;
use App\DataLayer\Election\ConsolidatedElection;
use App\BusinessLogic\BallotManager;

class BallotElectionsController extends Controller
{
  private $ballot_manager;

  public function __construct(BallotManager $ballot_manager) {
    $this->ballot_manager = $ballot_manager;
  }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Ballot $ballot)
    {
      $elections = $this->ballot_manager->get_elections_from_ballot($ballot);
        return response()->json($elections, 200);
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

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function update(Request $request, $id)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function destroy($id)
    // {
    //     //
    // }
}
