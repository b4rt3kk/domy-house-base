<?php

namespace Base\Db\ResultSet;

class Iterator implements \Iterator
{
    protected $position = 0;
    
    protected $data;
    
    public function add($item)
    {
        $this->data[] = $item;
    }
    
    public function current()
    {
        return $this->data[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return isset($this->data[$this->position]);
    }
    
    public function count()
    {
        return sizeof($this->data);
    }
}
