<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Helpers\JWT;
use App\Models\Client;
use App\Models\ClientLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Location\Facades\Location;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            DB::beginTransaction();
            // Validating incoming request data
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Filtering user by username and password
            $user = User::where('username', $request->input('username'))->first();

            if ($user && Hash::check($request->password, $user->password)) {
                // Check if user exists
                if (!$user) {
                    $returnJson = Helpers::formatJson(null, 404, "User not found", true);
                    return response()->json($returnJson['data'], $returnJson['status_response']);
                }

                $generateToken = JWT::generateToken($user, Config::get('constants.jwt_expired_time'), Config::get('constants.jwt_secret_key'));

                Session::put("token", $generateToken);

                // update table user
                $user->jwt_token = $generateToken;
                $user->last_login = Carbon::now();
                $user->save();

                DB::commit();
                $returnJson = Helpers::formatJson(null, 200, "Success login", true);
            } else {
                $returnJson = Helpers::formatJson(null, 401, "Username or password is wrong", false);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            $returnJson = Helpers::formatJson(null, 500, $th->getMessage() . ' line: ' . $th->getLine(), false, $request->all(), URL::current());
        }
        return response()->json($returnJson['data'], $returnJson['status_response']);
    }

    public function logout(Request $request)
    {
        try {
            $client = User::find($request->user_id);
            $client->jwt_token = null;
            $client->save();
            Session::flush();
            $returnJson = Helpers::formatJson(null, 200, "Success logout", true);
        } catch (\Throwable $th) {
            $returnJson = Helpers::formatJson(null, 500, $th->getMessage() . ' line: ' . $th->getLine(), false, $request->all(), URL::current());
        }
        return response()->json($returnJson['data'], $returnJson['status_response']);
    }

    public function me(Request $request)
    {
        try {
            $user = Session::get('token');
            if (!$user) {
                $returnJson = Helpers::formatJson(null, 403, "Unauthorized", true);
                return response()->json($returnJson['data'], $returnJson['status_response']);
            }
            $returnJson = Helpers::formatJson($request->all(), 200, "Success login", true);
        } catch (\Throwable $th) {
            $returnJson = Helpers::formatJson(null, 500, $th->getMessage() . ' line: ' . $th->getLine(), false, $request->all(), URL::current());
        }
        return response()->json($returnJson['data'], $returnJson['status_response']);
    }
}
