<?php

namespace Sokil\Rest\Client\Request;

/**
 * @property \Guzzle\Http\Message\EntityEnclosingRequest $_request Guzzle's request object
 */
class ReadRequest extends \Sokil\Rest\Client\Request
{
    protected $_requestMethod = 'GET';
    
    public function setBody($body, $contentType)
    {        
        $this->_request->setBody($body, $contentType);
        return $this;
    }
}