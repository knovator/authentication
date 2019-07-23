<?php

use App\Modules\Sales\Models\ManufacturingCompany;
use Illuminate\Database\Seeder;

class ManufacturingCompanySeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $companies = [
            0 => [
                'name' => 'SIDDHI TEXO FAB',
            ],
            1 => [
                'name' => 'JENNY TEXO FAB',
            ]
        ];


        foreach ($companies as $company) {
            $data = ManufacturingCompany::where('name', $company['name'])->first();
            if (!$data) {
                ManufacturingCompany::create($company);
            }
        }


    }
}
