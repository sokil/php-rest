<?php

namespace Sokil\Rest\Client\RequestMock;

class GetRequestMock extends \Sokil\Rest\Client\Request
{
    protected $_action = self::ACTION_READ;
    
    protected $_url = 'some/resource';
}

