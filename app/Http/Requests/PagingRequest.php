<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * General purpose form request allowing for pagination.
 */
class PagingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'limit' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
        ];
    }

    public function getLimit(): ?int
    {
        return (int) $this->get('limit') ?: null;
    }
}
