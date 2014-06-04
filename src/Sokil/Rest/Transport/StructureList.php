<?php

namespace Sokil\Rest\Transport;

class StructureList implements \SeekableIterator, \Countable
{
    private $_list = array();
    
    protected $_structureClassName = '\Sokil\Rest\Transport\Structure';
    
    public function __construct(array $list = null, $structureClassName = null)
    {
        if($list) {
            $this->_list = $list;
        }
        
        if($structureClassName) {
            $this->_structureClassName = $structureClassName;
        }
    }
    
    private function _getStructureClassName(array $data)
    {
        if(is_callable($this->_structureClassName)) {
            $classNameGenerator = $this->_structureClassName;
            return $classNameGenerator($data);
        } else {
            return $this->_structureClassName;
        }
    }
    
    public function setFromArray(array $list)
    {
        $this->_list = $list;
        return $this;
    }
    
    /**
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->_list;
    }
    
    /**
     * 
     * @return \Sokil\Rest\StructureList
     */
    public function current()
    {
        $data = current($this->_list);
        if(false === $data) {
            return null;
        }
        
        $className = $this->_getStructureClassName($data);
        return new $className($data);
    }
    
    public function next()
    {
        next($this->_list);
    }
    
    public function valid()
    {
        return null !== $this->key();
    }
    
    public function key()
    {
        return key($this->_list);
    }
    
    public function rewind()
    {
        reset($this->_list);
    }
    
    public function seek($index)
    {
        if (!isset($this->_list[$index])) {
            throw new \OutOfBoundsException("Invalid index ($index)");
        }

        $this->rewind();
        
        while($index !== $this->key()) {
            $this->next();
        }
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
        foreach($this as $index => $structure) {
            call_user_func($callback, $structure, $index);
        }
        
        return $this;
    }
    
    public function map($callback)
    {
        foreach($this as $index => $structure) {
            $this->set(
                $index,
                call_user_func($callback, $structure, $index)
            );
        }

        return $this;
    }
    
    public function filter($callback)
    {
        $list = new self;
        foreach($this as $index => $structure) {
            if(call_user_func($callback, $structure, $index)) {
                $list->push($structure);
            }
        }
        
        return $list;
    }
}


