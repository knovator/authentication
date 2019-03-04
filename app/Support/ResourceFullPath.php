<?php


namespace App\Support;

/**
 * Trait ResourceFullPath
 * @package App\Modules\Support
 */
trait ResourceFullPath
{

    protected $baseFolder = 'uploads/';

    /**
     * @param $filePath
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getPath($filePath) {
        $path = $this->getDomainPath();

        return $path . $filePath;
    }

    /**
     * @return string
     */
    private function getDomainPath() {
        return config('app.file_url') .'/'. $this->baseFolder;
    }
}
