<?php

namespace Sokil\Rest\Client;

class Get42Behavior extends \Sokil\Rest\Client\Behavior
{
    public function get42()
    {
        return 42;
    }
}

class FactoryBehaviorTest extends \PHPUnit_Framework_TestCase
{
    public function testAttachBehavior()
    {        
        $factory = new Factory('http://localhost/');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        
        $factory->attachBehavior('get42', new \Sokil\Rest\Client\Get42Behavior);
        
        // exec behavior
        $requert = $factory->createRequest('GetRequestMock');
        $this->assertEquals(42, $requert->get42());
    }
}

