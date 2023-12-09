<?php

namespace Base\Helper;

class NumberToWord
{
    const DEFAULT_LANGUAGE = 'PL';
    
    protected $language = self::DEFAULT_LANGUAGE;
    
    protected $currencySymbol = 'PLN';
    
    protected $currencySymbolMinuscule = 'gr';
    
        
    public function __invoke($number, $language = null)
    {
        if (empty($language)) {
            $language = $this->getLanguage();
        }
        
        $firstPart = floor($number);
        $secondPart = (number_format($number - $firstPart, 2)) * 100;
        
        $formatter = new \NumberFormatter($language, \NumberFormatter::SPELLOUT);
        
        $firstPartInWords = $formatter->format($firstPart);
        $secondPartInWords = $formatter->format($secondPart);
        
        return $firstPartInWords . ' ' . $this->getCurrencySymbol() . ' ' . $secondPartInWords . ' ' . $this->getCurrencySymbolMinuscule();
    }
    
    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language): void
    {
        $this->language = $language;
    }
    
    public function getCurrencySymbol()
    {
        return $this->currencySymbol;
    }

    public function setCurrencySymbol($currencySymbol): void
    {
        $this->currencySymbol = $currencySymbol;
    }
    
    public function getCurrencySymbolMinuscule()
    {
        return $this->currencySymbolMinuscule;
    }

    public function setCurrencySymbolMinuscule($currencySymbolMinuscule): void
    {
        $this->currencySymbolMinuscule = $currencySymbolMinuscule;
    }
}
