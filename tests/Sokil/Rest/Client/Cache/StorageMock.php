<?php

namespace Sokil\Rest\Client\Cache;

class StorageMock extends \Sokil\Rest\Client\Cache\Storage
{
    public function getCacheKey(\Guzzle\Http\Message\RequestInterface $request)
    {
        return parent::getCacheKey($request);
    }
}