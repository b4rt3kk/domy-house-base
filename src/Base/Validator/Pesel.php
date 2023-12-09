<?php

namespace Base\Validator;

class Pesel extends \Laminas\Validator\AbstractValidator
{    
    public const INVALID_PESEL = 'invalidPesel';
    public const INVALID_PESEL_LENGTH = 'invalidPeselLength';
    public const INVALID_PESEL_CHARACTERS = 'invalidPeselCharacters';

    protected $messageTemplates = [
        self::INVALID_PESEL => "Numer PESEL jest nieprawidłowy",
        self::INVALID_PESEL_LENGTH => "Numer PESEL musi składać się dokładnie z 11 cyfr",
        self::INVALID_PESEL_CHARACTERS => "Numer PESEL może zawierać jedynie cyfry",
    ];
    
    protected $weights = [
        0 => 1,
        1 => 3,
        2 => 7,
        3 => 9,
        4 => 1,
        5 => 3,
        6 => 7,
        7 => 9,
        8 => 1,
        9 => 3,
        10 => 1,
    ];

    public function isValid($value)
    {
        $this->setValue($value);
        $isValid = true;
        
        // sprawdzenie czy pesel zawiera jedynie cyfry
        if (!preg_match("#^[0-9]+$#", $value)) {
            $this->error(self::INVALID_PESEL_CHARACTERS);
            $isValid = false;
        }
        
        // sprawdzenie czy PESEL jest odpowiedniej długości
        if (strlen($value) !== 11) {
            $this->error(self::INVALID_PESEL_LENGTH);
            $isValid = false;
        }
        
        if (!$this->isControlSumValid($value)) {
            $this->error(self::INVALID_PESEL);
            $isValid = false;
        }
        
        return $isValid;
    }
    
    protected function isControlSumValid($value)
    {
        $isValid = true;
        
        $controlNumber = $value[10];
        
        // dla kolejnych dziesięciu cyfr identyfikatora PESEL obliczany jest iloczyn cyfry i jej wagi
        // obliczana jest suma tych iloczynów
        $weights = $this->getWeights();
        
        $sum = 0;
        
        for ($i = 0; $i < 10; $i++) {
            $sum += $weights[$i] * $value[$i];
        }
        
        // obliczana jest wartość M (modulo) operacji S (suma iloczynów) modulo 10
        $modulo = bcmod($sum, 10);
        
        // od liczby dziesięć odejmowana jest liczba M (modulo)
        $testNumber = 10 - $modulo;
        
        if ($testNumber != $controlNumber) {
            $isValid = false;
        }
        return $isValid;
    }
    
    protected function getWeights()
    {
        return $this->weights;
    }

    protected function setWeights($weights): void
    {
        $this->weights = $weights;
    }


}
