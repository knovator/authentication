<?php

use Illuminate\Database\Seeder;

/**
 * Class DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {
//        $this->call(RoleSeeder::class);
        $this->call(MasterSeeder::class);
        $this->call(UniqueIdSeeder::class);
//        $this->call(StateSeeder::class);
//        $this->call(ManufacturingCompanySeeder::class);
    }
}
