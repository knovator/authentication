<?php

namespace Knovators\Authentication\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Class MongoDBPassportCommand
 * @package App\Console\Commands
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
    protected $mongoModel = 'Jenssegers\Mongodb';

    /**
     * Laravel Eloquent Model to Replace with
     *
     * @var string
     */
    protected $laravelModel = 'Illuminate\Database';

    /**
     * Passport vendor files location
     *
     * @var string
     */
    protected $paths = [
        'vendor/laravel/passport/src/',
        'vendor/knovators/support/src',
        'vendor/knovators/authentication/src',
        'vendor/knovators/media/src
        '
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
        $this->modifyFiles($this->laravelModel, $this->mongoModel);
    }

    /**
     * @param $source
     * @param $target
     */
    protected function modifyFiles($source, $target) {
        foreach ($this->paths as $path) {
            if (File::isDirectory($path)) {
                $files = File::allfiles($path);
                foreach ($files as $filename) {
                    $file = file_get_contents($filename);
                    file_put_contents($filename,
                        str_replace($source, $target, $file));
                }
            }
        }
    }

    protected function rollbackFiles() {
        $this->modifyFiles($this->mongoModel, $this->laravelModel);
    }
}
