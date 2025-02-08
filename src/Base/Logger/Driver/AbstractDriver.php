<?php

namespace Base\Logger\Driver;

abstract class AbstractDriver
{
    use \Base\Traits\ServiceManagerTrait;
    
    protected $code;
    
    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }
    
    abstract public function logMessage($message, $messageType, $additionalData = []);
}

