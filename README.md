Multi-Tenant Laravel API
Laravel PHP Sanctum MySQL License
A Multi-Tenant REST API built with Laravel 12 and Laravel Sanctum, where each client (tenant) has its own isolated MySQL database.
The application uses a central database to maintain tenant information, while all tenant-specific data (users, authentication tokens, etc.) is stored in dedicated tenant databases.
________________________________________
Table of Contents
•	Features
•	Architecture
•	Requirements
•	Installation
•	API Endpoints
•	Default Users
•	Project Structure
•	Artisan Commands
•	Adding a New Tenant
________________________________________
Features
•	Laravel 12
•	Laravel Sanctum Authentication
•	Multi-Tenant Architecture
•	Separate Database Per Tenant
•	Central Tenant Registry
•	Automatic Tenant Detection
•	Dynamic Database Switching
•	Token Storage Inside Tenant Database
•	Custom Artisan Command for Tenant Migrations
•	Seeder Support for Multiple Tenants
________________________________________
Architecture
                Central Database
             (multi_tenant_db)

          +----------------------+
          |      clients table   |
          +----------------------+
                  │
      Stores tenant database details
                  │
    ┌─────────────┼──────────────┐
    │             │              │
    ▼             ▼              ▼

+------------+ +------------+ +---------------+
|   ibm_db   | |   hcl_db   | |  infosys_db   |
+------------+ +------------+ +---------------+
| users      | | users      | | users         |
| tokens     | | tokens     | | tokens        |
+------------+ +------------+ +---------------+
Authentication Flow
1.	User submits email and password
2.	System scans all registered tenant databases
3.	Matching user is located
4.	Database connection switches dynamically
5.	Credentials are validated
6.	Sanctum token is created inside the tenant database
7.	Every authenticated request automatically reconnects to the correct tenant database
No client_code or tenant identifier is required during login.
________________________________________
Requirements
•	PHP 8.2+
•	Composer
•	MySQL
•	Laravel 12
•	XAMPP / Laragon / Valet / Docker (optional)
________________________________________
Installation
1. Clone the Repository
git clone <repository-url>
cd multi-tenant
________________________________________
2. Install Dependencies
composer install
________________________________________
3. Create Environment File
cp .env.example .env
________________________________________
4. Generate Application Key
php artisan key:generate
________________________________________
5. Configure the Central Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multi_tenant_db
DB_USERNAME=root
DB_PASSWORD=
Configure the shared tenant database connection:
TENANT_DB_HOST=127.0.0.1
TENANT_DB_PORT=3306
TENANT_DB_USERNAME=root
TENANT_DB_PASSWORD=
________________________________________
6. Create Databases
CREATE DATABASE multi_tenant_db;
CREATE DATABASE ibm_db;
CREATE DATABASE hcl_db;
CREATE DATABASE infosys_db;
________________________________________
7. Run Central Migration
php artisan migrate
________________________________________
8. Seed Tenant Registry
php artisan db:seed --class=ClientSeeder
This registers all tenants inside the central database.
________________________________________
9. Run Tenant Migrations
php artisan tenants:migrate
This creates tenant tables including:
•	users
•	personal_access_tokens
•	password_reset_tokens
•	cache
•	jobs
•	etc.
________________________________________
10. Seed Tenant Users
Seed individual tenants:
php artisan db:seed --class=IBMUserSeeder

php artisan db:seed --class=HCLUserSeeder

php artisan db:seed --class=InfosysUserSeeder
Or seed everything:
php artisan db:seed
________________________________________
11. Start the Development Server
php artisan serve
Application URL
http://127.0.0.1:8000
________________________________________
API Endpoints
Login
POST
/api/login
Automatically identifies the tenant and authenticates the user.
Request
{
    "email": "ibmuser@gmail.com",
    "password": "12345678"
}
Success Response (200)
{
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxx",
    "user": {
        "id": 1,
        "name": "IBM User",
        "email": "ibmuser@gmail.com"
    }
}
Failed Response (401)
{
    "message": "Invalid credentials"
}
________________________________________
Logout
POST
/api/logout
Header
Authorization: Bearer {token}
Success Response
{
    "message": "Logged out successfully"
}
Unauthorized
{
    "message": "No token provided"
}
________________________________________
Default Users
Tenant	Email	Password	Database
IBM	ibmuser@gmail.com	12345678	ibm_db
HCL	hcluser@gmail.com	12345678	hcl_db
Infosys	infosysuser@gmail.com	12345678	infosys_db
________________________________________
Project Structure
app
├── Console
│   └── Commands
│       └── MigrateTenants.php
│
├── Http
│   └── Controllers
│       └── Api
│           └── AuthController.php
│
├── Models
│   ├── Client.php
│   └── User.php
│
├── Providers
│   └── AppServiceProvider.php
│
└── Services
    └── TenantService.php

database
├── migrations
└── seeders
    ├── ClientSeeder.php
    ├── IBMUserSeeder.php
    ├── HCLUserSeeder.php
    └── InfosysUserSeeder.php

config
└── database.php
________________________________________
Artisan Commands
Command	Description
php artisan migrate	Run central database migrations
php artisan tenants:migrate	Run migrations on all tenant databases
php artisan tenants:migrate --fresh	Fresh migrate all tenant databases
php artisan db:seed	Seed all databases
php artisan db:seed --class=ClientSeeder	Seed tenant registry
php artisan db:seed --class=IBMUserSeeder	Seed IBM users
php artisan db:seed --class=HCLUserSeeder	Seed HCL users
php artisan db:seed --class=InfosysUserSeeder	Seed Infosys users
________________________________________
Adding a New Tenant
Step 1
Create a new database.
CREATE DATABASE newclient_db;
________________________________________
Step 2
Register the tenant.
Client::create([
    'client_code' => 'NEWCLIENT',
    'db_server'   => '127.0.0.1',
    'db_port'     => '3306',
    'db_name'     => 'newclient_db',
    'db_user'     => 'root',
    'db_password' => '',
]);
________________________________________
Step 3
Run tenant migrations.
php artisan tenants:migrate
________________________________________
Step 4
Create a user seeder for the tenant and execute it.
________________________________________
Security
•	Tenant databases are fully isolated.
•	Authentication tokens are stored inside the tenant’s own database.
•	Database connections are switched dynamically based on the authenticated tenant.
•	No tenant identifier is exposed in the authentication API.
________________________________________
License
This project is open-source and available under the MIT License.
