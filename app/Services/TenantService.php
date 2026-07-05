<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TenantService
{
    /**
     * Dynamically connect to the tenant database.
     *
     * @param object $client
     * @return void
     */
    public static function connect($client)
    {
        config([
            'database.connections.tenant.host'=>$client->db_server,
            'database.connections.tenant.port'=>$client->db_port,
            'database.connections.tenant.database'=>$client->db_name,
            'database.connections.tenant.username'=>$client->db_user,
            'database.connections.tenant.password'=>$client->db_password,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}