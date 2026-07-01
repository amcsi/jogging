<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('oauth_clients') || ! Schema::hasColumn('oauth_clients', 'password_client')) {
            return;
        }

        $ownerId = DB::table('users')->min('id');

        if ($ownerId === null) {
            return;
        }

        $passwordClients = DB::table('oauth_clients')
            ->where('password_client', 1)
            ->whereNull('user_id')
            ->get(['id', 'provider']);

        foreach ($passwordClients as $client) {
            DB::table('oauth_clients')
                ->where('id', $client->id)
                ->update([
                    'user_id' => $ownerId,
                    'provider' => $client->provider ?? 'users',
                ]);
        }
    }

    public function down(): void
    {
        // Ownership cannot be restored safely once assigned.
    }
};
