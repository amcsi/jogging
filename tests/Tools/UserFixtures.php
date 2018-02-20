<?php
declare(strict_types=1);

namespace Tests\Tools;

use App\User;

trait UserFixtures
{
    /** @var User */
    protected $admin;

    /**
     * @before
     */
    public function setupUserFixtures(): void
    {
        parent::setUp();
        $admin = factory(User::class)->create(['email' => 'admin@example.com']);
        $this->admin = $admin;
    }
}
