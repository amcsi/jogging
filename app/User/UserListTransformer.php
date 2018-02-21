<?php
declare(strict_types=1);

namespace App\User;

use App\Common\ModelTransformer;
use App\User;

class UserListTransformer
{
    private $modelTransformer;

    public function __construct(ModelTransformer $modelTransformer)
    {
        $this->modelTransformer = $modelTransformer;
    }

    public function __invoke(User $user): array
    {
        $return = $this->modelTransformer->__invoke($user);
        $return['email'] = $user->email;
        $return['role'] = $user->role;
        return $return;
    }
}
