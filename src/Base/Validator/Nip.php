<?php

namespace Base\Validator;

class Nip extends \Laminas\Validator\AbstractValidator
{    
    public const INVALID_NIP = 'invalidNip';
    public const INVALID_NIP_LENGTH = 'invalidNipLength';
    public const INVALID_NIP_CHARACTERS = 'invalidNipCharacters';

    protected $messageTemplates = [
        self::INVALID_NIP => "Numer NIP jest nieprawidłowy",
        self::INVALID_NIP_LENGTH => "Numer NIP musi składać się dokładnie z 10 cyfr",
        self::INVALID_NIP_CHARACTERS => "Numer NIP może zawierać jedynie cyfry",
    ];

    public function isValid($value)
    {
        $this->setValue($value);
        $isValid = true;
        
        // sprawdzenie czy NIP zawiera jedynie cyfry
        if (!preg_match("#^[0-9]+$#", $value)) {
            $this->error(self::INVALID_NIP_CHARACTERS);
            $isValid = false;
        }
        
        // sprawdzenie czy NIP jest odpowiedniej długości
        if (strlen($value) !== 10) {
            $this->error(self::INVALID_NIP_LENGTH);
            $isValid = false;
        }
        
        if (!$this->isControlSumValid($value)) {
            $this->error(self::INVALID_NIP);
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
