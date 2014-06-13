<?php

namespace Sokil\Rest\Client;

class Response
{    

    /**
     *
     * @var \Guzzle\Http\Message\Response
     */
    private $_response;
    
    /**
     *
     * @var \Sokil\Rest\Transport\Structure
     */
    private $_structure;
    
    public function __construct(\Guzzle\Http\Message\Response $response, $type)
    {
        $this->_response = $response;
        
        // if json returned - parse and fill structure
        if($this->_response->getContentType() == 'application/json') {
            $this->_structure = new $type($this->_response->json());
        } else {
            throw new \Exception('Structure parser for content type "' . $this->_response->getContentType() . '" not implemented');
        }
    }
    
    public function __destruct()
    {
        $this->_response = null;
        $this->_structure = null;
    }
    
    public function __call($name, $arguments)
    {
        if(!method_exists($this->_structure, $name)) {
            throw new \Exception('Wrong method "' . $name . '" specified');
        }
        
        $result = call_user_func_array(array($this->_structure, $name), $arguments);
        if($result === $this->_structure) {
            return $this;
        }
        
        return $result;
    }
    
    public function __get($name)
    {
        return $this->_structure->get($name);
    }
    
    public function __set($name, $value)
    {
        $this->_structure->set($name, $value);
    }
    
    /**
     * 
     * @return \Sokil\Rest\Transport\Structure
     */
    public function getStructure()
    {
        return $this->_structure;
    }
    
    public function getHttpCode()
    {
        return $this->_response->getStatusCode();
    }
    
    public function getHeaders()
    {
        return $this->_response->getHeaders()->toArray();
    }
    
    public function getHeader($name)
    {
        return $this->_response->getHeader($name);
    }
    
    public function __toString()
    {
        return (string) $this->_response;
    }
    
    public function toArray()
    {
        return $this->_structure->toArray();
    }
    
    public function isCacheable()
    {
        return $this->_response->canCache();
    }
    
    public function getBody()
    {
        return $this->_response->getBody();
    }
}

