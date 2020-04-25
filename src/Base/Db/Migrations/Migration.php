<?php
namespace Base\Db\Migrations;

abstract class Migration
{
    protected $isInitial;
    
    protected $index;
    
    public function getIsInitial()
    {
        return $this->isInitial;
    }

    public function setIsInitial($isInitial)
    {
        $this->isInitial = $isInitial;
    }
    
    public function getDependencies()
    {
        return [];
    }
    
    public function getIndex()
    {
        return $this->index;
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }
    
    /**
     * Pobierz listę zapytań w postaci tablicy
     * @return array
     */
    abstract public function getQueries();
    
    /**
     * Pobierz nazwę autora migracji
     * @return string
     */
    abstract public function getAuthor();
    
    /**
     * Pobierz nazwę migracji
     * @return string
     */
    abstract public function getName();
}
