<?php

namespace Base\Migration;

abstract class AbstractMigration
{
    protected $fileName;
    
    protected $name;
    
    protected $index;
    
    protected $isExecuted = false;
    
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getFileName()
    {
        return $this->fileName;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getIsExecuted()
    {
        return $this->isExecuted;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }

    public function setIsExecuted($isExecuted)
    {
        $this->isExecuted = !empty($isExecuted);
    }
    
    abstract public function getQueries();
}
