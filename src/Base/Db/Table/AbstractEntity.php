<?php
namespace Base\Db\Table;

abstract class AbstractEntity
{
    protected $data;
    
    protected $headersMapping = [];
    
    protected $rowActions = [];

    protected $allowMassActions = false;
    protected $massActions = [];
    protected $massEditableFields = [];

    public function getAllowMassActions(): bool { return $this->allowMassActions; }
    public function getMassActions(): array { return $this->allowMassActions ? $this->massActions : []; }
    public function getMassEditableFields(): array { return $this->allowMassActions ? $this->massEditableFields : []; }
    
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

    /** Only explicitly declared paginator headers can become selectable columns. */
    public function getPaginatorColumns(array $headers): array
    {
        $columns = [];
        foreach ($headers as $header) {
            $config = $this->headersMapping[$header] ?? [];
            if (($config['selectable'] ?? true) === false) {
                continue;
            }
            $columns[$header] = [
                'label' => $config['title'] ?? $header,
                'defaultVisible' => (bool) ($config['defaultVisible'] ?? true),
            ];
        }
        return $columns;
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
        $this->allowMassActions = false;
        $this->massActions = [];
        $this->massEditableFields = [];
    }
}
