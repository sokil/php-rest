<?php

namespace Sokil\Rest\Transport;

class Structure implements \Serializable
{
    private $_data;
    
    public function __construct(array $data = null)
    {
        if($data) {
            $this->_data = $data;
        }
        
        $this->init();
    }
    
    public function init() {}
    
    public function setFromArray(array $data)
    {
        $this->_data = $data;
        return $this;
    }
    
    public function set($selector, $value)
    {
        // modify
        $arraySelector = explode('.', $selector);
        $chunksNum = count($arraySelector);
        
        // optimize one-level selector search
        if(1 == $chunksNum) {
            $this->_data[$selector] = $value;
            return $this;
        }
        
        // selector is nested
        $section = &$this->_data;

        for($i = 0; $i < $chunksNum - 1; $i++) {

            $field = $arraySelector[$i];

            if(!isset($section[$field])) {
                $section[$field] = array();
            }
            elseif(!is_array($section[$field])) {
                throw new Exception('Assigning array to scalar value');
            }

            $section = &$section[$field];
        }
        
        $section[$arraySelector[$chunksNum - 1]] = $value;
        
        return $this;
    }
    
    public function toArray()
    {
        return $this->_data;
    }
    
    public function __get($name)
    {
        return isset($this->_data[$name]) ? $this->_data[$name] : null;
    }
    
    public function get($selector)
    {
        if(false === strpos($selector, '.')) {
            return  isset($this->_data[$selector]) ? $this->_data[$selector] : null;
        }

        $value = $this->_data;
        foreach(explode('.', $selector) as $field)
        {
            if(!isset($value[$field])) {
                return null;
            }

            $value = $value[$field];
        }

        return $value;
    }
    
    public function getObject($selector, $className)
    {
        $data = $this->get($selector);
        if(!$data) {
            return null;
        }
        
        // get classname from callable
        if(is_callable($className)) {
            $className = $className($data);
        }
        
        // prepare structure
        $structure =  new $className();
        if(!($structure instanceof Structure)) {
            throw new Exception('Wring structure class specified');
        }
        
        return $structure->setFromArray($data);
    }
    
    /**
     * 
     * @param string $selector
     * @param string $className
     * @return \Sokil\Rest\StructureList
     * @throws \Exception
     */
    public function getObjectList($selector, $className)
    {        
        $list = new $className($this->get($selector));
        
        if(!($list instanceof StructureList)) {
            throw new \Exception('Must be iterator');
        }
        
        return $list;
    }
    
    public function serialize()
    {
        return json_encode($this->toArray());
    }
    
    public function unserialize($serialized)
    {
        $this->_data = json_decode($serialized, true);
        
        return $this;
    }
    
    public function __toString()
    {
        return $this->serialize();
    }
}

