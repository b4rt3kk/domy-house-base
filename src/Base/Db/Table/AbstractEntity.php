<?php
namespace Base\Db\Table;

abstract class AbstractEntity
{
    protected $data;
    
    protected $headersMapping = [];
    
    protected $rowActions = [];
    
    public function exchangeArray(array $data)
    {
        $this->setData($data);
    }
    
    public function setData($data)
    {
        $this->data = $data;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function getHeadersMapping()
    {
        return $this->headersMapping;
    }

    public function setHeadersMapping($headersMapping)
    {
        $this->headersMapping = $headersMapping;
    }
    
    public function getRowActions()
    {
        return $this->rowActions;
    }

    public function setRowActions($rowActions)
    {
        $this->rowActions = $rowActions;
    }
    
    public function __get($name)
    {
        $data = $this->getData();
        
        return array_key_exists($name, $data) ? $data[$name] : null;
    }
}
