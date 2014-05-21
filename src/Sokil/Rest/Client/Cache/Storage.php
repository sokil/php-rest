<?php

namespace Sokil\Rest\Client\Cache;

class Storage extends \Guzzle\Plugin\Cache\DefaultCacheStorage
{
    private $_cacheKeyGenerator;
    
    public function setCacheKeyGenarator($generator)
    {
        if(!is_callable($generator)) {
            throw new \Exception('Generator must be callable');
        }
        
        $this->_cacheKeyGenerator = $generator;
        return$this;
    }
    
    protected function getCacheKey(\Guzzle\Http\Message\RequestInterface $request)
    {
        if(!$this->_cacheKeyGenerator) {
            return parent::getCacheKey($request);
        }
        
        $generator = $this->_cacheKeyGenerator;
        return md5(parent::getCacheKey($request) . $generator($request));
    }
}