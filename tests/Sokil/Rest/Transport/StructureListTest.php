<?php

namespace Sokil\Rest\Transport;

class StructureListTest extends \PHPUnit_Framework_TestCase
{
    public function testMixedKey()
    {
        $list = new StructureList(array(
            'a' => array('key' => 'value0'),
            'b' => array('key' => 'value1'),
            'c' => array('key' => 'value2'),
        ));
        
        $structure = $list->current();
        $this->assertEquals('a', $list->key());
        $this->assertInstanceOf('\Sokil\Rest\Transport\Structure', $structure);
        $this->assertEquals('value0', $list->current()->get('key'));
        
        $list->next();
        $structure = $list->current();
        $this->assertEquals('b', $list->key());
        $this->assertInstanceOf('\Sokil\Rest\Transport\Structure', $structure);
        $this->assertEquals('value1', $list->current()->get('key'));
    }
    
    public function testEach()
    {
        $list = new StructureList;
        $list->setFromArray(array(
            array('key' => 'value0'),
            array('key' => 'value1'),
            array('key' => 'value2'),
        ));
        
        $list->each(function($structure, $index) {
            $this->assertInstanceOf('\Sokil\Rest\Transport\Structure', $structure);
            $this->assertEquals('value' . $index, $structure->get('key'));
        });
    }
    
    public function testMap()
    {
        $list = new StructureList;
        $list->setFromArray(array(
            array('key' => 'value0'),
            array('key' => 'value1'),
            array('key' => 'value2'),
        ));
        
        // map list
        $list->map(function($structure, $index) {
            $this->assertInstanceOf('\Sokil\Rest\Transport\Structure', $structure);
            $this->assertEquals('value' . $index, $structure->get('key'));
            
            // update
            $structure->set('key', 'updated' . $index);
            
            return $structure;
        });
        
        // test list
        $list->each(function($structure, $index) {
            $this->assertInstanceOf('\Sokil\Rest\Transport\Structure', $structure);
            $this->assertEquals('updated' . $index, $structure->get('key'));
        });
    }
    
    public function testFilter()
    {
        $list = new StructureList;
        $list->setFromArray(array(
            array('key' => 0),
            array('key' => 1),
            array('key' => 2),
            array('key' => 3),
            array('key' => 4),
        ));
        
        // filter list
        $filteredList = $list->filter(function($structure, $index) {
            $this->assertInstanceOf('\Sokil\Rest\Transport\Structure', $structure);
            $this->assertEquals($index, $structure->get('key'));
            
            // filter
            return $structure->get('key') % 2 == 0;
        });
        
        // test list
        $filteredList->each(function($structure) {
            $this->assertInstanceOf('\Sokil\Rest\Transport\Structure', $structure);
            $this->assertEquals(0, $structure->get('key') % 2);
        });
    }
}