<?php
namespace Base\Db\Table;

abstract class AbstractEntity
{
    protected $data;
    
    protected $headersMapping = [];
    
    protected $rowActions = [];
    
    protected $dictionaries = [];
    
    protected $actionsPartial = 'base/table_actions';
    
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
    
    public function getDictionaries()
    {
        return $this->dictionaries;
    }

    public function setDictionaries($dictionaries)
    {
        $this->dictionaries = $dictionaries;
    }
    
    public function getActionsPartial()
    {
        return $this->actionsPartial;
    }

    public function setActionsPartial($actionsPartial)
    {
        $this->actionsPartial = $actionsPartial;
    }

    public function __get($name)
    {
        $data = $this->getData();
        
        return array_key_exists($name, $data) ? $data[$name] : null;
    }
    
    public function __isset($name)
    {
        $data = $this->getData();
        
        return array_key_exists($name, $data);
    }
    
    public function toArray()
    {
        return $this->getData();
    }
    
    /**
     * Wyczyść zbędne dane przy konwersji na Rowset
     */
    public function clearRedundantData()
    {
        $this->setHeadersMapping([]);
        $this->setRowActions([]);
        $this->setDictionaries([]);
        $this->setActionsPartial(null);
    }
}
