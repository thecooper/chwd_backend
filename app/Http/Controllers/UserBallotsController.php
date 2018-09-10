<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\DataSources\GeocodioAPIDataSource;
use App\Models\Candidate\ConsolidatedCandidate;
use App\Models\Address;
use App\UserBallot;

class UserBallotsController extends Controller
{
    public function __construct() {
        $this->middleware('ballot-valid-user:ballot')->except('index', 'store');
    }

    // public function getRouteKeyName()
    // {
    //     return 'ballot_id';
    // }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if($user == null) {
            return response()->json(array('error' => 'Not authenticated'), 401);
        }

        return response()->json($user->ballots, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, GeocodioAPIDataSource $geocodio_data_source)
    {
        $user = $request->user();
        $fields = $request->all();

        $address = new Address();
        $address->load($fields);
        
        // TODO: get district info based on address object
        $geocodio_response = $geocodio_data_source->get_geolocation_information($address);

        if(is_array($geocodio_response)) {
            return response()->json("error from Geocodio API: " . $geocodio_response["error"], 400);
        }

        $user_ballot = new UserBallot();

        $user_ballot->user_id = $user->id;
        $user_ballot->address_line_1 = $geocodio_response->address_line_1;
        $user_ballot->address_line_2 = $geocodio_response->address_line_2;
        $user_ballot->city = $geocodio_response->city;
        $user_ballot->state_abbreviation = $geocodio_response->state;
        $user_ballot->zip = $geocodio_response->zip;
        $user_ballot->county = $geocodio_response->county;
        $user_ballot->congressional_district = $geocodio_response->congressional_district;
        $user_ballot->state_legislative_district = $geocodio_response->state_legislative_district;
        $user_ballot->state_house_district = $geocodio_response->state_house_district;

        $user_ballot->save();

        return response()->json($user_ballot, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, UserBallot $ballot)
    {
        return response()->json($ballot, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $candidate_id)
    {
        return response()->json("endpoint is not yet implemented", 500);
        // $user = $request->user();

        // $candidate = ConsolidatedCandidate::find($candidate_id);

        // if($candidate == null) {
        //     return response()->json("candidate with ID ${candidate_id} does not exist", 404);
        // }
        
        // DB::table('user_ballots')->insert([
        //     'user_id' => $user->id,
        //     'candidate_id' => $candidate_id
        // ]);

        // return response()->json(201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserBallot $ballot)
    {
        // dd($id);
        // $user_ballot = UserBallot::find($id);

        if($ballot == null) {
            return response()->json('user ballot not found', 404);
        }

        $ballot->delete();

        return response()->json('', 202);
    }
}
