<?php

namespace Base\Services\Image;

class Converter
{
    /**
     * @var \Base\Image
     */
    protected \Base\Image $sourceImage;
    
    /**
     * @return \Base\Image
     */
    public function getSourceImage(): \Base\Image
    {
        return $this->sourceImage;
    }

    /**
     * @param \Base\Image $sourceImage
     * @return void
     */
    public function setSourceImage(\Base\Image $sourceImage): void
    {
        $this->sourceImage = $sourceImage;
    }
    
    /**
     * Pobierz przeskalowany obraz do wskazanego rozmiaru
     * @param integer $targetWidth
     * @param integer $targetHeight
     * @return \Base\Image
     * @throws \Exception
     */
    public function getImageResized($targetWidth, $targetHeight)
    {
        $image = $this->getSourceImage();
        
        if (empty($image)) {
            throw new \Exception("Nie przekazano obiektu obrazu źródłowego");
        }
        
        // właściwe rozszerzenie pliku, znalezione na podstawie jego treści
        $extensionFromMime = $image->getExtensionFromMimeType();
        $width = $image->getWidth();
        $height = $image->getHeight();
        
        $gdImage = $this->getGdSourceImage();
        
        $resizedGdImage = imagescale($gdImage, $targetWidth, $targetHeight);
        
        if (empty($resizedGdImage)) {
            throw new \Exception("Konwersja obrazu nie powiodła się");
        }
        
        // odnalezienie właściwej funkcji do pobrania treści pliku odpowiedniej dla jego treści
        $functionName = 'image' . strtolower($extensionFromMime);
        
        if (!function_exists($functionName)) {
            throw new \Exception("Zapis pliku typu %s nie jest możliwy", $extensionFromMime);
        }
        
        // utworzenie pliku tymczasowego
        $file = tmpfile();
        $fileMetaData = stream_get_meta_data($file);
        $uri = $fileMetaData['uri'];
        
        // pobranie treści pliku
        if (!$functionName($resizedGdImage, $uri)) {
            throw new \Exception(sprintf("Zapis pliku w lokalizacji tymczasowej %s nie powiódł się", $uri));
        }
        
        $body = file_get_contents($uri);
        fclose($file);
        
        if (empty($body)) {
            throw new \Exception(sprintf("Otworzenie treści przetworzonego pliku po konwersji %s nie powiodło się", $uri));
        }
        
        $imageConverted = new \Base\Image();
        $imageConverted->setBody($body);
        
        return $imageConverted;
    }
    
    /**
     * Utwórz obiekt obrazu biblioteki gd
     * @return \GdImage|false
     * @throws \Exception
     */
    protected function getGdSourceImage()
    {
        $image = $this->getSourceImage();
        $extension = $image->getExtension();
        // właściwe rozszerzenie pliku, znalezione na podstawie jego treści
        $extensionFromMime = $image->getExtensionFromMimeType();
        
        // utworzenie pliku tymczasowego
        $file = tmpfile();
        $stream = fwrite($file, $image->getBody());
        
        $fileMetaData = stream_get_meta_data($file);
        $uri = $fileMetaData['uri'];
        
        // odnalezienie właściwej funkcji do pobrania treści pliku odpowiedniej dla jego treści
        $functionName = 'imagecreatefrom' . strtolower($extensionFromMime);
        
        if (!function_exists($functionName)) {
            throw new \Exception("Konwersja pliku typu %s nie jest możliwa", $extensionFromMime);
        }
        
        // utworzenie obiektu
        $gdImage = $functionName($uri);
        
        if (empty($gdImage)) {
            throw new \Exception("Nie udało się utworzyć obiektu pliku do konwersji");
        }
        
        // usunięcie pliku tymczasowego poprzez zamknięcie streama
        fclose($stream);
        
        return $gdImage;
    }
}
