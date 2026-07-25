<?php

namespace Base\Migration;

abstract class AbstractMigration
{
    protected $fileName;
    
    protected $name;

    /**
     * Krótki tytuł widoczny w rejestrze migracji i logach wdrożenia.
     *
     * @var string
     */
    protected $title = '';

    /**
     * Opis celu, zakresu i istotnych skutków migracji.
     *
     * @var string
     */
    protected $description = '';
    
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

    public function getTitle()
    {
        return $this->title ?: $this->name;
    }

    public function getDescription()
    {
        return $this->description;
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
    
    public function getRevertQueries()
    {
        return [];
    }
    
    abstract public function getQueries();
}
