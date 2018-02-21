<?php
declare(strict_types=1);

namespace App\Http\Requests;

use App\User\Role;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUser extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules()
    {
        $rules = [
            'password' => 'min:4',
        ];

        $authUser = $this->user();
        $targetUser = $this->route('user');
        if ($authUser->can('changeEmail', $targetUser)) {
            $rules['email'] = 'email';
        }
        if ($authUser->can('changeRole', $targetUser)) {
            $rules['role'] = 'in:' . implode(',', Role::getAll());
        }
        return $rules;
    }

}
