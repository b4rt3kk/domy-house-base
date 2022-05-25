<?php

namespace Base\Services\Payments\Przelewy24;

abstract class AbstractObject
{
    public function setData($data)
    {
        foreach ($data as $name => $value) {
            $methodName = 'set' . ucfirst($name);
            
            if (method_exists($this, $methodName)) {
                $this->{$methodName}($value);
            }
        }
    }

    public function getData()
    {
        $return = [];
        $reflectionClass = new \ReflectionClass($this);
        
        $properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);
        
        foreach ($properties as $property) {
            $name = $property->getName();
            if (!empty($this->{$name})) {
                $return[$name] = $this->{$name};
            }
        }
        
        return $return;
    }
    
    public function getDataObject()
    {
        $object = new \stdClass();
        $data = $this->getData();
        
        foreach ($data as $name => $value) {
            $object->{$name} = $value;
        }
        
        return $object;
    }
}
