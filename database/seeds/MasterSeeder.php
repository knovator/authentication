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
                        'name'      => 'Warp',
                        'code'      => 'WARP',
                        'is_active' => true,
                    ]
                ]
            ],
            1 => [
                'name'        => 'Color',
                'code'        => 'COLOR',
                'is_active'   => true,
                'sub_masters' => [

                ]
            ],
            2 => [
                'name'        => 'Purchase Order Status',
                'code'        => 'PURCHASE_STATUS',
                'is_active'   => true,
                'sub_masters' => [
                    0 => [
                        'name'      => 'Pending',
                        'code'      => 'PO_PENDING',
                        'is_active' => true,
                    ],
                    1 => [
                        'name'      => 'Delivered',
                        'code'      => 'PO_DELIVERED',
                        'is_active' => true,
                    ],
                    2 => [
                        'name'      => 'Canceled',
                        'code'      => 'PO_CANCELED',
                        'is_active' => true,
                    ],
                ]
            ],
            3 => [
                'name'        => 'Sales Order Status',
                'code'        => 'SALES_STATUS',
                'is_active'   => true,
                'sub_masters' => [
                    0 => [
                        'name'      => 'Pending',
                        'code'      => 'SO_PENDING',
                        'is_active' => true,
                    ],
                    1 => [
                        'name'      => 'Manufacturing',
                        'code'      => 'SO_MANUFACTURING',
                        'is_active' => true,
                    ],
                    2 => [
                        'name'      => 'Delivered',
                        'code'      => 'SO_DELIVERED',
                        'is_active' => true,
                    ],
                ]
            ]
        ];
    }
}
