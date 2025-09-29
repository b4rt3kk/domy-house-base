<?php

namespace Base\Validator\File;

use Laminas\Validator\AbstractValidator;
use InvalidArgumentException;

class ImageAspectRatio extends AbstractValidator
{
    public const INVALID_IMAGE     = 'invalidImage';
    public const INVALID_RATIO     = 'invalidRatio';
    public const INVALID_OPTION    = 'invalidOption';

    protected $messageTemplates = [
        self::INVALID_IMAGE  => "Nie można odczytać obrazu.",
        self::INVALID_RATIO  => "Obraz musi mieć proporcje %expected% (szer.:wys.), wykryto: %actual%",
        self::INVALID_OPTION => "Opcja 'ratio' ma nieprawidłowy format. Oczekiwany format: 'x:y' (np. '1:1' lub '16:9').",
    ];

    protected $messageVariables = [
        'expected' => 'expectedLabel',
        'actual'   => 'actualLabel',
    ];

    /** @var string np. "1:1" */
    protected $expectedLabel = '1:1';

    /** @var float oczekiwany stosunek szer./wys. (np. 1.0 dla 1:1) */
    protected $expectedFloat = 1.0;

    /** @var float tolerancja względna (np. 0.05 = 5%) */
    protected $tolerance = 0.05;

    /** @var string np. "800:600" (wartość używana w komunikacie) */
    protected $actualLabel = '';

    /**
     * @param array|\Traversable|null $options expects ['ratio' => 'x:y', 'tolerance' => 0.05]
     * @throws InvalidArgumentException when 'ratio' option is in wrong format
     */
    public function __construct($options = null)
    {
        $opts = is_array($options) ? $options : (is_object($options) ? (array) $options : []);

        // parse ratio option: must be string like "x:y"
        if (isset($opts['ratio'])) {
            if (!is_string($opts['ratio'])) {
                throw new InvalidArgumentException("Opcja 'ratio' musi być stringiem w formacie 'x:y'.");
            }
            $this->parseRatioOption($opts['ratio']);
        }

        if (isset($opts['tolerance'])) {
            $t = (float) $opts['tolerance'];
            if ($t < 0) {
                throw new InvalidArgumentException("Opcja 'tolerance' musi być nieujemna.");
            }
            $this->tolerance = $t;
        }

        parent::__construct($options);
    }

    /**
     * Weryfikuje plik obrazu - $value / $file mogą być w różnych formatach zależnie od tego jak Laminas przekazuje dane.
     *
     * @param mixed $value
     * @param mixed|null $file
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        $filePath = $this->resolveFilePath($value, $file);
        if ($filePath === null || !is_readable($filePath)) {
            $this->error(self::INVALID_IMAGE);
            return false;
        }

        $imageSize = @getimagesize($filePath);
        if ($imageSize === false) {
            $this->error(self::INVALID_IMAGE);
            return false;
        }

        $width  = (float) $imageSize[0];
        $height = (float) $imageSize[1];

        if ($height == 0.0) {
            $this->error(self::INVALID_IMAGE);
            return false;
        }

        $actualFloat = $width / $height;
        // ustaw opis np. "800:600"
        $this->actualLabel = sprintf('%d:%d', (int)$width, (int)$height);

        // porównanie względne: |actual - expected| / expected <= tolerance
        if ($this->expectedFloat == 0.0) {
            // ochronne
            $this->error(self::INVALID_OPTION);
            return false;
        }

        $relativeDiff = abs($actualFloat - $this->expectedFloat) / $this->expectedFloat;
        if ($relativeDiff > $this->tolerance) {
            $this->error(self::INVALID_RATIO);
            return false;
        }

        return true;
    }

    /**
     * Parsuje string "x:y" i zapamiętuje expectedFloat oraz expectedLabel.
     *
     * @param string $ratioString
     * @throws InvalidArgumentException
     */
    protected function parseRatioOption(string $ratioString): void
    {
        $ratioString = trim($ratioString);

        // akceptujemy liczby całkowite i dziesiętne, np. "16:9" lub "1.5:1"
        if (!preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*:\s*([0-9]+(?:\.[0-9]+)?)$/', $ratioString, $m)) {
            throw new InvalidArgumentException("Opcja 'ratio' ma nieprawidłowy format. Oczekiwany 'x:y'.");
        }

        $num = (float) $m[1];
        $den = (float) $m[2];

        if ($den == 0.0) {
            throw new InvalidArgumentException("Opcja 'ratio' ma nieprawidłowy mianownik (nie może być 0).");
        }

        $this->expectedFloat = $num / $den;
        // znormalizowana etykieta - usuń nadmiarowe zera, pokaż np. "16:9" albo "3:2"
        $this->expectedLabel = $this->normalizeLabel($num, $den);
    }

    protected function normalizeLabel(float $num, float $den): string
    {
        // Jeżeli są to liczby całkowite - pokaż jako int:int, w przeciwnym razie pokaż jako float:float z max 3 dec.
        $isNumInt = abs($num - round($num)) < 0.00001;
        $isDenInt = abs($den - round($den)) < 0.00001;

        if ($isNumInt && $isDenInt) {
            return sprintf('%d:%d', (int)$num, (int)$den);
        }

        return sprintf('%.3f:%.3f', $num, $den);
    }

    /**
     * Rozwiązuje ścieżkę do pliku z różnych możliwych struktur jakie Laminas może przekazać.
     *
     * @param mixed $value
     * @param mixed|null $file
     * @return string|null ścieżka do tmp_name lub null
     */
    protected function resolveFilePath($value, $file = null): ?string
    {
        // 1) $file może być tablicą ['tmp_name' => '/tmp/phpxyz', ...]
        if (is_array($file) && !empty($file['tmp_name'])) {
            return $file['tmp_name'];
        }

        // 2) $value może być tablicą (często Laminas przekazuje $value jako array z tmp_name)
        if (is_array($value) && !empty($value['tmp_name'])) {
            return $value['tmp_name'];
        }

        // 3) czasem $value jest bezpośrednią ścieżką pliku
        if (is_string($value) && file_exists($value)) {
            return $value;
        }

        return null;
    }

    // getters (używane przez messageVariables)
    public function getExpectedLabel(): string
    {
        return $this->expectedLabel;
    }

    public function getActualLabel(): string
    {
        return $this->actualLabel;
    }
}