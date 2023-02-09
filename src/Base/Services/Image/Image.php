<?php

namespace Base\Services\Image;

class Image
{
    const SOURCE_URL = 1;
    const SOURCE_DISK = 2;
    const SOURCE_FROM_BODY = 3;
    
    const EXTENSION_PREFIX = '.';
    
    /**
     * Lokalizacja pliku
     * @var string
     */
    protected $location;
    
    /**
     * Treść pliku kodowana do base64 i dekodowana przy jej pobieraniu
     * @var string
     */
    protected $body;
    
    /**
     * Źródło pliku
     * @var integer
     */
    protected $source;
    
    /**
     * Nazwa pliku
     * @var string
     */
    protected $name;
    
    /**
     * Rozszerzenie pliku
     * @var string
     */
    protected $extension;
    
    /**
     * Typ pliku
     * @var string
     */
    protected $mimeType;
    
    /**
     * Szerokość obrazu
     * @var integer
     */
    protected $width;
    
    /**
     * Wysokość obrazu
     * @var integer
     */
    protected $height;
    
    protected $metaData;
    
    /**
     * Rozmiar w bajtach
     * @var integer
     */
    protected $size;
    
    // http://www.iana.org/assignments/media-types/media-types.xhtml#image
    protected $extensionsByMimeType = [
        'application/cdf',
        'application/dicom',
        'application/fractals',
        'application/postscript',
        'application/vnd.hp-hpgl',
        'application/vnd.oasis.opendocument.graphics',
        'application/x-cdf',
        'application/x-cmu-raster',
        'application/x-ima',
        'application/x-inventor',
        'application/x-koan',
        'application/x-portable-anymap',
        'application/x-world-x-3dmf',
        'image/bmp',
        'image/c',
        'image/cgm',
        'image/fif',
        'image/gif',
        'image/heic',
        'image/heif',
        'image/jpeg',
        'image/jpm',
        'image/jpx',
        'image/jp2',
        'image/naplps',
        'image/pjpeg',
        'image/png',
        'image/svg',
        'image/svg+xml',
        'image/tiff',
        'image/vnd.adobe.photoshop',
        'image/vnd.djvu',
        'image/vnd.fpx',
        'image/vnd.net-fpx',
        'image/webp',
        'image/x-cmu-raster',
        'image/x-cmx',
        'image/x-coreldraw',
        'image/x-cpi',
        'image/x-emf',
        'image/x-ico',
        'image/x-icon',
        'image/x-jg',
        'image/x-ms-bmp',
        'image/x-niff',
        'image/x-pict',
        'image/x-pcx',
        'image/x-png',
        'image/x-portable-anymap',
        'image/x-portable-bitmap',
        'image/x-portable-greymap',
        'image/x-portable-pixmap',
        'image/x-quicktime',
        'image/x-rgb',
        'image/x-tiff',
        'image/x-unknown',
        'image/x-windows-bmp',
        'image/x-xpmi',
    ];
    
    public function getLocation()
    {
        return $this->location;
    }

