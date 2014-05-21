<?php

namespace Sokil\Rest\Server\Auth\Validator;

class RequestSignTest extends \PHPUnit_Framework_Testcase
{
    public function testValidate()
    {
        $signValidator = new \Sokil\Rest\Server\Auth\Validator\RequestSign('key');
        
        $signValidator->setBody('b=b&a=a');
        $this->assertEquals('97af1933a37be03d920954b54eeb47f8c8d2897a', $signValidator->getSign());
        
        $signValidator->setBody('a=a&b=b');
        $this->assertEquals('8bbc6bb4a2986242825b0ed9b1158f4fedf2a2a7', $signValidator->getSign());
        
        $signValidator->setBody(array('b' => 'b', 'a' => 'a'));
        $this->assertEquals('8bbc6bb4a2986242825b0ed9b1158f4fedf2a2a7', $signValidator->getSign());
        
        $signValidator->setBody(array('a' => 'a', 'b' => 'b'));
        $this->assertEquals('8bbc6bb4a2986242825b0ed9b1158f4fedf2a2a7', $signValidator->getSign());
    }
}