<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\TenantSeeder;
use Spatie\Permission\Models\Role;
use Database\Seeders\MessageChatSeeder;
use Database\Seeders\PublicGroupSeeder;
use Database\Seeders\PublicMessageGroupSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    
        $this->call([
            TenantSeeder::class,
            UserSeeder::class,
            MessageChatSeeder::class,
            PublicGroupSeeder::class,
            PublicMessageGroupSeeder::class,
        ]);
        Role::create(['name' => 'superadmin']);
        Role::create(['name' => 'group admin']);
        Role::create(['name' => 'normal user']);


    }
}
