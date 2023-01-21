<?php
namespace Base\Route\Dynamic;

class RouteString
{
    protected $string;
    
    protected $rawString;
    
    protected $values = [];
    
    public function getString()
    {
        return $this->string;
    }

    public function getRawString()
    {
        return $this->rawString;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setString($string): void
    {
        $this->string = $string;
    }

    public function setRawString($rawString): void
    {
        $this->rawString = $rawString;
    }

    public function setValues($values): void
    {
        $this->values = $values;
    }
}
