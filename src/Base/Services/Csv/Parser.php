<?php

namespace Base\Services\Csv;

class Parser
{
    const DEFAULT_SEPARATOR = ';';
    const DEFAULT_ENCLOSURE = '"';
    const DEFAULT_ESCAPE = '\\';

    protected $separator = self::DEFAULT_SEPARATOR;
    
    protected $enclosure = self::DEFAULT_ENCLOSURE;
    
    protected $escapeCharacter = self::DEFAULT_ESCAPE;
    
    protected $columnsMapping = [];
    
    protected $skipHeaders = true;
    
    protected $filePath;
    
    protected $fileHeaders = [];
    
    public function getSeparator()
    {
        return $this->separator;
    }

    public function getEnclosure()
    {
        return $this->enclosure;
    }

    public function getColumnsMapping()
    {
        return $this->columnsMapping;
    }

    public function getSkipHeaders()
    {
        return $this->skipHeaders;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }

    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;
    }

    public function setColumnsMapping($columnsMapping)
    {
        $this->columnsMapping = $columnsMapping;
    }

    public function setSkipHeaders($skipHeaders)
    {
        $this->skipHeaders = $skipHeaders;
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }
    
    public function getEscapeCharacter()
    {
        return $this->escapeCharacter;
    }

    public function setEscapeCharacter($escapeCharacter)
    {
        $this->escapeCharacter = $escapeCharacter;
    }
    
    public function getFileHeaders()
    {
        return $this->fileHeaders;
    }

    public function setFileHeaders($fileHeaders)
    {
        $this->fileHeaders = $fileHeaders;
    }
    
    /**
     * Pobierz dane z pliku csv w postaci tablicy
     * @return array
     * @throws \Exception
     */
    public function getDataAsArray()
    {
        $filePath = $this->getFilePath();
        
        if (!is_file($filePath)) {
            throw new \Exception(sprintf("Plik %s nie istnieje", $filePath));
        }
        
        $stream = fopen($filePath, 'r');
        
        if (empty($stream)) {
            throw new \Exception(sprintf("Nie udało się otworzyć pliku %s", $filePath));
        }
        
        $separator = $this->getSeparator();
        $enclosure = $this->getEnclosure();
        $escape = $this->getEscapeCharacter();
        $mapping = $this->getColumnsMapping();
        
        $data = [];
        $index = 0;
        
        while (($row = fgetcsv($stream, 0, $separator, $enclosure, $escape)) !== false) {
            $index++;
            $rowData = [];
            
            if ($index === 1) {
                $this->setFileHeaders($row);
                
                if ($this->getSkipHeaders()) {
                    continue;
                }
            }
            
            foreach ($row as $key => $value) {
                if (isset($mapping[$key])) {
                    $rowData[$mapping[$key]] = $value;
                }
                
                $rowData[$key] = $value;
            }
            
            $data[] = $rowData;
        }
        
        fclose($stream);
        
        return $data;
    }
}