    public function getBody()
    {
        return base64_decode($this->body);
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getExtension()
    {
        $extension = $this->extension;
        
        if (empty($extension)) {
            $extension = $this->getExtensionFromMimeType();
        }
        
        return $extension;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getMetaData()
    {
        return $this->metaData;
    }

    public function getSize()
    {
        return $this->size;
    }
    
    /**
     * Pobierz nazwę pliku wraz z jego rozszerzeniem
     * @return string
     */
    public function getNameWithExtension()
    {
        $name = $this->getName();
        $extension = $this->getExtension();
        
        return $name . self::EXTENSION_PREFIX . $extension;
    }

    /**
     * Ustaw lokalizację dla pliku.
     * Obsługiwane lokalizacje: 
     * - adres URL 
     * - ścieżka do pliku 
     * Lokalizacja może zostać ustawiona jedynie w przypadku, gdy wcześniej nie ustanowiono treści pliku.
     * @param string $location
     * @return void
     * @throws \Exception
     */
    public function setLocation($location): void
    {
        // określenie źródła pliku
        $source = $this->autodiscoverSourceForLocation($location);
        
        // pobranie treści pliku ze wskazanej lokalizacji
        $body = $this->getBodyFromLocation($location, $source);
        
        if (!empty($this->body)) {
            throw new \Exception("Dla tego pliku określono już jego treść, której nie można nadpisać");
        }
        
        $fileInfo = $this->getFileInfoFromLocation($location);
        
        // odnaleziona nazwa pliku
        $this->setName($fileInfo['filename']);
        $this->setExtension($fileInfo['extension']);
        
        $this->setSource($source);
        $this->setBody($body);
        
        $this->location = $location;
    }

    /**
     * Ustaw treść pliku. 
     * Treść pliku może zostać ustanowiona jedynie w przypadku gdy nie wskazano lokalizacji pliku.
     * @param string $body
     * @return void
     * @throws \Exception
     */
    public function setBody($body): void
    {
        if (!empty($this->location)) {
            throw new \Exception("Dla tego pliku określono już jego lokalizację i nie można nadpisać jego treści");
        }
        
        $contentType = $this->getMimeTypeFromBody($body);
        
        $this->setMimeType($contentType);
        $this->setSize(strlen($body));
        
        $dimensions = $this->getFileDimensionsFromBody($body);
        $metaData = $this->getMetaDataFromBody($body);
        
        // szerokość obrazu
        $this->setWidth($dimensions[0]);
        // wysokość obrazu
        $this->setHeight($dimensions[1]);
        // meta data obrazu
        $this->setMetaData($metaData);
        
        if (empty($this->name)) {
            // automatyczne wygenerowanie nazwy pliku
            $this->setName(md5('YmdHis' . mt_rand()));
        }
        
        if (empty($this->extension)) {
            // rozszerzenie pliku na podstawie określonego typu z mime
            $this->setExtension($this->getExtensionFromMimeType());
        }
        
        if (empty($this->source)) {
            // jeśli źródło jest puste oznacza  to, że źródłem jest treść pliku
            $this->setSource(self::SOURCE_FROM_BODY);
        }
        
        if (!$this->isFileImage($body)) {
            throw new \Exception("Treść pliku nie jest prawidłowym obrazem");
        }
        
        $this->body = base64_encode($body);
    }

    /**
     * Pobierz treść pliku ze wskazanej lokalizacji dla wskazanego źródła
     * @param string $location Przy pozostawieniu tego pola pustego metoda pobiera wcześniej podaną lokalizację pliku
     * @param integer $source Źródło pliku, w przypadku gdy ten parametr jest pusty metoda próbuje automatycznie określić źródło
     * @return string
     * @throws \Exception
     */
    public function getBodyFromLocation($location = null, $source = null)
    {
        $body = null;
        
        if (empty($location)) {
            $location = $this->getLocation();
            
            if (empty($location)) {
                throw new \Exception("Nie podano lokalizacji pliku");
            }
        }
        
        if (empty($source)) {
            $source = $this->autodiscoverSourceForLocation($location);
        }
        
        switch ($source) {
            case self::SOURCE_URL:
                $body = $this->getBodyFromUrl($location);
                break;
            case self::SOURCE_DISK:
                $body = $this->getBodyFromDisk($location);
                break;
            default:
                throw new \Exception(sprintf("Źródło %s nie jest obsługiwane", $source));
        }
        
        if (empty($body)) {
            throw new \Exception(sprintf("Nie udało się pobrać treści pliku %s", $location));
        }
        
        return $body;
    }
    
    protected function getExtensionsByMimeType()
    {
        return $this->extensionsByMimeType;
    }
    
    protected function setWidth($width): void
    {
        $this->width = $width;
    }

    protected function setHeight($height): void
    {
        $this->height = $height;
    }
    
    protected function setMimeType($mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    protected function setMetaData($metaData): void
    {
        $this->metaData = $metaData;
    }

    protected function setSize($size): void
    {
        $this->size = $size;
    }
    
    protected function setSource($source): void
    {
        $this->source = $source;
    }

    protected function setName($name): void
    {
        $this->name = $name;
    }

    protected function setExtension($extension): void
    {
        $this->extension = $extension;
    }

    /**
     * Określ źródło dla pliku obrazu
     * @param string $location
     * @return integer
     * @throws \Exception
     */
    protected function autodiscoverSourceForLocation($location)
    {
        $source = null;
        
        switch (true) {
            case filter_var($location, FILTER_VALIDATE_URL):
                $source = self::SOURCE_URL;
                break;
            case file_exists($location):
                $source = self::SOURCE_DISK;
                break;
            default:
                throw new \Exception(sprintf("Nie udało się określić typu źródła dla lokalizacji %s", $location));
        }
        
        return $source;
    }
    
    /**
     * Pobierz treść obrazu na podstawie jego lokalizacji na zasobie URL
     * @param string $url
     * @return string
     */
    protected function getBodyFromUrl($url)
    {
        return file_get_contents($url);
    }
    
    /**
     * Pobierz treść obrazu na podstawie jego lokalizacji na dysku
     * @param string $location
     * @return string
     */
    protected function getBodyFromDisk($location)
    {
        return file_get_contents($location);
    }
    
    /**
     * Pobierz typ MIME obrazu na podstawie jego treści
     * @param string $body
     * @return string
     */
    protected function getMimeTypeFromBody($body)
    {
        $file = fopen('php://memory', 'w+b');
        fwrite($file, $body);

        $contentType = mime_content_type($file);
        
        return $contentType;
    }
    
    /**
     * Pobierz rozmiar obrazu (w bajtach) na podstawie jego treści
     * @param string $body
     * @return integer
     */
    protected function getFileSizeFromBody($body)
    {
        return strlen($body);
    }
    
    /**
     * Pobierz wymiary obrazu na podstawie jego treści
     * @param string $body
     * @return array
     */
    protected function getFileDimensionsFromBody($body)
    {
        $info = getimagesizefromstring($body);
        
        return $info;
    }
    
    /**
     * Pobierz meta data z treści pliku
     * @param string $body
     * @return array
     */
    protected function getMetaDataFromBody($body)
    {
        $file = fopen('php://memory', 'w+b');
        fwrite($file, $body);
        
        $metaData = exif_read_data($file);
        
        return $metaData;
    }
    
    /**
     * Pobierz rozszerzenie pliku na podstawie jego MimeType
     * @todo Zrobić mapowanie dla $extensionsByMimeType i przenieść na mapowanie na podstawie $extensionsByMimeType
     * @return string
     */
    protected function getExtensionFromMimeType()
    {
        $extension = null;
        $mimeType = $this->getMimeType();
        
        if (!empty($mimeType)) {
            $chunks = explode('/', $mimeType);
            
            $extension = $chunks[1];
        }
        
        return $extension;
    }
    
    protected function getFileInfoFromLocation($location)
    {
        $fileLocation = $location;
        
        if (strpos($location, '?') !== false) {
            $chunks = explode('?', $location);
            $fileLocation = $chunks[0];
        }
        
        $pathInfo = pathinfo($fileLocation);
        
        return $pathInfo;
    }
    
    protected function isFileImage($body)
    {
        $validator = new \Laminas\Validator\File\IsImage([
            'enableHeaderCheck' => true,
        ]);
        
        // utworzenie pliku tymczasowego
        $file = tmpfile();
        fwrite($file, $body);
        
        $fileMetaData = stream_get_meta_data($file);
        
        // sprawdzenie poprawności
        $isValid = $validator->isValid($fileMetaData['uri']);
        
        fclose($file);
        
        return $isValid;
    }
}
