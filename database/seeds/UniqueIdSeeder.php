<?php

use App\Repositories\GenerateIdRepository;
use Illuminate\Database\Seeder;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class UniqueIdSeeder
 */
class UniqueIdSeeder extends Seeder
{

    protected $generateIdRepository;

    /**
     * MasterSeeder constructor.
     * @param GenerateIdRepository $generateIdRepository
     */
    public function __construct(GenerateIdRepository $generateIdRepository) {
        $this->generateIdRepository = $generateIdRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws ValidatorException
     */
    public function run() {

        foreach ($this->uniqueIds() as $data) {
            $this->generateIdRepository->updateOrCreate(['code' => $data['code']], $data);
        }
    }


    /**
     * @return array
     */
    private function uniqueIds() {
        return [
            0 => [
                'code'   => 'DESIGN',
                'prefix' => 'DZ',
            ],
            1 => [
                'code'   => 'PURCHASE',
                'prefix' => 'PO'
            ],
            2 => [
                'code'   => 'SALES',
                'prefix' => 'SO'
            ],
            3 => [
                'code'   => 'DELIVERY',
                'prefix' => 'DN'
            ],
            4 => [
                'code'   => 'YARN_SALES',
                'prefix' => 'YNSO'
            ],
            5 => [
                'code'   => 'PO_DELIVERY',
                'prefix' => 'PDN'
            ],
            6 => [
                'code'   => 'WASTAGE',
                'prefix' => 'WO'
            ],
        ];
    }
}
