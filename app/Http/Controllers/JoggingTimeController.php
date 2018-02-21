<?php

namespace App\Http\Controllers;

use App\Common\JsonResponder;
use App\JoggingTime;
use Illuminate\Http\Request;

class JoggingTimeController extends Controller
{
    private const DEFAULT_LIMIT = 10;

    /**
     * Display a listing of the resource.
     */
    public function index(JoggingTime\JoggingTimeTransformer $joggingTimeTransformer, Request $request)
    {
        $limit = max(1, min(100, $request['limit'] ?: self::DEFAULT_LIMIT));
        $page = $request['page'] ?: 1;

        $joggingTimes = JoggingTime::where('user_id', \Auth::user()->id)->paginate($limit, null, null, $page);

        return JsonResponder::respond($joggingTimes, $joggingTimeTransformer);
    }

    public function store(Request $request, JoggingTime\JoggingTimeTransformer $joggingTimeTransformer)
    {
        $data = $request->validate([
            'distance' => 'required|integer|min:1',
            'seconds' => 'required|integer|min:1',
            'day' => 'date|date_format:Y-m-d|before:tomorrow',
        ]);

        $joggingTime = new JoggingTime($data);
        $joggingTime->user_id = \Auth::user()->id;
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
     *
     * @param  \App\JoggingTime  $joggingTime
     * @return \Illuminate\Http\Response
     */
    public function destroy(JoggingTime $joggingTime)
    {
        //
    }
}
