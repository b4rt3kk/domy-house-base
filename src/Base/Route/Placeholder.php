<?php
namespace Base\Route;

class Placeholder
{
    protected $rawName;
    
    protected $name;
    
    protected $index = 1;
    
    protected $valuesData;
    
    protected $serviceManager;
    
    public function getRawName()
    {
        return $this->rawName;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getValuesData()
    {
        return $this->valuesData;
    }

    /**
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setRawName($rawName): void
    {
        $indexSeparator = DynamicRoute::SEPARATOR_HASH;
        
        if (strpos($rawName, $indexSeparator) !== false) {
            $matches = [];
            preg_match("#\\{$indexSeparator}([0-9]+)#", $rawName, $matches);
            
            if (!empty($matches[1])) {
                $this->setIndex($matches[1]);
                $this->setName(str_replace($indexSeparator . $matches[1], '', $rawName));
            }
        } else {
            $this->setName($rawName);
        }
        
        $this->rawName = $rawName;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function setIndex($index): void
    {
        $this->index = $index;
    }

    public function setValuesData($valuesData): void
    {
        $this->valuesData = $valuesData;
    }

    public function setServiceManager($serviceManager): void
    {
        $this->serviceManager = $serviceManager;
    }
}

