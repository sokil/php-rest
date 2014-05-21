<?php

namespace Sokil\Rest\Client\Cache;

class StorageTest extends \Guzzle\Tests\GuzzleTestCase
{
    public function testSetCacheKeyGenarator()
    {
        $request = new \Guzzle\Http\Message\Request('GET', '/resource');
        
        $storage = new StorageMock(new MemoryAdapter);
        
        // without generator
        $this->assertEquals('686a3bbc0a2faf497451662472676f9d', $storage->getCacheKey($request));
        
        // with generator
        $storage->setCacheKeyGenarator(function() {
            return '==prefix==';
        });
        
        $this->assertEquals('15f8db8d1623477f41023683c5dde3a8', $storage->getCacheKey($request));
    }
}