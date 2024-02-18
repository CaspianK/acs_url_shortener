<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperuserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create();

        $userRole = Role::whereTitle('user')->first();
        $superRole = Role::whereTitle('super')->first();

        $user->roles()->attach($userRole);
        $user->roles()->attach($superRole);
    }
}
