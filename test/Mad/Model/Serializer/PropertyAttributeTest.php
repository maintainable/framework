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
class Mad_Model_Serializer_PropertyAttributeTest extends Mad_Test_Unit
{
    // set up new db by inserting dummy data into the db
    public function setUp()
    {
        $this->fixtures('articles');
    }
    
    public function testComputeTypeForString()
    {
        $record = $this->articles('xml_rpc');
        $record->validity = 'hey';
        
        $attribute = new Mad_Model_Serializer_PropertyAttribute('validity', $record);
        $this->assertEquals('string', $attribute->getType());
    }

    public function testComputeTypeForBoolean()
    {
        $record = $this->articles('xml_rpc');
        $record->validity = true;

        $attribute = new Mad_Model_Serializer_PropertyAttribute('validity', $record);
        $this->assertEquals('boolean', $attribute->getType());
    }

    public function testComputeTypeForInteger()
    {
        $record = $this->articles('xml_rpc');
        $record->validity = 1;

        $attribute = new Mad_Model_Serializer_PropertyAttribute('validity', $record);
        $this->assertEquals('integer', $attribute->getType());
    }

    public function testComputeTypeForFloat()
    {
        $record = $this->articles('xml_rpc');
        $record->validity = 1.2;

        $attribute = new Mad_Model_Serializer_PropertyAttribute('validity', $record);
        $this->assertEquals('float', $attribute->getType());
    }
}