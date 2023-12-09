<?php

namespace Base\Validator;

class AccountNumber extends \Laminas\Validator\AbstractValidator
{    
    const DEFAULT_COUNTRY_CODE = 'PL';
    
    public const INVALID_ACCOUNT_NO = 'invalidAccountNo';
    public const INVALID_ACCOUNT_NO_LENGTH = 'invalidAccountNoLength';

    protected $messageTemplates = [
        self::INVALID_ACCOUNT_NO => "Numer konta jest nieprawidłowy",
        self::INVALID_ACCOUNT_NO_LENGTH => "Numer konta musi składać się z %length% cyfr",
        
    ];
    
    /** @var string[] */
    protected $messageVariables = [
        'length' => 'length',
    ];
    
    protected $countryCode = self::DEFAULT_COUNTRY_CODE;
    
    protected $length;
    
    /**
     * Prawidłowa długość numeru konta bankowego (wraz z kodem kraju) w zależności od kodu kraju
     * @var array
     */
    protected array $countries = [
        'PL' => 28,
    ];
    
    protected $letterValues = [
        'A' => 10,
        'B' => 11,
        'C' => 12,
        'D' => 13,
        'E' => 14,
        'F' => 15,
        'G' => 16,
        'H' => 17,
        'I' => 18,
        'J' => 19,
        'K' => 20,
        'L' => 21,
        'M' => 22,
        'N' => 23,
        'O' => 24,
        'P' => 25,
        'Q' => 26,
        'R' => 27,
        'S' => 28,
        'T' => 29,
        'U' => 30,
        'V' => 31,
        'W' => 32,
        'X' => 33,
        'Y' => 34,
        'Z' => 35,
    ];
    
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    public function setCountryCode($countryCode)
    {
        $countries = $this->getCountries();
        
        if (!in_array(strtoupper($countryCode), $countries)) {
            throw new \Exception(sprintf("Kraj o kodzie %s nie jest obsługiwany przez walidator", $countryCode));
        }
        
        $this->countryCode = strtoupper($countryCode);
    }

    public function isValid($value)
    {
        $countryCode = $this->getCountryCode();
        $countries = $this->getCountries();
        
        $this->setValue($value);
        $isValid = true;
        
        // sprawdzenie czy numer konta jest odpowiedniej długości
        $accountNumberLength = $countries[$countryCode] - strlen($countryCode);
        $this->setLength($accountNumberLength);
        
        if (strlen($value) !== $accountNumberLength) {
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
        //Algorytm sprawdzania cyfr kontrolnych:
        //Weź pełny numer konta (razem z kodem kraju), bez spacji.
        //Sprawdź, czy zgadza się długość numeru dla danego kraju.
        //Przenieś 4 pierwsze znaki numeru na jego koniec.
        //Przekształć litery w numerze konta na ciągi cyfr, zamieniając 'A' na '10', 'B' na '11' itd., aż do 'Z' na '35' (dla Polski 2521).
        //Potraktuj otrzymany ciąg znaków jak liczbę i wylicz resztę z dzielenia przez 97.
        //Jeśli reszta jest równa 1, to numer konta ma prawidłowe cyfry kontrolne.

        $isValid = true;
        
        $countryCode = $this->getCountryCode();
        
        //Przekształć litery w numerze konta na ciągi cyfr
        $countryCodeAsNumber = null;
        
        for ($i = 0; $i < strlen($countryCode); $i++) {
            $countryCodeAsNumber .= $this->getLetterValue($countryCode[$i]);
        }
        
        $start = $countryCodeAsNumber . substr($value, 0, 2);
        
        //Przenieś 4 pierwsze znaki numeru na jego koniec.
        $accountNo = substr($value, 2) . $start;
        
        //Potraktuj otrzymany ciąg znaków jak liczbę i wylicz resztę z dzielenia przez 97.
        $mod = bcmod($accountNo, 97);
        
        if ($mod != 1) {
            $isValid = false;
        }
        
        return $isValid;
    }
    
    protected function getCountries()
    {
        return $this->countries;
    }
    
    protected function getLength()
    {
        return $this->length;
    }

    protected function setLength($length): void
    {
        $this->length = $length;
    }
    
    protected function getLetterValues()
    {
        return $this->letterValues;
    }

    protected function setLetterValues($letterValues): void
    {
        $this->letterValues = $letterValues;
    }
    
    protected function getLetterValue($letter)
    {
        $letterValues = $this->getLetterValues();
        
        if (!array_key_exists($letter, $letterValues)) {
            throw new \Exception(sprintf("Brak kodu liczbowego dla %s", $letter));
        }
        
        return $letterValues[$letter];
    }
}
