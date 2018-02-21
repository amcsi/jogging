<?php

namespace App\Http\Requests;

use App\User;

class IndexUser extends PagingRequest
{
    public function authorize()
    {
        return $this->user()->can('list', User::class);
    }
}
