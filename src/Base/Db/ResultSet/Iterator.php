<?php

namespace Base\Db\ResultSet;

class Iterator implements \Iterator
{
    protected $position = 0;
    
    protected $data = [];
    
    public function add($item)
    {
        $this->data[] = $item;
    }
    
    public function current() : mixed
    {
        return $this->data[$this->position];
    }

    public function key() : mixed
    {
        return $this->position;
    }

    public function next() : void
    {
        ++$this->position;
    }

    public function rewind() : void
    {
        $this->position = 0;
    }

    public function valid() : bool
    {
        return isset($this->data[$this->position]);
    }
    
    public function count()
    {
        return is_countable($this->data) ? sizeof($this->data) : 0;
    }
}
