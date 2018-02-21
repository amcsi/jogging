<?php

namespace App\Http\Controllers;

use App\Common\JsonResponder;
use App\JoggingTime;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JoggingTimeController extends Controller
{
    private const DEFAULT_LIMIT = 10;

    /**
     * Display a listing of the resource.
     */
    public function index(JoggingTime\JoggingTimeTransformer $joggingTimeTransformer, Request $request, Guard $guard)
    {
        $limit = max(1, min(100, $request['limit'] ?: self::DEFAULT_LIMIT));
        $page = $request['page'] ?: 1;

        $joggingTimes = JoggingTime::where('user_id', $guard->user()->id)->paginate($limit, null, null, $page);

        return JsonResponder::respond($joggingTimes, $joggingTimeTransformer);
    }

    public function store(Request $request, JoggingTime\JoggingTimeTransformer $joggingTimeTransformer, Guard $guard)
    {
        $data = $request->validate([
            'distance' => 'required|integer|min:1',
            'seconds' => 'required|integer|min:1',
            'day' => 'date|date_format:Y-m-d|before:tomorrow',
        ]);

        $joggingTime = new JoggingTime($data);
        $joggingTime->user_id = $guard->user()->id;
        $joggingTime->save();

        return JsonResponder::respond($joggingTime, $joggingTimeTransformer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\JoggingTime  $joggingTime
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JoggingTime $joggingTime)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JoggingTime $joggingTime, Guard $guard)
    {
        if ((int) $joggingTime->user_id === $guard->user()->id) {
            $joggingTime->delete();
        }

        return new Response('', 204);
    }
}
