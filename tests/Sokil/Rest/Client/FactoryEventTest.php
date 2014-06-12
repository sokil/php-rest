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
        $status->ok = 0;
        $factory->onError(function() use($status) {
            $status->ok = 1;
        });
        
        // send
        try {
            $factory->createRequest('GetRequestMock')->send();
        } catch (\Exception $e) {}
        
        // check if event occured
        $this->assertEquals(1, $status->ok);
    }
}
