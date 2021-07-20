<?php


namespace App\Service\File;


use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileManagerInterface
{
    /**
     * @param UploadedFile $file
     * @param string $storageDirName
     * @return string
     */
    public function imageUpload(UploadedFile $file, string $storageDirName): string;

    /**
     * @param string $fileName
     * @param string $storageDirName
     * @return mixed
     */
    public function removeImage(string $fileName, string  $storageDirName);
}