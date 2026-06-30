<?php
declare(strict_types=1);

namespace Tests\Tools;

use App\User;
use App\User\Role;

trait UserFixtures
{
    /** @var User */
    protected $admin;
    /** @var User */
    protected $manager;
    /** @var User */
    protected $user;

    public function setUpUserFixtures(): void
    {
        $this->admin = User::factory()->create(['email' => 'admin@example.com', 'role' => Role::ADMIN]);
        $this->manager = User::factory()->create(['email' => 'manager@example.com', 'role' => Role::MANAGER]);
        $this->user = User::factory()->create(['email' => 'user@example.com', 'role' => Role::USER]);
    }
}
