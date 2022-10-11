<?php
namespace Base\Route\Dynamic;

class Placeholder
{
    protected $name;
    
    protected $rawName;
    
    protected $values;
    
    /**
     * Nazwa placeholdera
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Base\Route\Dynamic\PlaceholderValue[]
     */
    public function getValues()
    {
        return $this->values;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function setValues($values): void
    {
        $this->values = $values;
    }
    
    /**
     * Czysta nazwa placeholdera
     * @return string
     */
    public function getRawName()
    {
        return $this->rawName;
    }

    public function setRawName($rawName): void
    {
        $this->rawName = $rawName;
    }
    
    public function addValue(PlaceholderValue $value)
    {
        $value->setPlaceholderName($this->getName());
        
        $this->values[] = $value;
    }
    
    /**
     * Pobierz obiekt wartości dla wskazanej wartości
     * @param string $value
     * @return \Base\Route\Dynamic\PlaceholderValue
     */
    public function getValueByValue($value)
    {
        $return = null;
        $values = $this->getValues();
        
        foreach ($values as $rowValue) {
            if ($rowValue->getValue() === $value) {
                $return = $rowValue;
                
                break;
            }
        }
        
        return $return;
    }
    
    public function hasValue($value)
    {
        $rowValue = $this->getValueByValue($value);
        
        return !empty($rowValue);
    }
    
    /**
     * Pobierz \Base\Route\Dynamic\PlaceholderValue na podstawie wartości parametru
     * @param string $paramName
     * @param mixed $paramValue
     * @return \Base\Route\Dynamic\PlaceholderValue
     */
    public function getValueByParam($paramName, $paramValue)
    {
        $return = null;
        $values = $this->getValues();
        
        foreach ($values as $value) {
            if ($value->hasParamWithValue($paramName, $paramValue)) {
                $return = $value;
                
                break;
            }
        }
        
        return $return;
    }
}
