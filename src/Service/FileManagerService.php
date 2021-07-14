<?php


namespace App\Service;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManagerService implements FileManagerServiceInterface
{
    private $postImageDirectory;

    public function __construct($postImageDirectory)
    {
        $this->postImageDirectory = $postImageDirectory;
    }

    /**
     * @return mixed
     */
    public function getPostImageDirectory()
    {
        return $this->postImageDirectory;
    }

    /**
     * @inheritDoc
     */
    public function imagePostUpload(UploadedFile $file): string
    {
        $fileName = uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->getPostImageDirectory(), $fileName);
        } catch (FileException $exception){
            return $exception;
        }

        return $fileName;
    }

    /**
     * @inheritDoc
     */
    public function removePostImage(string $fileName)
    {
        $fileSystem = new Filesystem();
        $fileImage = $this->getPostImageDirectory() . $fileName;
        try {
            $fileSystem->remove($fileImage);
        } catch (FileException $exception){
            echo $exception->getMessage();
        }
    }
}