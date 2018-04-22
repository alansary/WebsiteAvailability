<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transformers\UrlTransformer;
use App\Url;
use Validator;
use JWTAuth;
use App\DownLog;
use App\Http\Helpers\Utilities;

class UrlsController extends Controller
{
	/**
	 * function to get all urls of the authenticated user
	 * 
	 * @return JSON           returns the list of urls of the authenticated user
	 * @api
	 */
	public function getAllUserUrls(Request $request)
	{
		// authenticating the user
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);

		$urls = $user->urls;
		foreach ($urls as $url) {
	        // update the status of the URL
	        $is_active = Utilities::curlInit($url->url);

	        $url->is_active = ($is_active) ? 1 : 0;
	        $url->save();

	        // creating a down log if the URL is down
	        if (! $is_active) {
	        	// if no down log exist, create a down log
	        	if (! DownLog::where('url_id', '=', $url->id)->get()->count()) {
		        	DownLog::create([
		        		'url_id' => $url->id
		        	]);
		        }
	        } else {
	        	// deleting the down log if any
	        	DownLog::where('url_id', '=', $url->id)->delete();
	        }
		}

		// response
		$urls = fractal()
			->collection($user->urls)
			->transformWith(new UrlTransformer)
			->toArray()['data'];

		return response()->json(['urls' => $urls], 200);
	}

	/**
	 * function to store a new user url
	 *
	 * @param  Request $request request parameter is url
	 * @return JSON           returns the stored url
	 * @api
	 */
	public function store(Request $request)
	{
		// authenticating the user
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);

        // input validation
        $input = [
        	'url' => $request->input('url')
        ];
        $rules = [
        	'url' => 'required|url|string|max:255'
        ];
        $messages = [];
        $validator = Validator::make($input, $rules, $messages);
        if ($validator->fails()) {
        	$errors = $validator->errors()->all();
        	return response()->json(['errors' => $errors], 400);
        }

        // check if the url already exists
        if (Url::where('url', '=', $input['url'])->where('user_id', '=', $user->id)->get()->count()) {
        	return response()->json(['errors' => ['URL/App already exists']], 400);
        }
        // check if url is up or down
        $is_active = Utilities::curlInit($input['url']);

        $url = Url::create([
        	'url' => $input['url'],
        	'user_id' => $user->id,
        	'is_active' => ($is_active) ? 1 : 0
        ]);

        // creating a down log if the URL is down
        if (! $is_active) {
        	DownLog::create([
        		'url_id' => $url->id
        	]);
        }

		$url = fractal()
			->item($url)
			->transformWith(new UrlTransformer)
			->toArray()['data'];

		return response()->json(['url' => $url], 200);
	}

	/**
	 * function to get a user url by id
	 *
	 * @param Request $request request parameter is id
	 * @return JSON           returns the url if found
	 * @api
	 */
	public function getUrlById(Request $request)
	{
		// authenticating the user
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);

        // input validation
        $input = [
        	'id' => $request->input('id')
        ];
        $rules = [
        	'id' => 'required|numeric'
        ];
        $messages = [];
        $validator = Validator::make($input, $rules, $messages);
        if ($validator->fails()) {
        	$errors = $validator->errors()->all();
        	return response()->json(['errors' => $errors], 400);
        }

        // try to find the url
        try {
        	$url = Url::findOrFail($input['id']);
        } catch (\Exception $e) {
        	return response()->json(['errors' => ['URL not found']], 400);
        }

        // check if the url belongs to the request user
        if ($url->user_id != $user->id) {
        	return response()->json(['errors' => ['Unauthorized']], 401);
        }

        // update the status of the URL
        $is_active = Utilities::curlInit($url->url);

        $url->is_active = ($is_active) ? 1 : 0;
        $url->save();

        // creating a down log if the URL is down
        if (! $is_active) {
        	// if no down log exist, create a down log
        	if (! DownLog::where('url_id', '=', $url->id)->get()->count()) {
	        	DownLog::create([
	        		'url_id' => $url->id
	        	]);
	        }
        } else {
        	// deleting the down log if any
        	DownLog::where('url_id', '=', $url->id)->delete();
        }

        // return the response
		$url = fractal()
			->item($url)
			->transformWith(new UrlTransformer)
			->toArray()['data'];

		return response()->json(['url' => $url], 200);
	}

	/**
	 * function to delete a user url by id
	 *
	 * @param Request $request request parameter is id
	 * @return JSON           delete the url if found
	 * @api
	 */
	public function destroy(Request $request)
	{
		// authenticating the user
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);

        // input validation
        $input = [
        	'id' => $request->input('id')
        ];
        $rules = [
        	'id' => 'required|numeric'
        ];
        $messages = [];
        $validator = Validator::make($input, $rules, $messages);
        if ($validator->fails()) {
        	$errors = $validator->errors()->all();
        	return response()->json(['errors' => $errors], 400);
        }

        // try to find the url
        try {
        	$url = Url::findOrFail($input['id']);
        } catch (\Exception $e) {
        	return response()->json(['errors' => ['URL not found']], 400);
        }

        // check if the url belongs to the request user
        if ($url->user_id != $user->id) {
        	return response()->json(['errors' => ['Unauthorized']], 401);
        }

    	// deleting the down log if any
    	DownLog::where('url_id', '=', $url->id)->delete();

    	// deleting the url
    	$url->delete();

		return response()->json(['message' => 'URL deleted successfully'], 200);
	}

	/**
	 * function to update a user url
	 *
	 * @param Request $request request parameters are id and url
	 * @return JSON           delete the url if found
	 * @api
	 */
	public function update(Request $request)
	{
		// authenticating the user
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);

        // input validation
        $input = [
        	'id' => $request->input('id'),
        	'url' => $request->input('url')
        ];
        $rules = [
        	'id' => 'required|numeric',
        	'url' => 'required|url|string|max:255'
        ];
        $messages = [];
        $validator = Validator::make($input, $rules, $messages);
        if ($validator->fails()) {
        	$errors = $validator->errors()->all();
        	return response()->json(['errors' => $errors], 400);
        }

        // try to find the url
        try {
        	$url = Url::findOrFail($input['id']);
        } catch (\Exception $e) {
        	return response()->json(['errors' => ['URL not found']], 400);
        }

        // check if the url belongs to the request user
        if ($url->user_id != $user->id) {
        	return response()->json(['errors' => ['Unauthorized']], 401);
        }

        // check if the url already exists
        if (Url::where('url', '=', $input['url'])->where('user_id', '=', $user->id)->get()->count()) {
        	return response()->json(['errors' => ['URL/App already exists']], 400);
        }

        // removing the down log of the old url if exists
        DownLog::where('url_id', '=', $url->id)->delete();

        // check if url is up or down
        $is_active = Utilities::curlInit($input['url']);

        // updating the url
        $url->url = $input['url'];
        $url->is_active = ($is_active) ? 1 : 0;
        $url->save();

        // creating a down log if the URL is down
        if (! $is_active) {
        	DownLog::create([
        		'url_id' => $url->id
        	]);
        }

        // return the response
		$url = fractal()
			->item($url)
			->transformWith(new UrlTransformer)
			->toArray()['data'];

		return response()->json(['url' => $url], 200);
	}
}
