<?php

namespace App\Http\Controllers;

use App\Common\ApiFieldErrorsException;
use App\Common\JsonResponder;
use App\Common\UniqueIndex;
use App\Http\Requests\PagingRequest;
use App\JoggingTime;
use App\JoggingTime\JoggingTimeTransformer;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JoggingTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(JoggingTimeTransformer $joggingTimeTransformer, User $user, PagingRequest $request)
    {
        $this->authorize('index', [JoggingTime::class, $user]);

        $joggingTimes = JoggingTime::where('user_id', $user->id)
            ->orderBy('day', 'desc')
            ->paginate($request->getLimit());

        return JsonResponder::respond($joggingTimes, $joggingTimeTransformer);
    }

    public function store(Request $request, User $user, JoggingTimeTransformer $joggingTimeTransformer)
    {
        $this->authorize('create', [JoggingTime::class, $user]);

        $data = $request->validate([
            'distance_m' => 'required|integer|min:1',
            'minutes' => 'required|integer|min:1',
            'day' => 'required|date|date_format:Y-m-d|before_or_equal:today',
        ]);

        $joggingTime = new JoggingTime($data);
        $joggingTime->user_id = $user->id;
        try {
            $joggingTime->save();
        } catch (QueryException $exception) {
            if (UniqueIndex::isUniqueIndexException($exception)) {
                throw new ApiFieldErrorsException(['day' => ['There already is an entry for that day']], $exception);
            } else {
                throw $exception;
            }
        }

        return JsonResponder::respond($joggingTime, $joggingTimeTransformer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JoggingTime $joggingTime, JoggingTimeTransformer $joggingTimeTransformer)
    {
        $this->authorize('update', $joggingTime);

        $data = $request->validate([
            'distance_m' => 'integer|min:1',
            'minutes' => 'integer|min:1',
        ]);

        $joggingTime->fill($data);
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
