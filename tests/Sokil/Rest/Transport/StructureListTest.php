<?php

namespace Sokil\Rest\Transport;

class StructureMock extends \Sokil\Rest\Transport\Structure
{
    
}

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
        
        $that = $this; 
        $list->each(function($structure, $index) use($that) {
            $that->assertInstanceOf('\Sokil\Rest\Transport\Structure', $structure);
            $that->assertEquals('value' . $index, $structure->get('key'));
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
        $that = $this;
        $list->map(function($structure, $index) use($that) {
            $that->assertInstanceOf('\Sokil\Rest\Transport\Structure', $structure);
            $that->assertEquals('value' . $index, $structure->get('key'));
            
            // update
            $structure->set('key', 'updated' . $index);
            
            return $structure;
        });
        
        // test list
        $list->each(function($structure, $index) use($that) {
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
        $that = $this;
        $filteredList = $list->filter(function($structure, $index) use($that) {
            $that->assertInstanceOf('\Sokil\Rest\Transport\Structure', $structure);
            $that->assertEquals($index, $structure->get('key'));
            
            // filter
            return $structure->get('key') % 2 == 0;
        });
        
        // test list
        $filteredList->each(function($structure) use($that) {
            $that->assertInstanceOf('\Sokil\Rest\Transport\Structure', $structure);
            $that->assertEquals(0, $structure->get('key') % 2);
        });
    }
    
    public function testRewindAfterFilter()
    {
        $list = new StructureList(array(
            array('key' => 0),
            array('key' => 1),
            array('key' => 2),
            array('key' => 3),
            array('key' => 4),
        ));
        
        // filter list
        $list->filter(function() {
            return false;
        });
        
        // test list
        $this->assertEquals(0, $list->current()->get('key'));
    }
    
    public function testStructureListIterationType()
    {
        $list = new StructureList(
            array(
                array('key' => 0),
                array('key' => 1),
                array('key' => 2),
                array('key' => 3),
                array('key' => 4),
            ),
            '\Sokil\Rest\Transport\StructureMock'
        );
        
        // check list types
        foreach($list as $structure) {
            $this->assertInstanceOf('\Sokil\Rest\Transport\StructureMock', $structure);
        }
    }
    
    public function testFilteredListStructureType()
    {
        $list = new StructureList(
            array(
                array('key' => 0),
                array('key' => 1),
                array('key' => 2),
                array('key' => 3),
                array('key' => 4),
            ),
            '\Sokil\Rest\Transport\StructureMock'
        );
        
        // filter list
        $filteredList = $list->filter(function() {
            return true;
        });
        
        // check list types
        foreach($filteredList as $structure) {
            $this->assertInstanceOf('\Sokil\Rest\Transport\StructureMock', $structure);
        }
    }
    
    public function testArrayAccessOffsetSet()
    {
        $list = new StructureList();
        
        $list['hello'] = array('param' => 'hello_value');
        
        $this->assertEquals('hello', $list->key());
        $this->assertEquals('hello_value', $list->current()->get('param'));
    }
    
    public function testArrayAccessOffsetGet()
    {
        $list = new StructureList(array(
            'hello' => array('param' => 'hello_value'),
        ));
        
        $this->assertEquals('hello_value', $list['hello']->get('param'));
    }
    
    public function testArrayAccessOffsetExists()
    {
        $list = new StructureList(array(
            'hello' => array('param' => 'hello_value'),
        ));
        
        $this->assertTrue(isset($list['hello']));
        $this->assertFalse(isset($list['UNEXISTED_KEY']));
    }
    
    public function testArrayAccessOffsetUnset()
    {
        $list = new StructureList(array(
            'hello' => array('param' => 'hello_value'),
        ));
        
        unset($list['hello']);
        
        $this->assertEquals(0, count($list));
    }
    
    
        
       
}