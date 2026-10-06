<?php

namespace Base\Validator;

use Laminas\Validator\AbstractValidator;

class Url extends AbstractValidator
{
    public const INVALID_URL = 'invalidUrl';

    protected $messageTemplates = [
        self::INVALID_URL => "Nieprawidłowy adres URL",
    ];

    /**
     * @var bool Czy wymagać schematu (http/https)?
     */
    protected $requireScheme = false;

    public function __construct($options = null)
    {
        parent::__construct($options);

        if (isset($options['requireScheme'])) {
            $this->requireScheme = (bool)$options['requireScheme'];
        }
    }

    public function isValid($value)
    {
        $this->setValue($value);

        if (!is_string($value)) {
            $this->error(self::INVALID_URL);
            return false;
        }
        $valueToCheck = trim($value);
        // Only navigable HTTP(S) links; never allow script/data/FTP schemes or credentials.
        if (preg_match('/[\x00-\x20\x7f<>"\\\\]/', $valueToCheck)
            || (preg_match('/^[a-z][a-z0-9+.-]*:/i', $valueToCheck)
                && !preg_match('#^https?://#i', $valueToCheck))) {
            $this->error(self::INVALID_URL);
            return false;
        }

        // Usuwamy prefiks "www." tylko dla walidacji,
        // ale nie modyfikujemy oryginalnego $value
        $strippedValue = preg_replace('#^www\.#i', '', $valueToCheck);

        // Jeśli nie wymaga schematu, dodaj tymczasowo "http://"
        if (!$this->requireScheme && !preg_match('#^https?://#i', $strippedValue)) {
            $strippedValue = 'http://' . $strippedValue;
        }

        // Walidacja URL przez filter_var
        if (!filter_var($strippedValue, FILTER_VALIDATE_URL)) {
            $this->error(self::INVALID_URL);
            return false;
        }

        // Dodatkowa walidacja hosta
        $host = parse_url($strippedValue, PHP_URL_HOST);
        if (!$host || !in_array(strtolower((string) parse_url($strippedValue, PHP_URL_SCHEME)), ['http', 'https'], true)
            || parse_url($strippedValue, PHP_URL_USER) !== null || parse_url($strippedValue, PHP_URL_PASS) !== null) {
            $this->error(self::INVALID_URL);
            return false;
        }

        // Host musi mieć przynajmniej jedną kropkę (np. example.com)
        if (substr_count($host, '.') < 1) {
            $this->error(self::INVALID_URL);
            return false;
        }

        // Dodatkowa kontrola długości TLD (ostatni fragment po kropce)
        $parts = explode('.', $host);
        $tld = end($parts);
        if (strlen($tld) < 2 || strlen($tld) > 24) {
            $this->error(self::INVALID_URL);
            return false;
        }

        return true;
    }
}