<?php
namespace Base\CKEditor\Response;

class ImageUpload
{
    public $uploaded;
    
    public $fileName;
    
    public $url;
    
    /**
     * 
     * @var \Base\CKEditor\Response\Error
     */
    public $error;
    
    public function getUploaded()
    {
        return $this->uploaded;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getError(): \Base\CKEditor\Response\Error
    {
        return $this->error;
    }

    public function setUploaded($uploaded)
    {
        $this->uploaded = $uploaded;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setError(\Base\CKEditor\Response\Error $error)
    {
        $this->error = $error;
    }
    
    public function setErrorMessage($message)
    {
        $error = new Error();
        $error->setMessage($message);
        
        $this->setError($error);
    }
}
