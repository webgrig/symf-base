<?php


namespace App\Service\File;


use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileManagerInterface
{
    /**
     * @param string $storageDir
     * @param UploadedFile $file
     * @return string
     */
    public function upload(string $storageDir, UploadedFile $file): string;

    /**
     * @param string $storageDir
     * @param string $fileName
     * @return mixed
     */
    public function remove(string  $storageDir, string $fileName);
}