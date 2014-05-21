<?php

namespace Sokil\Rest\Client;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Request
{
    const ACTION_CREATE = 'PUT';
    const ACTION_READ   = 'GET';
    const ACTION_UPDATE = 'POST';
    const ACTION_DELETE = 'DELETE';
    
    /**
     *
     * @var \Sokil\Rest\Client\Factory
     */
    private $_factory;
    
    /**
     *
     * @var \Guzzle\Http\Message\Request
     */
    private $_request;
    
    protected $_url;
    
    protected $_action;
    
    protected $_responseClassName = '\Sokil\Rest\Client\Response';
    
    private $_rawResponse;
    
    private $_response;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;
    
    public function __construct(Factory $factory, array $urlParameters = null)
    {
        $this->_factory = $factory;
        
        // prepare uri
        if($urlParameters) {
            $url = array($this->_url, $urlParameters);
        } else {
            // check if placeholders exists
            if(strpos($this->_url, '{')) {
                throw new Exception('Url parameters not specified');
            }
            
            $url = $this->_url;
        }
        
        // create request
        $this->_request = $factory
            ->getConnection()
            ->createRequest($this->_action, $url);
        
        // do post-init tasks
        $this->init();
    }
    
    public function __destruct()
    {
        $this->_request = null;
        $this->_response = null;
        $this->_rawResponse = null;
        $this->_logger = null;
    }
    
    public function init() { }
    
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
     * @return \Sokil\Rest\Response
     */
    public function send()
    {
        $this->_rawResponse = $this->_request->send();
        
        // log
        if($this->hasLogger()) {
            $this->getLogger()->debug((string) $this->_request . PHP_EOL . (string) $this->_rawResponse);
        }
        
        // create response
        $this->_response = new $this->_responseClassName($this->_rawResponse);
        
        return $this->_response;
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

