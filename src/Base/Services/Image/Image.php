<?php

namespace Base\Services\Image;

class Image
{
    protected $filePath;
    
    protected $fileBody;
    
    protected $fileName;
    
    protected $fileExtension;
    
    protected $width;
    
    protected $height;
    
    public function getFilePath()
    {
        return $this->filePath;
    }

    public function getFileBody()
    {
        return $this->fileBody;
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    public function setFileBody($fileBody)
    {
        $this->fileBody = $fileBody;
    }
    
    public function getFileName()
    {
        return $this->fileName;
    }

    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }
}
