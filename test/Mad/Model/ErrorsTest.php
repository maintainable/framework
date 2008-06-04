<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config/environment.php';
}

/**
 * @group      model
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_ErrorsTest extends Mad_Test_Unit
{
    public function setup()
    {
        $this->_errors = new Mad_Model_Errors(new UnitTest);
    }

    public function testAddErrorToAttributeAppendsErrorList()
    {
        $this->_errors->add('string_value', "Foo");
        $this->_errors->add('text_value',   "Bar 1");
        $this->_errors->add('text_value',   "Bar 2");

        // assertion of attributes
        $this->assertEquals(array('Foo'), $this->_errors->on('string_value'));
        $this->assertEquals(array('Bar 1', 'Bar 2'), $this->_errors->on('text_value'));
    }
    
    public function testAddErrorDefaultMessageIsInvalid()
    {
        $this->_errors->add('string_value');
        $i = 0;
        foreach ($this->_errors as $key => $value) {
            if ($i == 0) {
                $this->assertEquals("string_value = is invalid", "$key = $value");
            }
            $i++;
        }
        $this->assertTrue($i > 0);
    }

    public function testAddToBase()
    {
        $this->_errors->addToBase("Foo");
        $this->assertEquals(array('Foo'), $this->_errors->onBase());
    }

    public function testClear()
    {
        $this->_errors->add('string_value', "Foo");
        $this->_errors->add('text_value',   "Bar 1");
        $this->_errors->add('text_value',   "Bar 2");

        $this->assertEquals(3, count($this->_errors));
        $this->_errors->clear();
        $this->assertEquals(0, count($this->_errors));
    }

    public function testOn()
    {
        $this->_errors->add('string_value', "Foo");
        $this->_errors->add('text_value',   "Bar 1");
        $this->_errors->add('text_value',   "Bar 2");

        // assertion of attributes
        $this->assertEquals(array('Foo'), $this->_errors->on('string_value'));
        $this->assertEquals(array('Bar 1', 'Bar 2'), $this->_errors->on('text_value'));
    }

    public function testOnBase()
    {
        $this->_errors->addToBase("Foo");
        $this->_errors->addToBase("Bar");

        $this->assertEquals(array('Foo', 'Bar'), $this->_errors->onBase());
    }

    public function testIsInvalid()
    {
        $this->_errors->add("string_value", "Error");

        $this->assertTrue($this->_errors->isInvalid('string_value'));
        $this->assertFalse($this->_errors->isInvalid('text_value'));
    }

    public function testIsEmpty()
    {
        $this->assertTrue($this->_errors->isEmpty());
        $this->_errors->add("string_value", "Error");
        $this->assertFalse($this->_errors->isEmpty());
    }

    public function testCount()
    {
        $this->_errors->add('string_value', "Foo");
        $this->_errors->add('text_value',   "Bar 1");
        $this->_errors->add('text_value',   "Bar 2");
        $this->_errors->add('date_value',   "Baz");

        $this->assertEquals(4, count($this->_errors));
    }

    public function testIteratable()
    {
        $this->_errors->add('string_value', "Foo");
        $this->_errors->add('text_value',   "Bar 1");
        $this->_errors->add('text_value',   "Bar 2");
        $this->_errors->add('date_value',   "Baz");

        $i = 0;
        foreach ($this->_errors as $key => $value) {
            if ($i == 0) {
                $this->assertEquals("string_value = Foo", "$key = $value");
            } elseif ($i == 1) {
                $this->assertEquals("text_value = Bar 1", "$key = $value");
            } elseif ($i == 2) {
                $this->assertEquals("text_value = Bar 2", "$key = $value");
            } elseif ($i == 3) {
                $this->assertEquals("date_value = Baz", "$key = $value");
            }
            $i++;
        }
    }

    public function testFullMessages()
    {
        $this->_errors->add('string_value', "Foo");
        $this->_errors->add('text_value',   "Bar 1");
        $this->_errors->add('text_value',   "Bar 2");
        $this->_errors->add('date_value',   "Baz");

        $expected = array("String value Foo", "Text value Bar 1",
                          "Text value Bar 2", "Date value Baz");
        $this->assertEquals($expected, $this->_errors->fullMessages());
    }
    
    /*##########################################################################
    ##########################################################################*/
}