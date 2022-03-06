<?php

namespace Base\Db\ResultSet;

class ResultSet extends \Laminas\Db\ResultSet\ResultSet
{
    public function setDataSource($dataSource)
    {
        $this->dataSource = $dataSource;
    }
    
    public function rewind()
    {
        $this->dataSource->rewind();
    }

    public function current()
    {
        return $this->dataSource->current();
    }

    public function key()
    {
        return $this->dataSource->key();
    }

    public function next()
    {
        $this->dataSource->next();
    }
}
