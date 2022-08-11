<?php
namespace Base\Route;

class RoutePart
{    
    protected $index;
    
    protected $rawString;
    
    protected $params = [];
    
    protected $placeholders = [];
    
    protected $placeholderExpression;

    public function getIndex()
    {
        return $this->index;
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }
    
    public function getRawString()
    {
        return $this->rawString;
    }

    public function setRawString($rawString)
    {
        $this->rawString = $rawString;
    }
    
    public function getParams()
    {
        return $this->params;
    }

    public function getPlaceholderExpression()
    {
        return $this->placeholderExpression;
    }

    public function setParams($params): void
    {
        $this->params = $params;
    }

    public function setPlaceholderExpression($placeholderExpression): void
    {
        $this->placeholderExpression = $placeholderExpression;
    }
        
    /**
     * Pobierz tablicę placeholderów dla tego kawałka route
     * @return \Base\Route\Placeholder[]
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    public function setPlaceholders($placeholders): void
    {
        foreach ($placeholders as $placeholder) {
            $this->addPlaceholder($placeholder);
        }
    }

    public function addPlaceholder(Placeholder $placeholder)
    {
        $this->placeholders[] = $placeholder;
    }
    
    public function hasPlaceholders()
    {
        return !empty($this->placeholders);
    }
    
    /**
     * Pobierz wszystkie możliwe wartości dla tego route part
     * @return array
     */
    public function getAllPossibleRouteValues()
    {
        $return = [];
        $rawString = $this->getRawString();
        
        if (!$this->hasPlaceholders()) {
            // route nie posiada placeholderów, więc jedyna możliwość to ten sam string route
            return [$this->getRawString()];
        }
        
        $placeholders = $this->getPlaceholders();
        
        foreach ($placeholders as $placeholder) {
            $values = $placeholder->getValuesData();
            
            foreach ($values as $index => $value) {
                $return[$index] = $rawString;
                $return[$index] = str_replace($placeholder->getName(), $value, $return[$index]);
            }
        }
        
        return $return;
    }
}
