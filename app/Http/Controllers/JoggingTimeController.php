<?php

namespace App\Http\Controllers;

use App\Common\JsonResponder;
use App\JoggingTime;
use App\JoggingTime\JoggingTimeTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JoggingTimeController extends Controller
{
    private const DEFAULT_LIMIT = 10;

    /**
     * Display a listing of the resource.
     */
    public function index(JoggingTimeTransformer $joggingTimeTransformer, Request $request)
    {
        $limit = max(1, min(100, $request['limit'] ?: self::DEFAULT_LIMIT));
        $page = $request['page'] ?: 1;

        $joggingTimes = JoggingTime::where('user_id', $request->user()->id)->paginate($limit, null, null, $page);

        return JsonResponder::respond($joggingTimes, $joggingTimeTransformer);
    }

    public function store(Request $request, JoggingTimeTransformer $joggingTimeTransformer)
    {
        $this->authorize('create', JoggingTime::class);

        $data = $request->validate([
            'distance' => 'required|integer|min:1',
            'seconds' => 'required|integer|min:1',
            'day' => 'date|date_format:Y-m-d|before:tomorrow',
        ]);

        $joggingTime = new JoggingTime($data);
        $joggingTime->user_id = $request->user()->id;
        $joggingTime->save();

        return JsonResponder::respond($joggingTime, $joggingTimeTransformer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JoggingTime $joggingTime, JoggingTimeTransformer $joggingTimeTransformer)
    {
        $this->authorize('update', $joggingTime);

        $joggingTime->fill($request->toArray());
        $joggingTime->save();

        return JsonResponder::respond($joggingTime, $joggingTimeTransformer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JoggingTime $joggingTime)
    {
        $this->authorize('delete', $joggingTime);

        $joggingTime->delete();

        return new Response('', 204);
    }
}
