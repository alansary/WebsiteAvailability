<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transformers\UserTransformer;
use App\User;
use Validator;
use JWTAuth;
use Hash;
use JWTException;

class UsersController extends Controller
{
	/**
	 * function for registering a new user
	 * 
	 * @param  Request $request request parameters are username, password and email
	 * @return JSON           returns the registered user and a session token
	 * @api
	 */
	public function register(Request $request)
	{
        // input validation
        $input = [
        	'username' => $request->input('username'),
            'email'   => $request->input('email'),
            'password' => $request->input('password'),
            'password_confirmation' => $request->input('password_confirmation')
        ];
        $rules = [
        	'username' => 'required|string|max:255|unique:users',
            'email'   => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ];
        $messages = [];
        $validator = Validator::make($input, $rules, $messages);
        if ($validator->fails()) {
        	$errors = $validator->errors()->all();
        	return response()->json(['errors' => $errors], 400);
        }

        // user registration
        $user = User::create([
        	'username' => $input['username'],
        	'email' => $input['email'],
        	'password' => Hash::make($input['password'])
        ]);

        // token generation
        $token = JWTAuth::fromUser($user);

        // response
        $user = fractal()
            ->item($user)
            ->transformWith(new UserTransformer)
            ->toArray()['data'];

        $response = ['user' => $user, 'token' => $token];

        return response()->json($response, 200);
	}

	/**
	 * function for authenticating a user
	 * 
	 * @param  Request $request request parameters are username and password
	 * @return JSON           returns the authenticated user and a session token
	 * @api
	 */
	public function login(Request $request)
	{
        // input validation
        $input = [
        	'username' => $request->input('username'),
            'password' => $request->input('password')
        ];
        $rules = [
        	'username' => 'required|string|max:255',
            'password' => 'required|string|min:6'
        ];
        $messages = [];
        $validator = Validator::make($input, $rules, $messages);
        if ($validator->fails()) {
        	$errors = $validator->errors()->all();
        	return response()->json(['errors' => $errors], 400);
        }

		$credentials = request()->only('username', 'password');
		try {
		    // verify the credentials and create a token for the user
		    if (! $token = JWTAuth::attempt($credentials)) {
		        return response()->json(['errors' => ['Invalid username or password']], 401);
		    }
		} catch (JWTException $e) {
		    // something went wrong
		    return response()->json(['errors' => [$e->getMessage()]], 500);
		}
		$user = JWTAuth::toUser($token);

        // Response
        $user = fractal()
            ->item($user)
            ->transformWith(new UserTransformer)
            ->toArray()['data'];

        $response = ['user' => $user, 'token' => $token];

        return response()->json($response, 200);
	}
}
