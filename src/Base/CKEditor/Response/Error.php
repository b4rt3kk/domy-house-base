<?php
namespace Base\CKEditor\Response;

class Error
{
    public $message;
    
    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }
}
