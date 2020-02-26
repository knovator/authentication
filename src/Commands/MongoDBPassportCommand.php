<?php

namespace Knovators\Authentication\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Knovators\Authentication\Constants\Role as RoleConstant;
use Knovators\Authentication\Repository\PermissionRepository;
use Knovators\Authentication\Repository\RoleRepository;
use Prettus\Validator\Exceptions\ValidatorException;


/**
 * Class StoreRoutes
 * @package Knovators\Authentication\Commands
 */
class MongoDBPassportCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mongodb:passport
                            {--rollback : Rollback the Passport fix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes passport for MongoDB support';

    /**
     * MongoDB Model to use
     *
     * @var string
     */
    protected $mongoModel = 'Jenssegers\Mongodb\Eloquent\Model';

    /**
     * Laravel Eloquent Model to Replace with
     *
     * @var string
     */
    protected $laravelModel = 'Illuminate\Database\Eloquent\Model';

    /**
     * Passport vendor files location
     *
     * @var string
     */
    protected $passportPath = 'vendor/laravel/passport/src/';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        if (!$this->option('rollback')) {
            $this->fixFiles();
            $this->info("Passport Files have been fixed for MongoDB");
        } else {
            $this->rollbackFiles();
            $this->info("Passport Files have been rolled back for MongoDB");
        }
    }

    /**
     * Searches and fixes the passport files
     *
     * @return void
     */
    protected function fixFiles() {

        foreach (glob(base_path($this->passportPath) . '*.php') as $filename) {
            $file = file_get_contents($filename);
            file_put_contents($filename,
                str_replace($this->laravelModel, $this->mongoModel, $file));
        }

    }

    protected function rollbackFiles() {

        foreach (glob(base_path($this->passportPath) . '*.php') as $filename) {
            $file = file_get_contents($filename);
            file_put_contents($filename,
                str_replace($this->mongoModel, $this->laravelModel, $file));
        }

    }
}
