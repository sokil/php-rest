<?php

namespace Sokil\Rest\Client;

class CachingTest_ extends \Guzzle\Tests\GuzzleTestCase
{
    public function _testReadFromWikiCache()
    {
        /**
         * Init factory
         */
        $cacheAdapter = new \Sokil\Rest\Client\Cache\MemoryAdapter;
        
        $factory = new Factory('http://www.wikipedia.org/');
        $factory
            ->setRequestClassNamespace('\Sokil\Rest\Client\RequestMock')
            ->setCacheAdapter($cacheAdapter);
        
        /**
         * Execute request
         */
        $request = $factory->createRequest('getRequestMock');
        $response = $request->send();
        $this->assertTrue($response->isCacheable());
        $this->assertEquals('MISS from GuzzleCache', $response->getHeader('X-Cache-Lookup'));
        
        /**
         * Execute cached request
         */
        $request = $factory->createRequest('getRequestMock');
        $response = $request->send();
        $this->assertEquals('HIT from GuzzleCache', $response->getHeader('X-Cache-Lookup'));
    }
    
    public function _testReadFromCache()
    {
        $client = new \Guzzle\Http\Client('http://example.com/');
           
        /**
         * Mock response
         */
        $client->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array(
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type'  => 'application/json',
                'Cache-Control' => 'max-age=3600',
                'Date'          => gmdate('D, d M Y H:i:s', time()) . ' GMT',
                'Expires'       => gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT',
                'Last-Modified' => gmdate('D, d M Y H:i:s', time() - 1000) . ' GMT',
            ), json_encode(array(
                'param' => 'abcdef',
            ))),
            new \Guzzle\Http\Message\Response(200, array(
                'Content-type'  => 'application/json',
                'Cache-Control' => 'max-age=3600',
                'Date'          => gmdate('D, d M Y H:i:s', time()) . ' GMT',
                'Expires'       => gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT',
                'Last-Modified' => gmdate('D, d M Y H:i:s', time() - 1000) . ' GMT',
            ), json_encode(array(
                'param' => 'fedcba',
            ))),
        )));
        
        $client->addSubscriber(new \Guzzle\Plugin\Cache\CachePlugin(array(
            'storage' => new \Guzzle\Plugin\Cache\DefaultCacheStorage(
                new \Sokil\Rest\Client\Cache\MemoryAdapter
            ),
        )));
        
        /**
         * Execute request
         */
        $response = $client->get('/')->send()->json();
        $this->assertEquals('abcdef', $response['param']);
        
        /**
         * Execute cached request
         */
        $response = $client->get('/')->send()->json();
        $this->assertEquals('abcdef', $response['param']);
    }
}