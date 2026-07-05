<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HCLUserSeeder extends Seeder
{
    protected string $tenantDatabase = 'hcl_db';

    public function run(): void
    {
        $this->switchTenantConnection($this->tenantDatabase);

        User::on('tenant')->create([
            'name' => 'HCL',
            'email' => 'hcluser@gmail.com',
            'password' => Hash::make('12345678'),
        ]);

        $this->command->info("HCLUserSeeder: seeded into [{$this->tenantDatabase}]");
    }

    protected function switchTenantConnection(string $database): void
    {
        config([
            'database.connections.tenant.host'     => env('TENANT_DB_HOST', '127.0.0.1'),
            'database.connections.tenant.port'     => env('TENANT_DB_PORT', '3306'),
            'database.connections.tenant.database' => $database,
            'database.connections.tenant.username' => env('TENANT_DB_USERNAME', 'root'),
            'database.connections.tenant.password' => env('TENANT_DB_PASSWORD', ''),
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}
