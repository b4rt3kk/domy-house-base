<?php
namespace Base\Db\Table;

abstract class AbstractEntity
{
    protected $data;
    
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
    
    public function __get($name)
    {
        $data = $this->getData();
        
        return array_key_exists($name, $data) ? $data[$name] : null;
    }
}
