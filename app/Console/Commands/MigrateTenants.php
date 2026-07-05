<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\TenantService;
use Illuminate\Console\Command;

class MigrateTenants extends Command
{
    protected $signature   = 'tenants:migrate {--fresh : Drop all tables and re-run migrations}';
    protected $description = 'Run migrations on every tenant database registered in the clients table';

    public function handle(): void
    {
        $clients = Client::all();

        if ($clients->isEmpty()) {
            $this->warn('No clients found in the database.');
            return;
        }

        foreach ($clients as $client) {
            $this->info("Migrating tenant: {$client->client_code} (DB: {$client->db_name})");

            TenantService::connect($client);

            $method = $this->option('fresh') ? 'migrate:fresh' : 'migrate';

            $this->call($method, [
                '--database' => 'tenant',
                '--force'    => true,
            ]);
        }

        $this->info('All tenant migrations completed.');
    }
}
