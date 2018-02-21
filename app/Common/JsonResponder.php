<?php
declare(strict_types=1);

namespace App\Common;

use Illuminate\Pagination\LengthAwarePaginator;

class JsonResponder
{
    /**
     * Transforms data and returns the response in an envelope in the 'data' property.
     * If the passed data is a paginator, a 'pagination' property is also included.
     *
     * @param $data
     * @param callable $transformer
     * @return array
     */
    public static function respond($data, callable $transformer): array
    {
        $responseData = [];
        if (is_iterable($data)) {
            foreach ($data as $datum) {
                $responseData[] = $transformer($datum);
            }
        } else {
            $responseData = $transformer($data);
        }
        $return = ['data' => $responseData];
        if ($data instanceof LengthAwarePaginator) {
            $return['pagination'] = [
                'current_page' => $data->currentPage(),
                'per_page' => (int) $data->perPage(),
                'total' => $data->total(),
            ];
        }
        return $return;
    }
}
