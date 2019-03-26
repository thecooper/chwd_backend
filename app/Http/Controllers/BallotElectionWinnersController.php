<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\DataLayer\Ballot\Ballot;
use App\BusinessLogic\BallotManager;

class BallotElectionWinnersController extends Controller
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
      $candidates = collect($this->ballot_manager->get_winners_of_last_elections($ballot))
          ->groupBy('district_type')
          ->map(function($value) {
              return $value->groupBy('office');
          });

      return response()->json($candidates, 200);
    }
}
