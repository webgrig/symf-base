<?php


namespace App\Service\File;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager implements FileManagerInterface
{
    /**
     * @param string $storageDir
     * @param UploadedFile $file
     * @return string
     * @throws \Exception
     */
    public function upload(string  $storageDir, UploadedFile $file): string
    {
        $fileName = bin2hex(random_bytes(15)) . '.' . $file->guessExtension();

        try {
            $file->move($storageDir, $fileName);
        } catch (FileException $exception){
            return $exception->getMessage();
        }

        return $fileName;
    }

    /**
     * @param string $storageDir
     * @param string $fileName
     * @return mixed|void
     */
    public function remove(string  $storageDir, string $fileName)
    {
        $fileSystem = new Filesystem();
        $fileImage = $storageDir . $fileName;
        try {
            $fileSystem->remove($fileImage);
        } catch (FileException $exception){
            echo $exception->getMessage();
        }
    }
}