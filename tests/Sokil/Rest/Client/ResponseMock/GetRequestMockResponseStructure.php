<?php

namespace Sokil\Rest\Client\ResponseMock;

class GetRequestMockResponseStructure extends \Sokil\Rest\Transport\Structure
{
    public function getParam1()
    {
        return $this->get('param1');
    }
    
    public function setParam1($value)
    {
        $this->set('param1', $value);
        return $this;
    }
    
    public function getParam2()
    {
        return $this->get('param2');
    }
    
    public function setParam2($value)
    {
        $this->set('param2', $value);
        return $this;
    }
}