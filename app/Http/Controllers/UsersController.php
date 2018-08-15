<?php

namespace App\Http\Controllers;

use App\User;
use App\GeocodioAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Do some permissions checking!!
        return User::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        throw new Exception("Not Implemented");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $input_params = $request->all();
            validator($input_params)->validate();

            $address_line_1 = $input_params['address_line_1'];
            $address_line_2 = $input_params['address_line_2'];
            $city = $input_params['city'];
            $state_abbreviation = $input_params['state_abbreviation'];
            $zip = $input_params['zip'];

            $geocodioAPI = new GeocodioAPI();

            $geocodio_results = $geocodioAPI->get_geolocation_information($address_line_1, $address_line_2, $state_abbreviation, $city, $zip);
            
            if(isset($geocodio_results['error'])) {
                return response()->json($geocodio_results['error'], 500);
            }

            $newUser = User::create([
                'name' => $input_params['name'],
                'email' => $input_params['email'],
                'password' => Hash::make($input_params['password']),
                'address_line_1' => $address_line_1,
                'address_line_2' => $address_line_2,
                'city' => $city,
                'zip' => $zip,
                'state_abbreviation' => $state_abbreviation,
                'congressional_district' => $geocodio_results[0],
                'state_legislative_district' => $geocodio_results[1]
            ]);

            
            // event(new Registered($newUser));

            return response()->json($newUser, 201);
        } catch (Exception $ex) {
            return response()->json([
                "error" => "could not create new user",
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        return $request->user();
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

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'address_line_1' => 'required|string',
            'address_line_2' => 'required|string',
            'city' => 'required|string',
            'state_abbreviation' => 'required|string|max:2',
            'zip' => 'required|string',
        ]);
    }
}
