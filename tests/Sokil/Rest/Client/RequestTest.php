<?php

namespace Sokil\Rest\Client;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testSetQueryParam()
    {
        $factory = new Factory('http://localhost');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        
        $request = $factory->createRequest('GetRequestMock');
        $request->setQueryParam('param1.subparam1', 'value1');
        $request->setQueryParam('param2.subparam2', 'value2');
        
        $this->assertEquals('param1%5Bsubparam1%5D=value1&param2%5Bsubparam2%5D=value2', $request->getQueryString());
    }
    
    public function testGetQueryParams()
    {
        $factory = new Factory('http://localhost');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        
        $request = $factory->createRequest('GetRequestMock');
        $request->setQueryParam('param1', 'value1');
        $request->setQueryParam('param2', 'value2');
        
        $this->assertEquals(array(
            'param1' => 'value1',
            'param2' => 'value2'
        ), $request->getQueryParams());
    }
    
    public function testGetQueryString()
    {
        $factory = new Factory('http://localhost');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        
        $request = $factory->createRequest('GetRequestMock');
        $request->setQueryParam('param1', 'value1');
        $request->setQueryParam('param2', 'value2');
        
        $this->assertEquals('param1=value1&param2=value2', $request->getQueryString());
    }
    
    public function testParametricUrls()
    {
        $factory = new Factory('http://localhost');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        
        $request = $factory
            ->createRequest('ParametricUrlRequestMock', array(
                'parameter' => 'abc12345',
            ));
        
        $this->assertEquals('http://localhost/resource/abc12345', $request->getUrl());
    }
    
    public function testEventOnSuccessRequest()
    {
        // prepare response
        $response = new \Guzzle\Http\Message\Response(200, array(
            'Content-type'  => 'application/json',
        ));
        
        $response->setBody(json_encode(array(
            'error' => 0,
        )));
        
        // replace response
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin;
        $plugin->addResponse($response);
        
        // configure subscriber
        $factory = new Factory('http://localhost/');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        
        $request = $factory->createRequest('GetRequestMock');
        $request->addSubscriber($plugin);
        
        $callStack = array();
        
        // configure event
        $response = $request
            ->onBeforeSend(function(\Guzzle\Common\Event $e) use(&$callStack) {
                $callStack[] = 'before_send';
            })
            ->onSend(function(\Guzzle\Common\Event $e) use(&$callStack) {
                $callStack[] = 'send';
            })
            ->onCompleteSend(function(\Guzzle\Common\Event $e) use(&$callStack) {
                $callStack[] = 'complete';
            })
            ->onError(function(\Guzzle\Common\Event $e) use(&$callStack) {
                $callStack[] = 'error';
            })
            ->onSuccess(function(\Guzzle\Common\Event $e) use(&$callStack) {
                $callStack[] = 'success';
            })
            ->send();
            
        $this->assertEquals(array(
            'before_send',
            'send',
            'complete',
            'success',
        ), $callStack);
    }
    
    public function testEventOnErrorRequest()
    {
        // prepare response
        $response = new \Guzzle\Http\Message\Response(404);
        $response->setBody(json_encode(array(
            'error' => 0,
        )));
        
        // replace response
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin;
        $plugin->addResponse($response);
        
        // configure subscriber
        $factory = new Factory('http://localhost/');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        
        $request = $factory->createRequest('GetRequestMock');
        $request->addSubscriber($plugin);
        
        $callStack = array();
        
        // configure event
        $request
            ->onBeforeSend(function(\Guzzle\Common\Event $e) use(&$callStack) {
                $callStack[] = 'before_send';
            })
            ->onSend(function(\Guzzle\Common\Event $e) use(&$callStack) {
                $callStack[] = 'send';
            })
            ->onCompleteSend(function(\Guzzle\Common\Event $e) use(&$callStack) {
                $callStack[] = 'complete';
            })
            ->onError(function(\Guzzle\Common\Event $e) use(&$callStack) {
                $callStack[] = 'error';
            })
            ->onSuccess(function(\Guzzle\Common\Event $e) use(&$callStack) {
                $callStack[] = 'success';
            });
            
        try {
            $response = $request->send();
            $this->assertFalse($response);
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {}
            
        $this->assertEquals(array(
            'before_send',
            'send',
            'complete',
            'error',
        ), $callStack);
    }
    
    public function testBuildUrlForRequest()
    {
        // configure subscriber
        $factory = new Factory('http://localhost/basepath');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        
        $request = $factory->createRequest('GetRequestMock');
        
        $this->assertEquals('http://localhost/basepath/some/resource', $request->getUrl());
    }    
}

