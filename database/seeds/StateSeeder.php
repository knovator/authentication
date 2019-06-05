<?php

use App\Repositories\StateRepository;
use DougSisk\CountryState\CountryState;
use Illuminate\Database\Seeder;

/**
 * Class StateSeeder
 */
class StateSeeder extends Seeder
{


    protected $stateRepository;

    /**
     * StateSeeder constructor.
     * @param StateRepository $stateRepository
     */
    public function __construct(StateRepository $stateRepository) {
        $this->stateRepository = $stateRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function run() {

        $countryState = new CountryState();
        $states = $countryState->getStates('IN');
        foreach ($states as $isoCode => $stateName) {
            $code = strtoupper($stateName);
            $this->stateRepository->updateOrCreate(['code' => $code], [
                'code'      => $code,
                'name'      => $stateName,
                'is_active' => true
            ]);
        }
    }
}
