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
//        $this->call(MasterSeeder::class);
//        $this->call(UniqueIdSeeder::class);
//        $this->call(StateSeeder::class);
//        $this->call(ManufacturingCompanySeeder::class);
//        $this->call(MachineChangeSeeder::class);
//        $this->call(OrderQuantitySeeder::class);
//        $this->call(PartiallyUpdateSeeder::class);
        $this->call(CreatePermissionSeeder::class);

        $this->call(DeliveryUpdateSeeder::class);
    }
}
