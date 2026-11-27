<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Ugm\Admin\Database\Seeders\AdminUserSeeder;
use Ugm\User\Database\Seeders\DocumentsTypeSeeder;
use Ugm\User\Database\Seeders\UserStatesSeeder;
use Ugm\User\Database\Seeders\UsersTypeSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');

        if (Schema::hasTable($prefix.'users_type')) {
            require_once base_path('packages/core/User/database/seeders/UsersTypeSeeder.php');
            (new UsersTypeSeeder)->setContainer($this->container)->setCommand($this->command)->__invoke();
        }

        if (Schema::hasTable($prefix.'user_states')) {
            require_once base_path('packages/core/User/database/seeders/UserStatesSeeder.php');
            (new UserStatesSeeder)->setContainer($this->container)->setCommand($this->command)->__invoke();
        }

        if (Schema::hasTable($prefix.'documents_type')) {
            require_once base_path('packages/core/User/database/seeders/DocumentsTypeSeeder.php');
            (new DocumentsTypeSeeder)->setContainer($this->container)->setCommand($this->command)->__invoke();
        }

        if (app()->environment(['local', 'testing']) && Schema::hasTable($prefix.'admin_users')) {
            require_once base_path('packages/core/Admin/database/seeders/AdminUserSeeder.php');
            (new AdminUserSeeder)->setContainer($this->container)->setCommand($this->command)->__invoke();
        }
    }
}
