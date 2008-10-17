<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD 
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
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
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
        $this->assertEquals(array('nil' => 'true'), $attribute->getDecorations());
    }

    public function testGetDecorationsAddsBinaryEncoding()
    {
        $record = $this->unit_tests('unit_test_1');

        $attribute = new Mad_Model_Serializer_Attribute('blob_value', $record);
        $this->assertEquals(array('type' => 'binary', 'encoding' => 'base64'), $attribute->getDecorations());
    }
}
