<?php

namespace Sokil\Rest\Client;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateFactory()
    {
        $factory = new Factory('http://localhost/');
        $factory->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock');
        
        $request = $factory->createRequest('getRequestMock');
        $this->assertInstanceOf('\Sokil\Rest\Client\Request', $request);
    }
    
    public function testCreateSignedRequest()
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
        
        // configure subscriber
        $signKey = 'APP_KEY';
        
        $factory = new Factory('http://localhost/');
        $factory
            ->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock')
            ->setSignKey($signKey)
            ->addSignAdditionalParam('app_id', 'APP_ID');
        
        $request = $factory->createSignedRequest('GetRequestMock');
        $request->addSubscriber($plugin);
        
        $response = $request
            ->setQueryParam('param', 'value')
            ->send();
        
        // test signature
        $body = $request->getQueryParams();
        unset($body['sign']);
        ksort($body);

        // calculate and compare sign with passed
        $this->assertEquals(
            hash_hmac(
                $factory->getSignAlgo(),
                http_build_query($body),
                $signKey
            ), 
            $request->getQueryParam('sign')
        );
    }
}

