<?php

use Illuminate\Database\Seeder;
use Knovators\Authentication\Models\Role;

/**
 * Class RoleSeeder
 */
class RoleSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $roles = [
            0 => [
                'name'   => 'Admin',
                'code'   => 'ADMIN',
                'weight' => 1
            ],
            1 => [
                'name'   => 'Designer',
                'code'   => 'DESIGNER',
                'weight' => 2
            ],
            2 => [
                'name'   => 'Accountant',
                'code'   => 'ACCOUNTANT',
                'weight' => 3
            ]
        ];


        foreach ($roles as $role) {
            $data = Role::where('name', $role['name'])->first();
            if (!$data) {
                Role::create($role);
            }
        }


    }
}
