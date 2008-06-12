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
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config/environment.php';
}

/**
 * @todo Tests for sanitizeSql()
 * 
 * @group      model
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_Serializer_AttributeTest extends Mad_Test_Unit
{
    // set up new db by inserting dummy data into the db
    public function setUp()
    {
        $this->fixtures('unit_tests');
    }
    
    
    // Types

    public function testComputeTypeForString()
    {
        $record = $this->unit_tests('unit_test_1');
        
        $attribute = new Mad_Model_Serializer_Attribute('string_value', $record);
        $this->assertEquals('string', $attribute->getType());
    }

    public function testComputeTypeForText()
    {
        $record = $this->unit_tests('unit_test_1');
        
        $attribute = new Mad_Model_Serializer_Attribute('text_value', $record);
        $this->assertEquals('string', $attribute->getType());
    }

    public function testComputeTypeForTime()
    {
        $record = $this->unit_tests('unit_test_1');
        
        $attribute = new Mad_Model_Serializer_Attribute('time_value', $record);
        $this->assertEquals('datetime', $attribute->getType());
    }


    // Values

    public function testComputeValueForString()
    {
        $record = $this->unit_tests('unit_test_1');

        $attribute = new Mad_Model_Serializer_Attribute('string_value', $record);
        $this->assertEquals('name a', $attribute->getValue());
    }


    // attributes

    public function testGetDecorationsAddsTypes()
    {
        $record = $this->unit_tests('unit_test_1');

        $attribute = new Mad_Model_Serializer_Attribute('datetime_value', $record);
        $this->assertEquals(array('type' => 'datetime'), $attribute->getDecorations());
    }

    public function testGetDecorationsExcludesTypeForString()
    {
        $record = $this->unit_tests('unit_test_1');

        $attribute = new Mad_Model_Serializer_Attribute('string_value', $record);
        $this->assertEquals(array(), $attribute->getDecorations());
    }

    public function testGetDecorationsAddsNulls()
    {
        $record = $this->unit_tests('unit_test_1');
        $record->string_value = null;

        $attribute = new Mad_Model_Serializer_Attribute('string_value', $record);
        $this->assertEquals(array('null' => 'string'), $attribute->getDecorations());
    }

    // @todo - we have no fixtures with binary data
    public function testGetDecorationsAddsBinaryEncoding()
    {
        // $record = $this->unit_tests('unit_test_1');
        // $record->binary_value = null;

        // $attribute = new Mad_Model_Serializer_Attribute('string_value', $record);
        // $this->assertEquals(array('null' => 'string'), $attribute->getDecorations());
    }
}
