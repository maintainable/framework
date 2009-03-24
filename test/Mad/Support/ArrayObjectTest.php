<?php
/**
 * @category   Mad
 * @package    Support
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config/environment.php';
}

/**
 * @group      support
 * @category   Mad
 * @package    Support
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Support_ArrayObjectTest extends Mad_Test_Unit
{
    public function testIsAnInstanceofArrayObject()
    {
        $o = new Mad_Support_ArrayObject();
        $this->assertType('ArrayObject', $o);
    }
    
    // offsetGet()
    
    public function testOffsetGetReturnsValueAtOffset()
    {
        $o = new Mad_Support_ArrayObject(array('foo' => 'bar'));
        $this->assertEquals('bar', $o->offsetGet('foo'));
    }
    
    public function testOffsetGetReturnsNullWhenOffsetDoesNotExist()
    {
        $o = new Mad_Support_ArrayObject();
        $this->assertNull($o->offsetGet('foo'));
    }
    
    // get()
    
    public function testGetReturnsValueAtOffset()
    {
        $o = new Mad_Support_ArrayObject(array('foo' => 'bar'));
        $this->assertEquals('bar', $o->get('foo'));
    }

    public function testGetReturnsNullByDefaultWhenOffsetDoesNotExist()
    {
        $o = new Mad_Support_ArrayObject();
        $this->assertNull($o->get('foo'));
    }
    
    public function testGetReturnsDefaultSpecifiedWhenOffsetDoesNotExist()
    {
        $o = new Mad_Support_ArrayObject();
        $this->assertEquals('bar', $o->get('foo', 'bar'));
    }
    
    public function testGetReturnsDefaultSpecifiedWhenValueAtOffsetIsNull()
    {
        $o = new Mad_Support_ArrayObject(array('foo' => null));
        $this->assertEquals('bar', $o->get('foo', 'bar'));
    }
    
    // setDefault()
    
    public function testSetDefaultReturnsValueAtOffset()
    {
        $o = new Mad_Support_ArrayObject(array('foo' => 'bar'));
        $this->assertEquals('bar', $o->setDefault('foo'));        
    }
    
    public function testSetDefaultReturnsAndSetsNullWhenOffsetDoesNotExist()
    {
        $o = new Mad_Support_ArrayObject();
        $this->assertNull($o->setDefault('foo'));
        $this->assertTrue($o->offsetExists('foo'));
        $this->assertNull($o->offsetGet('foo'));
    }
    
    public function testSetDefaultReturnsAndSetsDefaultSpecifiedWhenOffsetDoesNotExist()
    {
        $o = new Mad_Support_ArrayObject();
        $this->assertEquals('bar', $o->setDefault('foo', 'bar'));
        $this->assertTrue($o->offsetExists('foo'));
        $this->assertEquals('bar', $o->offsetGet('foo'));
    }
    
    public function testSetDefaultReturnsAndSetsDefaultSpecifiedValueAtOffsetIsNull()
    {
        $o = new Mad_Support_ArrayObject(array('foo' => null));
        $this->assertEquals('bar', $o->setDefault('foo', 'bar'));
        $this->assertTrue($o->offsetExists('foo'));
        $this->assertEquals('bar', $o->offsetGet('foo'));
    }
    
    // pop()
    
    public function testPopReturnsValueAtOffsetAndUnsetsIt()
    {
        $o = new Mad_Support_ArrayObject(array('foo' => 'bar'));
        $this->assertEquals('bar', $o->pop('foo'));
        $this->assertFalse($o->offsetExists('foo'));
    }
    
    public function testPopReturnsNullByDefaultWhenOffsetDoesNotExist()
    {
        $o = new Mad_Support_ArrayObject();
        $this->assertNull($o->pop('foo'));
    }
    
    public function testPopReturnsDefaultSpecifiedWhenOffsetDoesNotExist()
    {
        $o = new Mad_Support_ArrayObject();
        $this->assertEquals('bar', $o->pop('foo', 'bar'));
    }
    
    public function testPopReturnsDefaultSpecifiedWhenValueAtOffsetIsNull()
    {
        $o = new Mad_Support_ArrayObject(array('foo' => null));
        $this->assertEquals('bar', $o->pop('foo', 'bar'));
    }
    
    // update()
    
    public function testUpdateDoesNotThrowWhenArgumentIsAnArray()
    {
        $o = new Mad_Support_ArrayObject();
        $o->update(array());
    }
    
    public function testUpdateDoesNotThrowWhenArgumentIsTraversable()
    {
        $o = new Mad_Support_ArrayObject();
        $o->update(new ArrayObject());
    }
    
    public function testUpdateMergesNewValuesFromArayInArgument()
    {
        $o = new Mad_Support_ArrayObject();
        $o->update(array('foo' => 'bar'));
        $this->assertEquals('bar', $o->offsetGet('foo'));
    }
    
    public function testUpdateMergesAndOverwritesExistingOffsets()
    {
        $o = new Mad_Support_ArrayObject(array('foo' => 'bar'));
        $o->update(array('foo' => 'baz'));
        $this->assertEquals('baz', $o->offsetGet('foo'));
    }
    
    public function testUpdateMergeDoesNotAffectUnrelatedKeys()
    {
        $o = new Mad_Support_ArrayObject(array('foo' => 'bar'));
        $o->update(array('baz' => 'qux'));
        $this->assertEquals('qux', $o->offsetGet('baz'));
    }
    
    // clear()
    
    public function testClearErasesTheArray()
    {
        $o = new Mad_Support_ArrayObject(array('foo' => 'bar'));
        $o->clear();
        $this->assertEquals(0, $o->count());
    }
    
    // hasKey()
    
    public function testHasKeyReturnsTrueWhenKeyExists()
    {
        $o = new Mad_Support_ArrayObject(array('foo' => 'bar'));
        $this->assertTrue($o->hasKey('foo'));
    }
    
    public function testHasKeyReturnsFalseWhenKeyDoesNotExist()
    {
        $o = new Mad_Support_ArrayObject();
        $this->assertFalse($o->hasKey('foo'));
    }
    
    // getKeys()
    
    public function testGetKeysReturnsEmptyArrayWhenArrayIsEmpty()
    {
        $o = new Mad_Support_ArrayObject();
        $this->assertSame(array(), $o->getKeys());
    }
    
    public function testGetKeysReturnsArrayOfKeysInTheArray()
    {
        $o = new Mad_Support_ArrayObject(array('foo'=> 1, 'bar' => 2));
        $this->assertSame(array('foo', 'bar'), $o->getKeys());
    }
    
    // getValues()
    
    public function testGetValuesReturnsEmptyArrayWhenArrayIsEmpty()
    {
        $o = new Mad_Support_ArrayObject();
        $this->assertSame(array(), $o->getValues());
    }
    
    public function testGetValuesReturnsArrayOfValuesInTheArray()
    {
        $o = new Mad_Support_ArrayObject(array('foo' => 1, 'bar' => 2));
        $this->assertSame(array(1, 2), $o->getValues());
    }
}
