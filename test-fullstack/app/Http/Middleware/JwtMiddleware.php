<?php

namespace App\Http\Middleware;

use App\Helpers\Helpers;
use App\Helpers\JWT;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Session::get("token");
        if (empty($user)) {
            $returnJson = Helpers::formatJson(null, 403, "Unauthorized, please log in first", true);
            return response()->json($returnJson['data'], $returnJson['status_response']);
        }

        $extractToken = JWT::parsingToken($user);

        $getExpiredToken = JWT::convertToIndonesianTime($extractToken->get('exp'));
        $dateNow = Carbon::now();
        $expiredToken = Carbon::parse($getExpiredToken);

        if ($dateNow->gt($expiredToken)) {
            $getUserId = User::find($extractToken->get('data')['id']);
            if ($getUserId->jwt_token == $user) {
                $generateToken = JWT::generateToken($getUserId, Config::get('constants.jwt_expired_time'), Config::get('constants.jwt_secret_key'));
                $getUserId->jwt_token = $generateToken;
                $getUserId->save();
                Session::put('token', $generateToken);
            } else {
                $returnJson = Helpers::formatJson(null, 403, "Unauthorized, please log in first", true);
                return response()->json($returnJson['data'], $returnJson['status_response']);
            }
        }
        $request->merge([
            'user_id' => $extractToken->get('data')['id'],
            'username' => $extractToken->get('data')['username']
        ]);
        return $next($request);
    }
}
