<?php

namespace Sokil\Rest\Client\RequestMock;

class GetRequestMock extends \Sokil\Rest\Client\Request\ReadRequest
{    
    protected $_url = 'some/resource';
    
    protected $_structureClassName = '\Sokil\Rest\Client\ResponseMock\GetRequestMockResponseStructure';
}

