<?php

namespace Sokil\Rest\Transport;

class StructureTest extends \PHPUnit_Framework_TestCase
{
    public function testRemove()
    {
        $structure = new Structure(array(
            'param1'    => array(
                'subparam1' => 'value11',
                'subparam2' => 'value12',
            ),
            'param2'    => array(
                'subparam1' => 'value21',
                'subparam2' => 'value22',
            ),
        ));
        
        $structure->remove('param2.subparam2');
        
        $this->assertEquals(array(
            'param1'    => array(
                'subparam1' => 'value11',
                'subparam2' => 'value12',
            ),
            'param2'    => array(
                'subparam1' => 'value21',
            ),
        ), $structure->toArray());
    }
    
    public function testGetObjectList()
    {
        $structure = new Structure(array(
            'param1'    => array(
                array(
                    'param1' => 'value1',
                    'param2' => 'value2',
                ),
                array(
                    'param1' => 'value3',
                    'param2' => 'value4',
                )
            ),
        ));
        
        $this->assertEquals('value2' , $structure->getObjectList('param1')->current()->param2);
    }
}