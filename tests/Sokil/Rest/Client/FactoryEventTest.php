<?php

namespace Sokil\Rest\Client;

class FactoryEventTest extends \PHPUnit_Framework_TestCase
{
    
    public function testOnBeforeSend()
    {
        // replace response
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin;
        $plugin->addResponse(new \Guzzle\Http\Message\Response(
            200, 
            array(
                'Content-type'  => 'application/json',
            ),
            json_encode(array(
                'error' => 0,
            ))
        ));
        
        $factory = new Factory('http://localhost/');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        $factory->addSubscriber($plugin);
        
        $status = new \stdclass;
        $status->ok = 0;
        $factory->onBeforeSend(function() use($status) {
            $status->ok = 1;
        });
        
        // send
        $response = $factory->createRequest('GetRequestMock')->send();
        $this->assertEquals(0, $response->get('error'));
        
        // check if event occured
        $this->assertEquals(1, $status->ok);
    }
    
    public function testOnSend()
    {
        // replace response
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin;
        $plugin->addResponse(new \Guzzle\Http\Message\Response(
            200, 
            array(
                'Content-type'  => 'application/json',
            ),
            json_encode(array(
                'error' => 0,
            ))
        ));
        
        $factory = new Factory('http://localhost/');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        $factory->addSubscriber($plugin);
        
        $status = new \stdclass;
        $status->ok = 0;
        $factory->onSend(function() use($status) {
            $status->ok = 1;
        });
        
        // send
        $response = $factory->createRequest('GetRequestMock')->send();
        $this->assertEquals(0, $response->get('error'));
        
        // check if event occured
        $this->assertEquals(1, $status->ok);
    }
    
    public function testOnCompleteSend()
    {
        // replace response
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin;
        $plugin->addResponse(new \Guzzle\Http\Message\Response(
            200, 
            array(
                'Content-type'  => 'application/json',
            ),
            json_encode(array(
                'error' => 0,
            ))
        ));
        
        $factory = new Factory('http://localhost/');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        $factory->addSubscriber($plugin);
        
        $status = new \stdclass;
        $status->ok = 0;
        $factory->onCompleteSend(function() use($status) {
            $status->ok = 1;
        });
        
        // send
        $response = $factory->createRequest('GetRequestMock')->send();
        $this->assertEquals(0, $response->get('error'));
        
        // check if event occured
        $this->assertEquals(1, $status->ok);
    }
    
    public function testOnSuccess()
    {
        // replace response
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin;
        $plugin->addResponse(new \Guzzle\Http\Message\Response(
            200, 
            array(
                'Content-type'  => 'application/json',
            ),
            json_encode(array(
                'error' => 0,
            ))
        ));
        
        $factory = new Factory('http://localhost/');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        $factory->addSubscriber($plugin);
        
        $status = new \stdclass;
        $status->ok = 0;
        $factory->onSuccess(function() use($status) {
            $status->ok = 1;
        });
        
        // send
        $response = $factory->createRequest('GetRequestMock')->send();
        $this->assertEquals(0, $response->get('error'));
        
        // check if event occured
        $this->assertEquals(1, $status->ok);
    }
    
    public function testOnParseResponse()
    {
        // replace response
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin;
        $plugin->addResponse(new \Guzzle\Http\Message\Response(
            200, 
            array(
                'Content-type'  => 'application/json',
            ),
            json_encode(array(
                'error' => 234,
            ))
        ));
        
        $factory = new Factory('http://localhost/');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        $factory->addSubscriber($plugin);
        
        $status = new \stdclass;
        $status->error = 0;
        $factory->onParseResponse(function($event) use($status) {
            $this->assertInstanceof('\Sokil\Rest\Client\Response', $event['response']);
            $status->error = $event['response']->get('error');
        });
        
        // send
        $factory->createRequest('GetRequestMock')->send();
        
        // check if event occured
        $this->assertEquals(234, $status->error);
    }
    
    public function testOnError()
    {
        // replace response
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin;
        $plugin->addResponse(new \Guzzle\Http\Message\Response(
            404, 
            array(
                'Content-type'  => 'application/json',
            ),
            json_encode(array(
                'error' => 0,
            ))
        ));
        
        $factory = new Factory('http://localhost/');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        $factory->addSubscriber($plugin);
        
        $status = new \stdclass;
        $status->status = 0;
        $factory->onError(function($event) use($status) {
            $status->status = $event['response']->getStatusCode();
        });
        
        // send
        $response = $factory->createRequest('GetRequestMock')->send();
        $this->assertFalse($response);
        
        // check if event occured
        $this->assertEquals(404, $status->status);
    }
}

