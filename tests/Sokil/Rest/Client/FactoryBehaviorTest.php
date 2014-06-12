<?php

namespace Sokil\Rest\Client;

class MyBehavior extends \Sokil\Rest\Client\Behavior
{
    public function get42()
    {
        return 42;
    }
    
    public function getRequestUrl()
    {
        return $this->getOwner()->getUrl();
    }
}

class FactoryBehaviorTest extends \PHPUnit_Framework_TestCase
{
    public function testAttachBehavior()
    {        
        $factory = new Factory('http://localhost/');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        
        $factory->attachBehavior('my', new \Sokil\Rest\Client\MyBehavior);
        
        // exec behavior
        $requert = $factory->createRequest('GetRequestMock');
        $this->assertEquals(42, $requert->get42());
    }
    
    public function testBehaviorOwner()
    {
        $factory = new Factory('http://localhost/');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        
        $factory->attachBehavior('my', new \Sokil\Rest\Client\MyBehavior);
        
        // exec behavior
        $requert = $factory->createRequest('GetRequestMock');
        $this->assertEquals('http://localhost/some/resource', $requert->getRequestUrl());
    }
}

