<?php

use App\Repositories\GenerateIdRepository;
use Illuminate\Database\Seeder;

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
     * @throws \Prettus\Validator\Exceptions\ValidatorException
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
                'count'  => 0
            ]
        ];
    }
}
