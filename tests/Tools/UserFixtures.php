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

    /**
     * @before
     */
    public function setupUserFixtures(): void
    {
        parent::setUp();
        $this->admin = factory(User::class)->create(['email' => 'admin@example.com', 'role' => Role::ADMIN]);
        $this->manager = factory(User::class)->create(['email' => 'manager@example.com', 'role' => Role::MANAGER]);
        $this->user = factory(User::class)->create(['email' => 'user@example.com', 'role' => Role::USER]);
    }
}
