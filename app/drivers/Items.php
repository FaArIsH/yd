<?php

namespace Oishy\Driver;

use \Iterator;

class Items implements Iterator
{
    private $position = 0;
    private $array = [];

    public function __construct(array $items = [])
    {
        $this->position = 0;
        $this->array = $items;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->array[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->array[$this->position]);
    }

    public function length()
    {
        return count($this->array);
    }
}
