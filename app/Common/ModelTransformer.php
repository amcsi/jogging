<?php
declare(strict_types=1);

namespace App\Common;

use Illuminate\Database\Eloquent\Model;

/**
 * Base model transformer.
 */
class ModelTransformer
{
    public function __invoke(Model $model): array
    {
        return [
            'id' => $model->id,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
        ];
    }
}
