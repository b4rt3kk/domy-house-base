<?php

namespace Base\View\Helper;

use Laminas\View\Helper\HeadLink;
use Laminas\View\Helper\HeadScript;

class AngularAssetManager extends AbstractHelper
{
    const EXTENSION_JS = 'js';
    const EXTENSION_CSS = 'css';

    use \Base\Traits\ServiceManagerTrait;

    /**
     * Adres HTTP z którego zostaną pobrane pliki Angular i dołączone do projektu.
     *
     * Przydatne do użycia w przypadku <strong>developerskiego</strong> uruchomienia aplikacji.
     *
     * @var string
     */
    protected string $http;

    /**
     * Lokalizacja plików Angular w katalogu aplikacji, które zostaną dołączone do projektu.
     *
     * Przydatne do użycia w przypadku <strong>produkcyjnego</strong> uruchomienia aplikacji.
     *
     * @var string
     */
    protected string $local;

    /**
     * Lista plików Angular do dołączenia do aplikacji.
     *
     * W przypadku lokalizacji <strong>zdalnej - HTTP</strong> pliki powinny być wymienione <strong>enumeratywnie, bez użycia wyrażeń regularnych</strong>,
     * ponieważ lokalizacje zdalne nie są skanowane w celu poszukiwania plików.
     *
     * W przypadku lokalizacji <strong>lokalnej</strong> pliki mogą być wymienione <strong>enumeratywnie lub wyrażeniami regularnymi</strong>,
     * ponieważ lokalizacje lokalne są skanowane w celu poszukiwania plików.
     *
     * @var array
     */
    protected array $filesPatterns = [];

    public function getHttp(): string
    {
        return $this->http;
    }

    public function setHttp(string $http): void
    {
        $this->http = !empty($http) ? (rtrim($http, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) : '';
    }

    public function getLocal(): string
    {
        return $this->local;
    }

    public function setLocal(string $local): void
    {
        $this->local = !empty($local) ? (rtrim($local, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) : '';
    }

    public function getFilesPatterns(): array
    {
        return $this->filesPatterns;
    }

    public function setFilesPatterns(array $filesPatterns): void
    {
        $this->filesPatterns = $filesPatterns;
    }

    public function __invoke(array $config = []): void
    {
        // ustawienie konfiguracji
        $this->setConfig($config);
        // dołączenie plików do layoutu
        $this->appendFiles();
    }

    protected function setConfig(array $config): void
    {
        $this->setHttp($config['http'] ?? '');
        $this->setLocal($config['local'] ?? '');
        $this->setFilesPatterns($config['files_patterns'] ?? []);
    }

    protected function appendFiles(): void
    {
        $this->handleLocal();
        $this->handleHttp();
    }

    protected function handleLocal(): void
    {
        $local = $this->getLocal();

        if (empty($local)) {
            // w przypadku braku lokalnych plików, zakończ obsługę
            return;
        }

        $helper = $this->getHeadScriptHelper();
        $helperHeadLink = $this->getHeadLinkHelper();
        $filesPatterns = $this->getFilesPatterns();
        $appendedFiles = [];

        // zebranie wszystkich plików z katalogu lokalnego
        $directoryIterator = new \RecursiveDirectoryIterator($local);
        $localFiles = [];

        foreach ($directoryIterator as $file) {
            /* @var $file \SplFileInfo */
            if (!$file->isDir()) {
                $localFiles[] = $file->getFilename();
            }
        }

        // iteracja wzorców w zdefiniowanej kolejności — gwarantuje właściwą kolejność ładowania skryptów
        foreach ($filesPatterns as $pattern) {
            foreach ($localFiles as $fileName) {
                if (!preg_match('#^' . $pattern . '$#', $fileName)) {
                    continue;
                }

                $scriptPath = rtrim($local, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

                if (in_array($scriptPath, $appendedFiles)) {
                    continue;
                }

                // utworzenie lokalizacji HTTP dla pliku, która uwzględnia że public jest root aplikacji
                $rootFolder = 'public';
                $rootLocation = substr($scriptPath, strpos($scriptPath, $rootFolder) + strlen($rootFolder));

                // pobranie rozszerzenia pliku
                $extension = pathinfo($scriptPath, PATHINFO_EXTENSION);

                switch (strtolower($extension)) {
                    case self::EXTENSION_JS:
                        $helper->appendScript(
                            'import "'.$rootLocation.'";',
                            'module'
                        );
                        break;
                    case self::EXTENSION_CSS:
                        $helperHeadLink->appendStylesheet($rootLocation);
                        break;
                }

                $appendedFiles[] = $scriptPath;
            }
        }
    }

    protected function handleHttp(): void
    {
        $http = $this->getHttp();

        if (empty($http)) {
            return;
        }

        $helper = $this->getHeadScriptHelper();
        $helperHeadLink = $this->getHeadLinkHelper();
        $filesPatterns = $this->getFilesPatterns();
        // lista dołączonych plików
        $appendedFiles = [];

        foreach ($filesPatterns as $script) {
            $scriptPath = $http . ltrim($script, DIRECTORY_SEPARATOR);

            if (in_array($scriptPath, $appendedFiles)) {
                // pominięcie dodanych już plików
                continue;
            }

            // pobranie rozszerzenia pliku
            $extension = pathinfo($scriptPath, PATHINFO_EXTENSION);

            // sprawdzenie rozszerzenia pliku i odpowiednia obsługa
            switch (strtolower($extension)) {
                case self::EXTENSION_JS:
                    $helper->appendScript(
                        'import "'.$scriptPath.'";',
                        'module'
                    );
                    break;
                case self::EXTENSION_CSS:
                    $helperHeadLink->appendStylesheet($scriptPath);
                    break;
            }

            $appendedFiles[] = $scriptPath;
        }
    }

    protected function getHeadScriptHelper(): HeadScript
    {
        return $this->getServiceManager()->get('ViewHelperManager')->get('headScript');
    }

    protected function getHeadLinkHelper(): HeadLink
    {
        return $this->getServiceManager()->get('ViewHelperManager')->get('headLink');
    }

    /**
     * Sprawdź czy plik o podanej nazwie spełnia warunki jednego z wyrażeń regularnych określonych w konfiguracji.
     *
     * @param string $file
     * @return bool
     */
    protected function fileMatchAnyPattern(string $file): bool
    {
        $patterns = $this->getFilesPatterns();

        foreach ($patterns as $pattern) {
            if (preg_match('#^' . $pattern . '$#', $file)) {
                return true;
            }
        }

        return false;
    }
}