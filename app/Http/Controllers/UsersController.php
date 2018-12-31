<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\BusinessLogic\UserManager;

class UsersController extends Controller
{
  private $user_manager;

  public function __construct(UserManager $user_manager) {
    $this->user_manager = $user_manager;
  }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Do some permissions checking!!
        return $this->user_manager->get_users();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      // $input_params = $request->all();
      $name = $request->input('name');
      $email = $request->input('email');
      $password = $request->input('password');
        
      try {
        $newUser = $this->user_manager->save_user($name, $email, $password);
        return response()->json($newUser, 201);
      } catch (Exception $ex) {
        print_r($ex);
          return response()->json([
              "error" => "could not create new user: " . $ex->getMessage(),
          ], 500);
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
      $user = $this->user_manager->translate_user($request->user());
        return response()->json($user, 200);
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
            'password' => 'required|string|min:6|confirmed'
        ]);
    }
}
