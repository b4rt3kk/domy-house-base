<?php
namespace Base\Route\Dynamic;

class RoutePart
{
    /**
     * Oryginalna wartość stringa bez obróbki
     * @var string
     */
    protected $rawString;
    
    /**
     * Wartość stringa po obróbce, dostosowana do użycia jako route part
     * @var string
     */
    protected $string;
    
    /**
     * Oczekiwana wartość route part, podczas poszukiwania pasującego route musi się zgadzać z wartością tego route
     * @var string
     */
    protected $values = [];
    
    /**
     * Kolejność RoutePart w całym stringu liczona od zera
     * @var integer
     */
    protected $index;
    
    public function __construct($rawString)
    {
        if (!empty($rawString)) {
            $this->setRawString($rawString);
            // string pozbawiony wartości parametrów, dostosowany do wyszukiwania
            $this->setString($this->normalizeStringAndAssignValues($rawString));
        }
    }
    
    public function getRawString()
    {
        return $this->rawString;
    }

    public function getString()
    {
        return $this->string;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setRawString($rawString): void
    {
        $this->rawString = $rawString;
    }

    public function setString($string): void
    {
        $this->string = $string;
    }

    public function setValues($values): void
    {
        $this->values = $values;
    }
    
    public function setValue($name, $value)
    {
        $this->values[$name] = $value;
    }
    
    public function getValue($name)
    {
        $values = $this->getValues();
        
        return array_key_exists($name, $values) ? $values[$name] : null; 
    }
    
    public function isValueMatching($name, $value)
    {
        $data = $this->getValue($name);
        
        return $data === $value;
    }
    
    /**
     * Sprawdź czy route part posiada przypisane określone wartości 
     * @return boolean
     */
    public function hasSpecifiedValues()
    {
        $return = false;
        $data = $this->getValues();
        
        foreach ($data as $value) {
            if (isset($value)) {
                $return = true;
            }
        }
        
        return $return;
    }
    
    public function getIndex()
    {
        return $this->index;
    }

    public function setIndex($index): void
    {
        $this->index = $index;
    }
    
    /**
     * Pobierz placeholdery ze stringa RoutePart
     * @return array
     */
    public function getPlaceholdersNamesFromString()
    {
        return $this->getPlaceholdersFromString($this->getString());
    }
    
    public function __toString()
    {
        return $this->getString();
    }
    
    /**
     * Zwróć string, który może być wykorzystany jako route part, wyłuskując przekazane wartości placeholderów o ile je przekazano i zapisując je w $values
     * @param string $string
     * @return string
     */
    protected function normalizeStringAndAssignValues($string)
    {
        $return = $string;
        $placeholders = $this->getPlaceholdersFromString($string);
        
        foreach ($placeholders as $placeholder) {
            $placeholderName = str_replace(['{', '}'], '', $placeholder);
            
            
            if (strpos($placeholderName, '=') !== false) {
                $tmp = explode('=', $placeholderName);
                $rawPlaceholderName = '{' . $tmp[0] . '}';
                $value = $tmp[1];
                
                $return = str_replace($placeholder, $rawPlaceholderName, $return);
                $this->setValue($rawPlaceholderName, $value);
            }
        }
        
        return $return;
    }
    
    /**
     * Pobierz listę placeholderów ze stringa
     * @param string $string
     * @return array
     */
    protected function getPlaceholdersFromString($string)
    {
        // lista znalezionych placeholderów
        $matchedPlaceholders = [];
        
        // wyszukanie placeholderów
        preg_match_all("#\{[^\}]+\}#", $string, $matchedPlaceholders);
        
        return array_key_exists(0, $matchedPlaceholders) ? $matchedPlaceholders[0] : $matchedPlaceholders;
    }
}
