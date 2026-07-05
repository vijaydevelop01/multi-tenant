# Multi-Tenant Laravel API

![Laravel](https://img.shields.io/badge/Laravel-12-red?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)
![Sanctum](https://img.shields.io/badge/Sanctum-Auth-orange?style=flat-square)
![MySQL](https://img.shields.io/badge/MySQL-Database-blue?style=flat-square&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

A **Multi-Tenant REST API** built with **Laravel 12** and **Laravel Sanctum**, where each client (tenant) has its own isolated MySQL database.

The application uses a **central database** to maintain tenant information, while all tenant-specific data (users, authentication tokens, etc.) is stored in dedicated tenant databases.

---

## Table of Contents

- [Features](#features)
- [Architecture](#architecture)
- [Requirements](#requirements)
- [Installation](#installation)
- [API Endpoints](#api-endpoints)
- [Default Users](#default-users)
- [Project Structure](#project-structure)
- [Artisan Commands](#artisan-commands)
- [Adding a New Tenant](#adding-a-new-tenant)
- [Security](#security)
- [License](#license)

---

## Features

- Laravel 12
- Laravel Sanctum Authentication
- Multi-Tenant Architecture
- Separate Database Per Tenant
- Central Tenant Registry
- Automatic Tenant Detection
- Dynamic Database Switching
- Token Storage Inside Tenant Database
- Custom Artisan Command for Tenant Migrations
- Seeder Support for Multiple Tenants

---

## Architecture

```
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
```

### Authentication Flow

1. User submits email and password
2. System scans all registered tenant databases
3. Matching user is located
4. Database connection switches dynamically
5. Credentials are validated
6. Sanctum token is created inside the tenant database
7. Every authenticated request automatically reconnects to the correct tenant database

> **Note:** No `client_code` or tenant identifier is required during login.

---

## Requirements

- PHP 8.2+
- Composer
- MySQL
- Laravel 12
- XAMPP / Laragon / Valet / Docker (optional)

---

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd multi-tenant
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Create Environment File

```bash
cp .env.example .env
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Configure the Central Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multi_tenant_db
DB_USERNAME=root
DB_PASSWORD=
```

Configure the shared tenant database connection:

```env
TENANT_DB_HOST=127.0.0.1
TENANT_DB_PORT=3306
TENANT_DB_USERNAME=root
TENANT_DB_PASSWORD=
```

### 6. Create Databases

```sql
CREATE DATABASE multi_tenant_db;
CREATE DATABASE ibm_db;
CREATE DATABASE hcl_db;
CREATE DATABASE infosys_db;
```

### 7. Run Central Migration

```bash
php artisan migrate
```

### 8. Seed Tenant Registry

```bash
php artisan db:seed --class=ClientSeeder
```

This registers all tenants inside the central database.

### 9. Run Tenant Migrations

```bash
php artisan tenants:migrate
```

This creates tenant tables including:

- `users`
- `personal_access_tokens`
- `password_reset_tokens`
- `cache`
- `jobs`
- etc.

### 10. Seed Tenant Users

Seed individual tenants:

```bash
php artisan db:seed --class=IBMUserSeeder
php artisan db:seed --class=HCLUserSeeder
php artisan db:seed --class=InfosysUserSeeder
```

Or seed everything:

```bash
php artisan db:seed
```

### 11. Start the Development Server

```bash
php artisan serve
```

**Application URL:** [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## API Endpoints

### Login

| Method | Endpoint     |
|--------|--------------|
| `POST` | `/api/login` |

Automatically identifies the tenant and authenticates the user.

**Request**

```json
{
    "email": "ibmuser@gmail.com",
    "password": "12345678"
}
```

**Success Response (200)**

```json
{
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxx",
    "user": {
        "id": 1,
        "name": "IBM User",
        "email": "ibmuser@gmail.com"
    }
}
```

**Failed Response (401)**

```json
{
    "message": "Invalid credentials"
}
```

---

### Logout

| Method | Endpoint      |
|--------|---------------|
| `POST` | `/api/logout` |

**Header**

```
Authorization: Bearer {token}
```

**Success Response**

```json
{
    "message": "Logged out successfully"
}
```

**Unauthorized**

```json
{
    "message": "No token provided"
}
```

---

## Default Users

| Tenant  | Email                   | Password | Database    |
|---------|-------------------------|----------|-------------|
| IBM     | ibmuser@gmail.com       | 12345678 | ibm_db      |
| HCL     | hcluser@gmail.com       | 12345678 | hcl_db      |
| Infosys | infosysuser@gmail.com   | 12345678 | infosys_db  |

---

## Project Structure

```
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
```

---

## Artisan Commands

| Command | Description |
|---------|-------------|
| `php artisan migrate` | Run central database migrations |
| `php artisan tenants:migrate` | Run migrations on all tenant databases |
| `php artisan tenants:migrate --fresh` | Fresh migrate all tenant databases |
| `php artisan db:seed` | Seed all databases |
| `php artisan db:seed --class=ClientSeeder` | Seed tenant registry |
| `php artisan db:seed --class=IBMUserSeeder` | Seed IBM users |
| `php artisan db:seed --class=HCLUserSeeder` | Seed HCL users |
| `php artisan db:seed --class=InfosysUserSeeder` | Seed Infosys users |

---

## Adding a New Tenant

### Step 1 — Create a new database

```sql
CREATE DATABASE newclient_db;
```

### Step 2 — Register the tenant

```php
Client::create([
    'client_code' => 'NEWCLIENT',
    'db_server'   => '127.0.0.1',
    'db_port'     => '3306',
    'db_name'     => 'newclient_db',
    'db_user'     => 'root',
    'db_password' => '',
]);
```

### Step 3 — Run tenant migrations

```bash
php artisan tenants:migrate
```

### Step 4 — Create a user seeder for the tenant and execute it

---

## Security

- Tenant databases are fully isolated.
- Authentication tokens are stored inside the tenant's own database.
- Database connections are switched dynamically based on the authenticated tenant.
- No tenant identifier is exposed in the authentication API.

---

## License

This project is open-source and available under the [MIT License](LICENSE).
