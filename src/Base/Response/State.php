<?php

namespace Base\Response;

class State
{
    protected $isValid = true;
    
    protected $message;
    
    protected $assembledParams = [];
    
    public function getIsValid()
    {
        return $this->isValid;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setIsValid($isValid): void
    {
        $this->isValid = $isValid;
    }

    public function setMessage($message): void
    {
        $this->message = $message;
    }
    
    public function getAssembledParams()
    {
        return $this->assembledParams;
    }

    public function setAssembledParams($assembledParams): void
    {
        $this->assembledParams = $assembledParams;
    }

    public function setAssembledParam($name, $value)
    {
        $this->assembledParams[$name] = $value;
    }
}
