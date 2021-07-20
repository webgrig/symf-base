<?php


namespace App\Service\File;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager implements FileManagerInterface
{
    private $userImgDirectory;
    private $postImgDirectory;

    public function __construct($userImgDirectory, $postImgDirectory)
    {
        $this->userImgDirectory = $userImgDirectory;
        $this->postImgDirectory = $postImgDirectory;
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function getImageDirectory(string $storageDirName): string
    {
        $directoryProperty = $storageDirName . 'ImgDirectory';
        return $this->$directoryProperty;
    }

    /**
     * @param UploadedFile $file
     * @param string $storageDirName
     * @return string
     */
    public function imageUpload(UploadedFile $file, string  $storageDirName): string
    {
        $fileName = bin2hex(random_bytes(15)) . '.' . $file->guessExtension();

        try {
            $file->move($this->getImageDirectory($storageDirName), $fileName);
        } catch (FileException $exception){
            return $exception;
        }

        return $fileName;
    }

    /**
     * @inheritDoc
     */
    public function removeImage(string $fileName, string  $storageDirName)
    {
        $fileSystem = new Filesystem();
        $fileImage = $this->getImageDirectory($storageDirName) . $fileName;
        try {
            $fileSystem->remove($fileImage);
        } catch (FileException $exception){
            echo $exception->getMessage();
        }
    }
}