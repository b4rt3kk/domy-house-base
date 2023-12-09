<?php

namespace Base\Validator;

/**
 * Input przyjmujący jako wartość numer rachunku bankowego wg polskiego standardu
 */
class Nrb extends \Laminas\Validator\AbstractValidator
{    
    public const INVALID_ACCOUNT_NO = 'invalidAccountNo';
    public const INVALID_ACCOUNT_NO_LENGTH = 'invalidAccountNoLength';

    protected $messageTemplates = [
        self::INVALID_ACCOUNT_NO => "Numer konta jest nieprawidłowy",
        self::INVALID_ACCOUNT_NO_LENGTH => "Numer konta musi składać się dokładnie z 26 cyfr",
        
    ];

    public function isValid($value)
    {
        $this->setValue($value);
        $isValid = true;
        
        // sprawdzenie czy NRB (numer rachunku bankowego) jest odpowiedniej długości
        if (strlen($value) !== 26) {
            $this->error(self::INVALID_ACCOUNT_NO_LENGTH);
            $isValid = false;
        }
        
        if (!$this->isControlSumValid($value)) {
            $this->error(self::INVALID_ACCOUNT_NO);
            
            $isValid = false;
        }
        
        return $isValid;
    }
    
    protected function isControlSumValid($value)
    {
        // Dziesiąta cyfra NIP jest cyfrą kontrolną, obliczaną według poniższego algorytmu:
        // Pomnożyć każdą z pierwszych dziewięciu cyfr odpowiednio przez wagi: 6, 5, 7, 2, 3, 4, 5, 6, 7,
        // Zsumować wyniki mnożenia,
        // Obliczyć resztę z dzielenia przez 11 (operacja modulo 11).
        
        // zaczynamy liczyć od zera, więc cyfra kontrolna ma index 9

        $isValid = true;
        
        $multiplier = [
            6, 
            5, 
            7, 
            2, 
            3, 
            4, 
            5, 
            6, 
            7,
        ];
        
        $controlSum = 0;
        
        for ($i = 0; $i < 9; $i++) {
            $controlSum += $value[$i] * $multiplier[$i];
        }
        
        // reszta z dzielenia
        $reminder = $controlSum % 11;
        $checker = (int) $value[9];
        
        if ($reminder !== $checker) {
            $isValid = false;
        }
        
        return $isValid;
    }
}
