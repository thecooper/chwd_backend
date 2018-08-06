<?php

namespace App\Http\Controllers;

use App\User;
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
        try {
            $inputParams = $request->all();
            validator($inputParams)->validate();

            // if (!isset($inputParams['address_line_2'])) {
            //     $inputParams['address_line_2'] = '<blank>';
            // }

            $newUser = User::create([
                'name' => $inputParams['name'],
                'email' => $inputParams['email'],
                'password' => Hash::make($inputParams['password']),
                'address_line_1' => $inputParams['address_line_1'],
                'address_line_2' => $inputParams['address_line_2'],
                'city' => $inputParams['city'],
                'state' => $inputParams['state'],
                'zip' => $inputParams['zip'],
                'stateAbbv' => $inputParams['stateAbbv'],
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        throw new Exception("Not yet implemented");
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
            'state' => 'required|string',
            'zip' => 'required|string',
            'stateAbbv' => 'required|string|max:2',
        ]);
    }
}
