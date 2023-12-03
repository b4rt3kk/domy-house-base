<?php

namespace Base\Services\Csv;

class Generator extends \Base\Logic\AbstractLogic
{
    const DEFAULT_SEPARATOR = ';';
    const DEFAULT_ENCLOSURE = '"';
    const DEFAULT_ESCAPE = '\\';
    const DEFAULT_EXTENSION = '.csv';

    protected $separator = self::DEFAULT_SEPARATOR;
    
    protected $enclosure = self::DEFAULT_ENCLOSURE;
    
    protected $escapeCharacter = self::DEFAULT_ESCAPE;
    
    protected $fileExtension = self::DEFAULT_EXTENSION;
    
    protected array $data = [];
    
    protected array $headers = [];
    
    protected array $columns = [];
    
    protected bool $showHeaders = true;
    
    protected $fileName;
    
    protected $modelName;
    
    protected $select;
    
    public function getSeparator()
    {
        return $this->separator;
    }

    public function getEnclosure()
    {
        return $this->enclosure;
    }

    public function getEscapeCharacter()
    {
        return $this->escapeCharacter;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getShowHeaders(): bool
    {
        return $this->showHeaders;
    }

    public function setSeparator($separator): void
    {
        $this->separator = $separator;
    }

    public function setEnclosure($enclosure): void
    {
        $this->enclosure = $enclosure;
    }

    public function setEscapeCharacter($escapeCharacter): void
    {
        $this->escapeCharacter = $escapeCharacter;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function setShowHeaders(bool $showHeaders): void
    {
        $this->showHeaders = $showHeaders;
    }
    
    public function getModelName()
    {
        return $this->modelName;
    }

    public function setModelName($modelName): void
    {
        $this->modelName = $modelName;
    }
    
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }
    
    public function getSelect()
    {
        return $this->select;
    }

    public function setSelect($select): void
    {
        $this->select = $select;
    }
    
    public function getFileName()
    {
        return $this->fileName;
    }

    public function setFileName($fileName): void
    {
        $this->fileName = $fileName;
    }
    
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    public function setFileExtension($fileExtension): void
    {
        $this->fileExtension = $fileExtension;
    }
    
    public function getBody()
    {
        $data = $this->getData();
        
        if (empty($data)) {
            $data = $this->getDataFromModel();
        }
        
        $headers = $this->getHeaders();
        
        $stream = fopen("php://temp", 'w');
        
        fputcsv($stream, $headers, $this->getSeparator(), $this->getEnclosure(), $this->getEscapeCharacter());
        
        foreach ($data as $row) {
            $csvRow = [];
            
            foreach (array_keys($headers) as $header) {
                $csvRow[] = $row[$header];
            }
            
            fputcsv($stream, $csvRow, $this->getSeparator(), $this->getEnclosure(), $this->getEscapeCharacter());
        }
        
        // przewinięcie na początek, żeby pobrać treść
        rewind($stream);
        
        $content = stream_get_contents($stream);
        fclose($stream);
        
        return $content;
    }
    
    public function outputFile()
    {
        $fileName = $this->getFileName();
        
        if (empty($fileName)) {
            $fileName = $this->generateFileName();
        }
        
        $body = $this->getBody();
        
        $this->outputFileHeaders([
            'filename' => $fileName,
            'size' => strlen($body),
        ]);
        
        echo $body;
        exit;
    }
    
    protected function outputFileHeaders($options = [])
    {
        header('Content-Type: text/csv');
        header(sprintf('Content-Disposition: attachment; filename="%s"', $options['filename']));
        
        if (!empty($options['size'])) {
            header(sprintf("Content-Length: %s", $options['size']));
        }
    }
    
    protected function generateFileName()
    {
        $extension = $this->getFileExtension();
        
        $name = date('Ymd-His_') . uniqid() . $extension;
        
        return $name;
    }
    
    protected function getDataFromModel()
    {
        $model = $this->getModel();
        $select = $this->getSelect();
        $columns = $this->getColumns();
        
        $data = $model->fetchAll($select);
        $prototype = $data->getArrayObjectPrototype();
        /* @var $prototype \Base\Db\Table\AbstractEntity */
        
        $headersMapping = $prototype->getHeadersMapping();
        $headersFromModel = [];
        
        // ustawienie nagłówków
        foreach ($headersMapping as $headerKey => $headerMapping) {
            $headersFromModel[$headerKey] = $headerMapping['title'];
        }
        
        $this->setHeaders($headersFromModel);
        
        if (empty($columns)) {
            // wszystkie zmapowane  kolumny
            $columns = array_keys($headersMapping);
        }
        
        return $data->toArray();
    }
    
    /**
     * Pobierz klasę modelu dla przechowywania migracji
     * @return \Base\Db\Table\AbstractModel
     * @throws \Exception
     */
    protected function getModel()
    {
        $modelName = $this->getModelName();
        
        if (empty($modelName)) {
            throw new \Exception("Nazwa modelu nie może być pusta");
        }
        
        $model = $this->getServiceManager()->get($modelName);
        
        if (!$model instanceof \Base\Db\Table\AbstractModel) {
            throw new \Exception(sprintf("Klasa modelu musi dziedziczyć po %s", \Base\Db\Table\AbstractModel::class));
        }
        
        return $model;
    }
}
