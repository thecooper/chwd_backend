<?php

namespace App\Http\Controllers;

use App\DataLayer\News;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserNewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->user() == null) {
            return response()->json(array("Not authenticated"), 401);
        }

        return response()->json($request->user()->news, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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
        $user = $request->user();

        if($user == null) {
            return response()->json('request not authenticated', 401);
        }

        $news_article = News::find($id);

        if($news_article == null) {
            return response()->json('cannot bind to news article that does not exist', 404);
        }

        $existing_link = $this->getExisitngUserNewsLink($user->id, $id)->first();
        
        if($existing_link != null) {
            return response()->json('resource already exists', 409);
        }

        DB::table('user_news')->insert([
            "user_id" => $user->id,
            "news_id" => $id
        ]);

        return response()->json(null, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $existing_link_query = $this->getExisitngUserNewsLink($request->user()->id, $id);
        $existing_link = $existing_link_query->first();

        if($existing_link == null) {
            return response()->json('specified link does not exist', 404);
        }

        $existing_link_query->delete();

        return response()->json(null, 202);
    }

    private function getExisitngUserNewsLink($user_id, $id) {
        return DB::table('user_news')->where('user_id', $user_id)->where('news_id', $id);
    }
}
