<?php

namespace Sokil\Rest\Client;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class Request
{
    /**
     *
     * @var request method defined in realisation of this abstract class
     */
    protected $_requestMethod;
    
    /**
     *
     * @var \Sokil\Rest\Client\Factory
     */
    protected $_factory;
    
    /**
     *
     * @var \Guzzle\Http\Message\Request
     */
    protected $_request;
    
    protected $_url;
    
    protected $_structureClassName = '\Sokil\Rest\Transport\Structure';
    
    private $_rawResponse;
    
    private $_response;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;
    
    private $_behaviors = array();
    
    public function __construct(Factory $factory, array $urlParameters = null)
    {
        $this->_factory = $factory;
        
        $url = ltrim($this->_url, '/');
        
        // prepare uri
        if($urlParameters) {
            $url = array($url, $urlParameters);
        } else {
            // check if placeholders exists
            if(strpos($url, '{')) {
                throw new Exception('Url parameters not specified');
            }
        }
        
        // create request
        $this->_request = $factory
            ->getConnection()
            ->createRequest($this->_requestMethod, $url);
        
        // add behaviors
        $this->attachBehaviors($this->behaviors());
        
        // do post-init tasks
        $this->init();
    }
    
    public function __call($name, $arguments) {
        
        // behaviors
        foreach($this->_behaviors as $behavior) {
            if(!method_exists($behavior, $name)) {
                continue;
            }
            
            return call_user_func_array(array($behavior, $name), $arguments);
        }
        
        throw new Exception('Document has no method "' . $name . '"');
    }
    
    public function __destruct()
    {
        $this->_request = null;
        $this->_response = null;
        $this->_rawResponse = null;
        $this->_logger = null;
    }
    
    public function init() { }
    
    /**
     * 
     * @return \Sokil\Rest\Client\Factory
     */
    public function getFactory()
    {
        return $this->_factory;
    }
    
    public function getUrl()
    {
        return $this->_request->getUrl();
    }
    
    public function setQueryParam($name, $value)
    {
        // modify
        $arraySelector = explode('.', $name);
        $chunksNum = count($arraySelector);
        
        // optimize one-level selector search
        if(1 == $chunksNum) {
            $this->_request->getQuery()->set($name, $value);
            return $this;
        }
        
        // selector is nested
        $queryParams = $this->getQueryParams();
        $section = &$queryParams;
        
        for($i = 0; $i < $chunksNum - 1; $i++) {

            $field = $arraySelector[$i];

            if(!isset($section[$field])) {
                $section[$field] = array();
            }
            elseif(!is_array($section[$field])) {
                throw new Exception('Assigning subdocument to scalar value');
            }

            $section = &$section[$field];
        }
        
        // update local field
        $section[$arraySelector[$chunksNum - 1]] = $value;
        
        // add to query
        $this->_request->getQuery()->set($arraySelector[0], $queryParams[$arraySelector[0]]);
        
        return $this;
    }
    
    public function addQueryParams(array $params)
    {
        $this->_request->getQuery()->overwriteWith($params);
        return $this;
    }
    
    public function setQueryParams(array $params)
    {
        $this->_request->getQuery()->replace($params);
        return $this;
    }
    
    public function getQueryParam($key)
    {
        return $this->_request->getQuery()->get($key);
    }
    
    public function removeQueryParam($key)
    {
        return $this->_request->getQuery()->remove($key);
    }
    
    public function getQueryParams()
    {
        return $this->_request->getQuery()->toArray();
    }
    
    public function getQueryString()
    {
        return (string) $this->_request->getQuery();
    }
    
    public function setHeader($name, $value)
    {
        $this->_request->setHeader($name, $value);
        return $this;
    }
    
    public function getHeader($name)
    {
        return $this->_request->getHeader($name);
    }
    
    public function setHeaders(array $headers)
    {
        $this->_request->setHeaders($headers);
        return $this;
    }
    
    public function addHeaders(array $headers)
    {
        $this->_request->addHeaders($headers);
        return $this;
    }
    
    public function getHeaders()
    {
        return $this->_request->getHeaders()->toArray();
    }
    
    public function setCurlOptions(array $options)
    {
        $this->_request->getCurlOptions()->replace($options);
        return $this;
    }
    
    public function setCurlOption($key, $value)
    {
        $this->_request->getCurlOptions()->set($key, $value);
        return $this;
    }
    
    /**
     * @return \Sokil\Rest\Client\Response
     */
    public function send()
    {
        try {
            $this->_rawResponse = $this->_request->send();
            
            // create response
            $this->_response = new Response(
                $this->_rawResponse, 
                $this->_structureClassName
            );
            
            // trigger event
            $this->_request->getEventDispatcher()->dispatch('successParseResponse', new \Guzzle\Common\Event(array(
                'request' => $this,
                'response' => $this->_response,
            )));
            
            // log
            if($this->hasLogger()) {
                $this->getLogger()->debug((string) $this->_request . PHP_EOL . (string) $this->_rawResponse);
            }
            
            return $this->_response;
            
        } catch (\Exception $e) {
            if($this->hasLogger()) {
                $this->getLogger()->debug((string) $e);
            }
            
            return false;
        }

    }
    
    public function getResponse()
    {
        if(!$this->_response) {
            $this->send();
        }
        
        return $this->_response;
    }
    
    /**
     * 
     * @return \Guzzle\Http\Message\Response
     */
    public function getRawResponse()
    {
        return $this->_rawResponse;
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
    
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->_request->addSubscriber($subscriber);
        return $this;
    }
    
    public function onBeforeSend($callable)
    {
        $this->_request->getEventDispatcher()->addListener('request.before_send', $callable);
        return $this;
    }
    
    public function onSend($callable)
    {
        $this->_request->getEventDispatcher()->addListener('request.sent', $callable);
        return $this;
    }
    
    public function onCompleteSend($callable)
    {
        $this->_request->getEventDispatcher()->addListener('request.complete', $callable);
        return $this;
    }
    
    public function onSuccess($callable)
    {
        $this->_request->getEventDispatcher()->addListener('request.success', $callable);
        return $this;
    }
    
    public function onParseResponse($callable)
    {
        $this->_request->getEventDispatcher()->addListener('successParseResponse', $callable);
        return $this;
    }
    
    public function onError($callable)
    {
        $this->_request->getEventDispatcher()->addListener('request.error', $callable);
        return $this;
    }
    
    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->_logger = $logger;
        return $this;
    }
    
    public function toJson()
    {
        return json_encode($this->getQueryParams());
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
    
    public function __toString()
    {
        return (string) $this->_request;
    }
}

