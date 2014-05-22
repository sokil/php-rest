<?php

namespace Sokil\Rest\Transport;

class StructureList implements \SeekableIterator, \Countable
{
    private $_list = array();
    
    private $_index = 0;
    
    public function __construct(array $list = null)
    {
        if($list) {
            $this->_list = $list;
        }
    }
    
    protected function _getStructureClassName(array $data)
    {
        return '\Sokil\Rest\Transport\Structure';
    }
    
    public function setFromArray(array $list)
    {
        $this->_list = $list;
        return $this;
    }
    
    /**
     * 
     * @return \Sokil\Rest\StructureList
     */
    public function current()
    {
        $data = $this->_list[$this->_index];
        $className = $this->_getStructureClassName($data);
        
        $structure = new $className($data);
        
        return $structure;
    }
    
    public function next()
    {
        $this->_index++;
    }
    
    public function valid()
    {
        return isset($this->_list[$this->_index]);
    }
    
    public function key()
    {
        return $this->_index;
    }
    
    public function rewind()
    {
        $this->_index = 0;
    }
    
    public function seek($index)
    {
        if (!isset($this->_list[$index]))
        {
            throw new \OutOfBoundsException("Invalid index ($index)");
        }

        $this->_index = $index;
    }
    
    public function count()
    {
        return count($this->_list);
    }
    
    public function set($index, $value)
    {
        if ($value instanceof Structure) {
            $value = $value->toArray();
        }
        
        $this->_list[(int) $index] = $value;
        
        return $this;
    }
    
    public function push($value)
    {
        if ($value instanceof Structure) {
            $value = $value->toArray();
        }
        
        
        $this->_list[] = $value;
        return $this;
    }
    
    public function pop()
    {
        $lastIndex = $this->count() - 1;
        $lastValue = $this->_list[$lastIndex];
        
        $this->remove($lastIndex);
        
        return $lastValue;
    }
    
    public function remove($index)
    {
        array_splice($this->_list, $index, 1);

        return $this;
    }
    
    public function each($callback) 
    {        
        foreach($this as $structure) {
            call_user_func($callback, $structure, $this->_index);
        }
        
        return $this;
    }
    
    public function map($callback)
    {
        foreach($this as $structure) {
            $this->set(
                $this->_index,
                call_user_func($callback, $structure, $this->_index)
            );
        }

        return $this;
    }
    
    public function filter($callback)
    {
        $list = new self;
        foreach($this as $structure) {
            if(call_user_func($callback, $structure, $this->_index)) {
                $list->push($structure);
            }
        }
        
        return $list;
    }
}


