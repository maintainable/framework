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
class Mad_Model_Serializer_MethodAttributeTest extends Mad_Test_Unit
{
    // set up new db by inserting dummy data into the db
    public function setUp()
    {
        $this->fixtures('articles');
    }
    
    public function testComputeTypeForString()
    {
        $record = $this->articles('xml_rpc');
        
        $attribute = new Mad_Model_Serializer_MethodAttribute('foo', $record);
        $this->assertEquals('string', $attribute->getType());
    }

    public function testComputeTypeForBoolean()
    {
        $record = $this->articles('xml_rpc');
        
        $attribute = new Mad_Model_Serializer_MethodAttribute('boolMethod', $record);
        $this->assertEquals('boolean', $attribute->getType());
    }

    public function testComputeTypeForInteger()
    {
        $record = $this->articles('xml_rpc');
        
        $attribute = new Mad_Model_Serializer_MethodAttribute('intMethod', $record);
        $this->assertEquals('integer', $attribute->getType());
    }

    public function testComputeTypeForFloat()
    {
        $record = $this->articles('xml_rpc');
        
        $attribute = new Mad_Model_Serializer_MethodAttribute('floatMethod', $record);
        $this->assertEquals('float', $attribute->getType());
    }
}