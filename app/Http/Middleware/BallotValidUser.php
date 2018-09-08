<?php

namespace App\Http\Middleware;

use Closure;

use App\UserBallot;

class BallotValidUser
{
    public static $not_found_message = 'specified ballot does not belong to authenticated user';
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $route_parameter)
    {
        $route_parameter = $request->route($route_parameter);
        $user_ballot = null;
        $user = $request->user();

        if(!is_numeric($route_parameter)) {
            if(get_class($route_parameter) == UserBallot::class) {
                $user_ballot = $route_parameter;
            } else {
                return response()->json("ballot id ({$route_parameter}) is not numeric", 400);
            }
        } else {
            $user_ballot = UserBallot::find($route_parameter);
        }

        if($user_ballot == null) {
            return response()->json(null, 404);
        }
        
        if($user == null) {
            return response()->json('unauthenticated', 401);
        }

        if($user_ballot->verify_belongs_to_user($user)) {
            return $next($request);
        } else {
            return response()->json(BallotValidUser::$not_found_message . ": user ballot does not belong to user", 404);
        }
    }
}
