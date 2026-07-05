<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /**
     * Clients always live in the main/central database, never the tenant DB.
     */
    protected $connection = 'mysql';

    protected $fillable = [
        'client_code',
        'db_server',
        'db_port',
        'db_name',
        'db_user',
        'db_password',
    ];
}
