<?php

namespace Base\Route\Dynamic;

class Param
{
    protected $routePartIndex;
    
    protected $paramName;
    
    protected $paramValue;
    
    protected $value;
    
    public function getRoutePartIndex()
    {
        return $this->routePartIndex;
    }

    public function getParamName()
    {
        return $this->paramName;
    }

    public function getParamValue()
    {
        return $this->paramValue;
    }

    public function setRoutePartIndex($routePartIndex): void
    {
        $this->routePartIndex = $routePartIndex;
    }

    public function setParamName($paramName): void
    {
        $this->paramName = $paramName;
    }

    public function setParamValue($paramValue): void
    {
        $this->paramValue = $paramValue;
    }
    
    /**
     * @return \Base\Route\Dynamic\PlaceholderValue
     */
    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}
