<?php

namespace Sokil\Rest\Transport;

interface StructureInterface extends \Serializable
{        
    public function setFromArray(array $data);
    
    public function set($selector, $value);
    
    public function get($selector, $default = null);
    
    public function getObject($selector, $className);
    
    /**
     * 
     * @param string $selector
     * @param string $className
     * @return \Sokil\Rest\StructureList
     * @throws \Exception
     */
    public function getObjectList($selector, $className);
    
    public function __toString();
    
    public function toJson();
     
    public function toArray();
    
    public function remove($selector);
}

