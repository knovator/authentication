<?php

use Illuminate\Database\Seeder;
use Knovators\Masters\Models\Master;
use Knovators\Masters\Repository\MasterRepository;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class MasterSeeder
 */
class MasterSeeder extends Seeder
{

    protected $masterRepository;

    /**
     * MasterSeeder constructor.
     * @param MasterRepository $masterRepository
     */
    public function __construct(MasterRepository $masterRepository) {
        $this->masterRepository = $masterRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws RepositoryException
     * @throws ValidatorException
     */
    public function run() {


        foreach ($this->masters() as $master) {

            $parentMaster = $this->masterRepository->findBy('code', $master['code']);
            if (!$parentMaster) {
                $parentMaster = $this->masterRepository->create(Arr::except($master,
                    ['sub_masters']));
            }
            if (!empty($master['sub_masters'])) {


                foreach ($master['sub_masters'] as $subMaster) {
                    $childMaster = $this->masterRepository->findBy('code', $subMaster['code']);

                    if (!$childMaster) {
                        $subMaster['parent_id'] = $parentMaster->id;
                        $this->masterRepository->create($subMaster);
                    }

                }

            }

        }

    }


    /**
     * @return array
     */
    private function masters() {
        return [
            0 => [
                'name'        => 'Thread Type',
                'code'        => 'THREAD_TYPE',
                'is_active'   => true,
                'sub_masters' => [
                    0 => [
                        'name'      => 'Weft',
                        'code'      => 'WEFT',
                        'is_active' => true,
                    ],
                    1 => [
                        'name'      => 'Warf',
                        'code'      => 'WARF',
                        'is_active' => true,
                    ]
                ]
            ],
            1 => [
                'name'        => 'Thread Color',
                'code'        => 'THREAD_COLOR',
                'is_active'   => true,
                'sub_masters' => [
                    0 => [
                        'name'      => 'Red',
                        'code'      => '#FF0000',
                        'is_active' => true,
                    ],
                    1 => [
                        'name'      => 'Blue',
                        'code'      => '#0000FF',
                        'is_active' => true,
                    ],
                    2 => [
                        'name'      => 'Yellow',
                        'code'      => '#FFFF00',
                        'is_active' => true,
                    ]
                ]
            ]
        ];
    }
}
