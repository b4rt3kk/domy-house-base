<?php
namespace Base\Route\Dynamic;

class PlaceholderValue
{
    protected $value;
    
    protected $name;
    
    protected $params = [];
    
    protected $parentValue;
    
    protected $placeholderName;
    
    public function getValue()
    {
        return $this->value;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function setParams($params): void
    {
        foreach ($params as $name => $value) {
            $this->setParam($name, $value);
        }
    }
    
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }
    
    public function getPlaceholderName()
    {
        return $this->placeholderName;
    }

    public function setPlaceholderName($placeholderName): void
    {
        $this->placeholderName = $placeholderName;
    }
    
    public function getParamByName($name)
    {
        $params = $this->getParams();
        
        return isset($params[$name]) ? $params[$name] : null;
    }
    
    /**
     * Sprawdź czy istnieje parametr o podanej nazwie z wskazaną wartością
     * @param string $paramName
     * @param string $paramValue
     * @return boolean
     */
    public function hasParamWithValue($paramName, $paramValue)
    {
        $param = $this->getParamByName($paramName);
        
        return $param === $paramValue;
    }
    
    /**
     * @return \Base\Route\Dynamic\PlaceholderValue
     */
    public function getParentValue()
    {
        return $this->parentValue;
    }

    public function setParentValue($parentValue): void
    {
        $this->parentValue = $parentValue;
    }
    
    /**
     * Sprawdź czy obecna wartość posiada wartość nadrzędną
     * @return bool
     */
    public function hasParentValue()
    {
        return !empty($this->getParentValue());
    }
    
    /**
     * Czy wartość posiada wartość nadrzędną o podanej nazwie placeholdera
     * @param string $placeholderName
     * @return bool
     */
    public function hasParentValueWithPlaceholderName($placeholderName)
    {
        $parentValue = $this->getParentValue();
        
        return $parentValue instanceof PlaceholderValue && $parentValue->getPlaceholderName() === $placeholderName;
    }
}
