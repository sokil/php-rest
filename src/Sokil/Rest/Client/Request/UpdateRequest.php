<?php

namespace Sokil\Rest\Client\Request;

/**
 * @property \Guzzle\Http\Message\EntityEnclosingRequest $_request Guzzle's request object
 */
class UpdateRequest extends \Sokil\Rest\Client\Request
{
    protected $_requestMethod = 'PUT';
    
    public function setBody($body, $contentType = null)
    {        
        $this->_request->setBody($body, $contentType);
        return $this;
    }
    
    public function getBody()
    {
        return $this->_request->getBody();
    }
}