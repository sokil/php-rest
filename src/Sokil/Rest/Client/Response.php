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
    
    public function __construct(\Guzzle\Http\Message\Response $response)
    {
        $this->_response = $response;
        
        // if json returned - parse and fill structure
        if($this->_response->getContentType() == 'application/json') {
            $this->_structure->setFromArray($this->_response->json());
        }
    }
    
    public function __destruct()
    {
        $this->_response = null;
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
    
    public function isCacheable()
    {
        return $this->_response->canCache();
    }
    
    public function getBody()
    {
        return $this->_response->getBody();
    }
}

