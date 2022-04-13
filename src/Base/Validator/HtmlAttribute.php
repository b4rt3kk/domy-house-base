<?php

namespace Base\Validator;

class HtmlAttribute extends \Laminas\Validator\AbstractValidator
{
    public const INVALID_CHARACTER = 'attrbiuteInvalidCharacter';
    
    protected $messageTemplates = [
        self::INVALID_CHARACTER  => "Atrybut zawiera niedozwolone znaki: %characters%",
    ];
    
    protected $invalidCharacters = [
        '"',
    ];
    
    /** @var string[] */
    protected $messageVariables = [
        'characters' => 'characters',
    ];
    
    /**
     * Znaki, które nie przeszły walidacji
     * @var string
     */
    protected $characters;
    
    public function __construct($options = null)
    {
        if (is_string($options)) {
            $this->setInvalidCharacters($options);
        } else if (isset($options['invalidCharacters'])) {
            $this->setInvalidCharacters($options['invalidCharacters']);
        }
        
        parent::__construct($options);
    }
    
    /**
     * Pobierz tablicę niedozwolonych znaków
     * @return array
     */
    public function getInvalidCharacters()
    {
        return $this->invalidCharacters;
    }

    /**
     * Ustal listę niedozwolonych znaków - może to być string lub tablica
     * @param string|array $invalidCharacters
     */
    public function setInvalidCharacters($invalidCharacters)
    {
        if (is_string($invalidCharacters)) {
            $invalidCharacters = str_split($invalidCharacters);
        }
        
        $this->invalidCharacters = $invalidCharacters;
    }
    
    public function getCharacters()
    {
        return $this->characters;
    }

    public function setCharacters(string $characters)
    {
        $this->characters = $characters;
    }
    
    public function isValid($value)
    {
        $this->setValue($value);
        $isValid = true;
        $characters = null;
        
        $invalidCharacters = $this->getInvalidCharacters();
        
        foreach ($invalidCharacters as $character) {
            if (strpos($value, $character) !== false) {
                $characters .= $character;
            }
        }
        
        if (!empty($characters)) {
            $this->setCharacters($characters);
            $this->error(self::INVALID_CHARACTER);
            $isValid = false;
        }
        
        return $isValid;
    }
}
