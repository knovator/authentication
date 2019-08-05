<?php

use App\Modules\Sales\Models\ManufacturingCompany;
use Illuminate\Database\Seeder;

/**
 * Class ManufacturingCompanySeeder
 */
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
                'name'       => 'SIDDHI TEXO FAB',
                'address'    => 'PLOT NO: M-3/6,7,8, ROAD NO: 23, VIBHAG-2, HOJIWALA IND. ESTATE, SACHIN',
                'country'    => 'INDIA',
                'state'      => 'GUJARAT',
                'state_code' => '24',
                'city'       => 'SURAT',
                'pin_code'   => '394230',
                'phone'      => '99049 19000, 93280 11244',
                'gst_no'     => '24AKNPC1189K1Z0',
            ],
            1 => [
                'name'       => 'JENNY TEXO FAB',
                'address'    => 'PLOT NO: M-3/9,10, ROAD NO: 23, VIBHAG-2, HOJIWALA IND, ESTATE, SACHIN',
                'country'    => 'INDIA',
                'state'      => 'GUJARAT',
                'state_code' => '24',
                'city'       => 'SURAT',
                'pin_code'   => '394230',
                'phone'      => '99049 19000, 93280 11244',
                'gst_no'     => '24AEZPC1677G1Z9',
            ],
        ];


        foreach ($companies as $company) {
            ManufacturingCompany::updateOrCreate(['name' => $company['name']], $company);
        }


    }
}
