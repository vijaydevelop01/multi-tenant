<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Seed tenant client records into the central database.
     */
    public function run(): void
    {
        $clients = [
            [
                'client_code' => 'IBM',
                'db_server'   => '127.0.0.1',
                'db_port'     => '3306',
                'db_name'     => 'ibm_db',
                'db_user'     => 'root',
                'db_password' => '',
            ],
            [
                'client_code' => 'HCL',
                'db_server'   => '127.0.0.1',
                'db_port'     => '3306',
                'db_name'     => 'hcl_db',
                'db_user'     => 'root',
                'db_password' => '',
            ],
            [
                'client_code' => 'INFOSYS',
                'db_server'   => '127.0.0.1',
                'db_port'     => '3306',
                'db_name'     => 'infosys_db',
                'db_user'     => 'root',
                'db_password' => '',
            ],
        ];

        foreach ($clients as $data) {
            Client::firstOrCreate(['client_code' => $data['client_code']], $data);
            $this->command->info("Client [{$data['client_code']}] → {$data['db_name']}");
        }
    }
}
