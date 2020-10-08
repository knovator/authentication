<?php

namespace Knovators\Authentication\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Class MongoDBPassportCommand
 * @package App\Console\Commands
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
    protected $signature = 'fix:mongodb
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
    protected $mongoAuth = 'Jenssegers\Mongodb\Auth\User';

    /**
     * Laravel Eloquent Model to Replace with
     *
     * @var string
     */
    protected $laravelModel = 'Illuminate\Database\Eloquent\Model';
    protected $laravelAuth = 'Illuminate\Foundation\Auth\User';


    /**
     * Passport vendor files location
     *
     * @var string
     */
    protected $paths = [
        'vendor/laravel/passport/src/',
        'vendor/knovators/support/src/Models',
        'vendor/knovators/authentication/src/Models',
        'vendor/knovators/media/src/Models',
        'vendor/knovators/image-resize/src/Models',
        'vendor/knovators/masters/src/Models',
        'vendor/knovators/seo/src/Models'
    ];

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
        $this->modifyFiles($this->laravelModel, $this->mongoModel, $this->laravelAuth,
            $this->mongoAuth);
    }

    /**
     * @param $source
     * @param $target
     * @param $authFrom
     * @param $authTo
     */
    protected function modifyFiles($source, $target, $authFrom, $authTo) {
        foreach ($this->paths as $path) {
            if (File::isDirectory($path)) {
                $files = File::allfiles($path);
                foreach ($files as $filename) {
                    $str = file_get_contents($filename);
                    $str = str_replace($source, $target, $str);
                    $str = str_replace($authFrom, $authTo, $str);
                    file_put_contents($filename, $str);

                }
            }
        }
    }

    protected function rollbackFiles() {
        $this->modifyFiles($this->mongoModel, $this->laravelModel, $this->mongoAuth,
            $this->laravelAuth);
    }
}
