<?php

namespace Sokil\Rest\Client;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testMethogProxyingToStructure()
    {
        $guzzleResponse = new \Guzzle\Http\Message\Response(200, array(
            'Content-type'  => 'application/json',
        ), json_encode(array(
            'param1'    => 'value1',
            'param2'    => 'value2',
        )));
        
        $response = new Response($guzzleResponse, '\Sokil\Rest\Client\ResponseMock\GetRequestMockResponseStructure');
        
        // check response type
        $this->assertInstanceOf('\Sokil\Rest\Client\Response', $response);
        
        // check structure method
        $this->assertInstanceOf('\Sokil\Rest\Client\ResponseMock\GetRequestMockResponseStructure', $response->getStructure());
        
        // proxy to structure's method which returns value
        $this->assertEquals('value1', $response->getParam1());
        
        // proxy to structure's method which returns $this
        $this->assertInstanceOf('\Sokil\Rest\Client\Response', $response->setParam1('gg'));

    }
    
    public function testContentTypeWithCharset()
    {
        $guzzleResponse = new \Guzzle\Http\Message\Response(200, array(
            'Content-type'  => 'application/json; charset=utf-8',
        ), json_encode(array(
            'param1'    => 'value1',
            'param2'    => 'value2',
        )));
        
        $response = new Response($guzzleResponse, '\Sokil\Rest\Client\ResponseMock\GetRequestMockResponseStructure');
        
        // check response type
        $this->assertInstanceOf('\Sokil\Rest\Client\Response', $response);
        
        // check structure method
        $this->assertInstanceOf('\Sokil\Rest\Client\ResponseMock\GetRequestMockResponseStructure', $response->getStructure());
        
        // proxy to structure's method which returns value
        $this->assertEquals('value1', $response->getParam1());
        
        // proxy to structure's method which returns $this
        $this->assertInstanceOf('\Sokil\Rest\Client\Response', $response->setParam1('gg'));
        
        
    }
}

