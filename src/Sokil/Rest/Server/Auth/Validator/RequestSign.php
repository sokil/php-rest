<?php

namespace Sokil\Rest\Server\Auth\Validator;

use Sokil\Rest\Server\Auth\Validator\Exception\CredentialsNotSpecified;
use Sokil\Rest\Server\Auth\Validator\Exception\WrongCredentials;

class RequestSign extends \Sokil\Rest\Server\Auth\Validator
{
    protected $_algo = 'sha1';
    
    private $_key;
    
    protected $_signQueryParamName = 'sign';
    
    protected $_body;
    
    public function __construct($key = null)
    {
        if($key) {
            $this->setKey($key);
        }
    }
    
    public function setAlgo($algo)
    {
        $this->_signAlgo = $algo;
        return $this;
    }
    
    public function setSignQueryParamName($name)
    {
        $this->_signQueryParamName = $name;
        return $this;
    }
    
    public function getSignQueryParamName()
    {
        return $this->_signQueryParamName;
    }
    
    public function setKey($key)
    {
        $this->_key = $key;
        return $this;
    }
    
    public function getBody()
    {
        if($this->_body) {
            return $this->_body;
        }
        
        $this->_body = $_GET;
        unset($this->_body[$this->_signQueryParamName]);
        ksort($this->_body);
        $this->_body = http_build_query($this->_body);
        
        if('POST' === $_SERVER['REQUEST_METHOD']) {
            $postBody = file_get_contents('php://input');
            if($postBody) {
                $this->_body .= $postBody;
            }
        }
        
        return $this->_body;
    }
    
    public function setBody($body)
    {
        if(is_array($body)) {
            ksort($body);
            $body = http_build_query($body);
        }
        
        $this->_body = $body;
        
        return $this;
    }
    
    public function validate()
    {
        if(empty($_GET[$this->_signQueryParamName])) {
            throw new CredentialsNotSpecified('Sign not specified');
        }

        if($_GET[$this->_signQueryParamName] !== $this->getSign()) {
            throw new WrongCredentials('Sign is invalid');
        }
        
        return $this;
    }

    public function getSign()
    {
        return hash_hmac($this->_algo, $this->getBody(), $this->_key);
    }
}