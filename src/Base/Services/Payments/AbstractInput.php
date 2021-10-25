<?php

namespace Base\Services\Payments;

abstract class AbstractInput
{
    protected $data = [];
    
    public function setData($data)
    {
        foreach ($data as $name => $value) {
            $this->setDataValue($name, $value);
        }
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Skonfiguruj wartości dla istniejących parametrów klasy
     * @param string $name
     * @param mixed $value
     */
    public function setDataValue($name, $value)
    {
        $normalizedName = $this->getNormalizedName($name);
        $methodName = 'set' . $normalizedName;

        if (method_exists($this, $methodName)) {
            $this->{$methodName}($value);
        }

        $this->data[$name] = $value;
    }

    public function getDataValue($name, $default = null)
    {
        $data = $this->getData();

        return array_key_exists($name, $data) ? $data[$name] : $default;
    }

    protected function getPropertiesNames()
    {
        $return = [];
        $reflectionClass = new \ReflectionClass($this);
        
        $properties = $reflectionClass->getProperties();
        
        foreach ($properties as $property) {
            /* @var $property \ReflectionProperty */
            $return[] = $property->getName();
        }
        
        return $return;
    }
    
    /**
     * Przekształć string na konwencję CamelCase z underscore
     * @param string $name
     * @return string
     */
    protected function getNormalizedName($name)
    {
        $chunks = explode('_', $name);
        
        $return = implode('', array_map('ucfirst', $chunks));
        
        return $return;
    }
    
    /**
     * Przekształć string na konwencję underscore z CamelCase
     * @param string $name
     * @return string
     */
    protected function getUnnormalizedName($name)
    {
        $chunks = preg_split('/(?=[A-Z])/',$name);
        
        $return = implode('_', array_map('uclower', $chunks));
        
        return $return;
    }
    
    /**
     * Przekonweruj string zamieniając znaczniki na wartości na podstawie metod klasy
     * @param string $string
     * @return string
     */
    protected function getConvertedString($string)
    {
        $return = $string;
        $matches = [];
        // wyłuskanie wszystkich parametrów ze stringa zawartch w [:znacznik]
        preg_match_all('#\[(\:[^\]]*)*\]#', $string, $matches);
        
        foreach ($matches[1] as $match) {
            $methodName = 'get' . ucfirst(str_replace(':', '', $match));
            
            if (method_exists($this, $methodName)) {
                $value = $this->{$methodName}();
                $return = str_replace('[' . $match . ']', $value, $return);
            }
        }
        
        return $return;
    }
}
