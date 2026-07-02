<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Passport 13 treats unowned confidential password clients as client_credentials
     * capable, which breaks bearer auth when oauth user id equals client id.
     */
    public function up(): void
    {
        if (! Schema::hasTable('oauth_clients') || ! Schema::hasColumn('oauth_clients', 'password_client')) {
            return;
        }

        $ownerId = DB::table('users')->min('id');

        if ($ownerId === null) {
            return;
        }

        $columns = ['id'];
        if (Schema::hasColumn('oauth_clients', 'provider')) {
            $columns[] = 'provider';
        }

        $passwordClients = DB::table('oauth_clients')
            ->where('password_client', 1)
            ->whereNull('user_id')
            ->get($columns);

        foreach ($passwordClients as $client) {
            $updates = ['user_id' => $ownerId];

            if (Schema::hasColumn('oauth_clients', 'provider')) {
                $updates['provider'] = $client->provider ?? 'users';
            }

            DB::table('oauth_clients')
                ->where('id', $client->id)
                ->update($updates);
        }
    }

    public function down(): void
    {
        // Ownership cannot be restored safely once assigned.
    }
};
