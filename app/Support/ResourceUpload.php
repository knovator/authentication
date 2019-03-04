<?php


namespace App\Support;

use Carbon\Carbon;
use File;
use Illuminate\Support\Str;
use Storage;

/**
 * Trait ResourceUpload
 * @package App\Modules\Support
 */
trait ResourceUpload
{

    protected $basePath = '/uploads/';


    protected $driver = 'public';


    /**
     * @return string
     */
    public function getBasePath() {
        return $this->basePath;
    }

    /**
     * @param $folder
     * @param $file
     * @param $label
     * @return string
     */
    public function uploadResource($folder, $file, $label = false) {
        $folderName = $folder;
        $path = $this->getBasePath() . $folderName;
        $fileName = $this->createFileName($file->getClientOriginalName(), $label);
        $file->storeAs($path, $fileName, 'public');
        $imagePath = $folderName . '/' . $fileName;

        return $imagePath;
    }

    /**
     * @param $fileOriginalName
     * @param $label
     * @return string
     */
    public function createFileName($fileOriginalName, $label) {
        $fileName = Carbon::now()->timestamp . '-' . $fileOriginalName;
        if ($label) {
            $fileName = $label . '-' . $fileName;
        }

        return str_replace(' ', '-', $fileName);
    }


    /**
     * @param $image
     */
    public function removeResource($image) {
        $path = $this->getBasePath();
        $this->getResourceDriver()->delete($path . $image);
    }

    /**
     * @return \Illuminate\Filesystem\FilesystemAdapter
     */
    public function getResourceDriver() {
        return Storage::disk($this->driver);
    }

    /**
     * @param $oldFilePath
     * @param $folder
     * @return string
     */
    public function copyResource($oldFilePath, $folder) {
        $path = $this->getBasePath();
        $newFilePath = $folder . '/' . substr(strrchr($oldFilePath, "/"), 1);
        $this->getResourceDriver()->copy($path . $oldFilePath, $path . $newFilePath);

        return $newFilePath;
    }
}
