<?php


use App\Modules\Thread\Models\Thread as ThreadAlias;
use App\Modules\Thread\Repositories\ThreadRepository;
use Illuminate\Database\Seeder;

/**
 * Class PartiallyUpdateSeeder
 */
class PartiallyUpdateSeeder extends Seeder
{

    protected $threadRepository;


    /**
     * PurchaseController constructor
     * @param ThreadRepository $threadRepository
     */
    public function __construct(
        ThreadRepository $threadRepository
    ) {
        $this->threadRepository = $threadRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $threads = $this->threadRepository->all();
        foreach ($threads as $thread) {
            /** @var ThreadAlias $thread */
            $thread->threadColors()->update(['is_active' => $thread->is_active]);

        }
    }


}
