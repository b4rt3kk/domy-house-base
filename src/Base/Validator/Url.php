<?php

namespace Base\Validator;

class Url extends \Laminas\Validator\AbstractValidator
{    
    public const INVALID_URL = 'invalidUrl';

    protected $messageTemplates = [
        self::INVALID_URL => "Nieprawidłowy adres url",
        
    ];

    public function isValid($value)
    {
        $this->setValue($value);
        $isValid = true;
        
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->error(self::INVALID_URL);
            $isValid = false;
        }
        
        return $isValid;
    }
}
