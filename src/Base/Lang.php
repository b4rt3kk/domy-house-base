<?php

namespace Base;

class Lang extends \Base\Logic\AbstractLogic
{
    protected static $instance;
    
    protected $defaultLanguageSymbol = 'en_gb';
    
    protected $currentLanguageSymbol;
    
    /**
     * @return \Base\Lang
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof Lang) {
            self::$instance = new Lang();
        }
        
        return self::$instance;
    }
    
    public function getDefaultLanguageSymbol()
    {
        return $this->defaultLanguageSymbol;
    }

    public function getCurrentLanguageSymbol()
    {
        $languageSymbol = $this->currentLanguageSymbol;
        
        if (empty($languageSymbol)) {
            $languageSymbol = $this->getDefaultLanguageSymbol();
        }
        
        return $languageSymbol;
    }

    public function setDefaultLanguageSymbol($defaultLanguageSymbol)
    {
        $this->defaultLanguageSymbol = $defaultLanguageSymbol;
    }

    public function setCurrentLanguageSymbol($currentLanguageSymbol)
    {
        $this->currentLanguageSymbol = $currentLanguageSymbol;
    }
}
