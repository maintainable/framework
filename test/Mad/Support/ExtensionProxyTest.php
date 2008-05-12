<?php
/**
 * @category   Mad
 * @package    Mad_Support
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential 
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
 * @package    Mad_Support
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential
 */
class Mad_Support_ExtensionProxyTest extends Mad_Test_Unit
{
    public function testConstructorThrowsExceptionWhenExtensionDoesNotExist()
    {
        $extension = 'extension_name_that_does_not_exist';
        
        try {
            new Mad_Support_ExtensionProxy($extension);
            $this->fail();
        } catch (Mad_Support_Exception $e) {
            $this->assertEquals("Required extension '$extension' is not loaded",
                                $e->getMessage());
        }
    }

    public function testExposesTheExtensionNameAsPublicProperty()
    {
        $spl = new Mad_Support_ExtensionProxy('spl');
        $this->assertEquals('spl', $spl->extension);
    }

    public function testProxiesMethodCallsToTheirProceduralEquivalents()
    {
        $obj = new stdClass;
        $expected = spl_object_hash($obj);
        
        $spl = new Mad_Support_ExtensionProxy('spl');
        $actual = $spl->object_hash($obj);

        $this->assertEquals($expected, $actual);
    }
}
