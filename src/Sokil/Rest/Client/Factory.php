<?php

namespace Sokil\Rest\Client;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Factory
{
    protected $_requestClassNamespace;
    
    private $_host;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;
    
    protected $_signAlgo = 'sha1';
    
    private $_signKey;
    
    protected $_signQueryParamName = 'sign';
    
    private $_signAdditionalParams = array();
    
    protected $_headers = array();
    
    protected $_curlOptions = array();
    
    private $_connection;
    
    private $_behaviors = array();
    
    /**
     * 
     * @param string $host
     * @param string $requestClassNamespace if not specified in constructor
     *  then must be defined directly in child class
     */
    public function __construct($host = null)
    {
        if($host) {
            $this->_host = $host;
        }
        
        // add behaviors
        $this->attachBehaviors($this->behaviors());
        
        // init
        $this->init();
    }
    
    protected function init() {}
    
    /**
     * Get Guzzle RESTful client
     *
     * @return \Guzzle\Http\Client
     */
    public function getConnection()
    {
        if(!$this->_connection) {
            $this->_connection = new \Guzzle\Http\Client($this->getHost());
        }

        return $this->_connection;
    }
    
    public function setHost($host)
    {
        $this->_host = $host;
        return $this;
    }
    
    public function getHost()
    {
        return $this->_host;
    }
    
    public function setRequestClassNamespace($namespace)
    {
        $this->_requestClassNamespace = rtrim($namespace, '/');
        return $this;
    }
    
    public function setUserAgent($userAgent)
    {
        $this->getConnection()->setUserAgent($userAgent);
        return $this;
    }
    
    /**
     * 
     * @param string $name
     * @param array $urlParameters
     * @return \Sokil\Rest\Request
     * @throws \Exception
     */
    public function createRequest($name, array $urlParameters = null)
    {
        $requestClassName = $this->_requestClassNamespace . '\\' . ucfirst($name);
        $request = new $requestClassName($this, $urlParameters);
        if(!($request instanceof Request)) {
            throw new \Exception('Wrong request ' . $name . ' specified'); 
        }
        
        // add headers
        if($this->_headers) {
            $request->setHeaders($this->_headers);
        }
        
        // curl options
        if($this->_curlOptions) {
            $request->setCurlOptions($this->_curlOptions);
        }
        
        // add logger
        if($this->hasLogger()) {
            $request->setLogger($this->getLogger());
        }
        
        // attach behaviors
        if($this->_behaviors) {
            $request->attachBehaviors($this->_behaviors);
        }
        
        return $request;
    }
    
    public function createSignedRequest($name, array $urlParameters = null)
    {
        $signer = new \Sokil\Guzzle\Plugin\RequestSign(array(
            'algo'              => $this->_signAlgo,
            'key'               => $this->_signKey,
            'queryParamName'    => $this->_signQueryParamName,
            'additionalParams'  => $this->_signAdditionalParams,
        ));
        
        $request = $this->createRequest($name, $urlParameters);
        $request->addSubscriber($signer);
        
        return $request;
    }
    
    public function getSignAlgo()
    {
        return $this->_signAlgo;
    }
    
    public function setSignAlgo($algo)
    {
        $this->_signAlgo = $algo;
        return $this;
    }
    
    public function getSignQueryParamName()
    {
        return $this->_signQueryParamName;
    }
    
    public function setSignQueryParamName($name)
    {
        $this->_signQueryParamName = $name;
        return $this;
    }
    
    public function setSignKey($key)
    {
        $this->_signKey = $key;
        return $this;
    }
    
    public function addSignAdditionalParam($key, $value)
    {
        $this->_signAdditionalParams[$key] = $value;
        return $this;
    }
    
    public function setCurlOptions(array $options)
    {
        $this->_curlOptions = $options;
        return $this;
    }
    
    public function setCurlOption($name, $value)
    {
        $this->_curlOptions[$name] = $value;
        return $this;
    }
    
    public function setHeader($name, $value)
    {
        $this->_headers[$name] = $value;
        return $this;
    }
    
    public function getHeader($name)
    {
        return isset($this->_headers[$name]) ? $this->_headers[$name] : null;
    }
    
    public function setHeaders(array $headers)
    {
        $this->_headers = $headers;
        return $this;
    }
    
    public function addHeaders(array $headers)
    {
        $this->_headers = array_merge($this->_headers, $headers);
        return $this;
    }
    
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->getConnection()->addSubscriber($subscriber);
        return $this;
    }
    
    public function onBeforeSend($callable)
    {
        $this->getConnection()->getEventDispatcher()->addListener('request.before_send', $callable);
        return $this;
    }
    
    public function onSend($callable)
    {
        $this->getConnection()->getEventDispatcher()->addListener('request.sent', $callable);
        return $this;
    }
    
    public function onCompleteSend($callable)
    {
        $this->getConnection()->getEventDispatcher()->addListener('request.complete', $callable);
        return $this;
    }
    
    public function onSuccess($callable)
    {
        $this->getConnection()->getEventDispatcher()->addListener('request.success', $callable);
        return $this;
    }
    
    public function onParseResponse($callable)
    {
        $this->getConnection()->getEventDispatcher()->addListener('successParseResponse', $callable);
        return $this;
    }
    
    public function onError($callable)
    {
        $this->getConnection()->getEventDispatcher()->addListener('request.error', $callable);
        return $this;
    }
    
    protected function behaviors()
    {
        return array();
    }
    
    public function attachBehaviors(array $behaviors)
    {
        foreach($behaviors as $name => $behavior) {
            
            if(!($behavior instanceof Behavior)) {
                if(empty($behavior['class'])) {
                    throw new Exception('Behavior class not specified');
                }

                $className = $behavior['class'];
                unset($behavior['class']);

                $behavior = new $className($behavior);
            }
            
            $this->attachBehavior($name, $behavior);
        }
        
        return $this;
    }
    
    public function attachBehavior($name, Behavior $behavior)
    {
        $this->_behaviors[$name] = $behavior;
        
        return $this;
    }
    
    public function clearBehaviors()
    {
        $this->_behaviors = array();
        return $this;
    }
    
    /**
     * If request method and url insufficient as key and must be augment 
     * @return Callable
     */
    protected function _getCacheKeyGenerator() { }
    
    public function setCacheAdapter(\Guzzle\Cache\CacheAdapterInterface $adapter, $namespace = null)
    {
        $storage =  new \Sokil\Rest\Client\Cache\Storage($adapter, $namespace);
        
        $generator = $this->_getCacheKeyGenerator();
        if(is_callable($generator)) {
            $storage->setCacheKeyGenarator($generator);
        }
        
        $this
            ->getConnection()
            ->addSubscriber(new \Guzzle\Plugin\Cache\CachePlugin(array(
                'storage' => $storage,
            )));
        
        return $this;
    }
    
    /**
     *
     * @param string $lang lang identifier compatible with Accept-Language header
     */
    public function setLanguage($lang)
    {
        $this->setHeader('Accept-Language', $lang);
        return $this;
    }

    public function getLanguage()
    {
        return $this->getHeader('Accept-Language');
    }
    
    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->_logger = $logger;
        return $this;
    }
    
    /**
     * 
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }
    
    public function hasLogger()
    {
        return (bool) $this->_logger;
    }
    
    /**
     * Disable logging
     * 
     * @return \Sokil\Rest\Request
     */
    public function removeLogger()
    {
        $this->_logger = null;
        return $this;
    }
}

