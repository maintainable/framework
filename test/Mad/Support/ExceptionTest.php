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
class Mad_Support_ExceptionTest extends Mad_Test_Unit
{
    public function testExtendsException()
    {
        $e = new Mad_Support_Exception();
        $this->assertTrue($e instanceof Exception);
    }

    public function testSetsMessageFromConstructor()
    {
        $e = new Mad_Support_Exception($msg='the message');
        $this->assertEquals($msg, $e->getMessage());
    }

    public function testSetsCodeFromConstructor()
    {
        $e = new Mad_Support_Exception($msg=null, $code=42);
        $this->assertEquals($code, $e->getCode());
    }

    public function testSetsFileFromConstructor()
    {
        $e = new Mad_Support_Exception($msg=null, $code=null, 
                                       $file='/foo.php');
        $this->assertEquals($file, $e->getFile());
    }

    public function testSetsLineFromConstructor()
    {
        $e = new Mad_Support_Exception($msg=null, $code=null, 
                                       $file=null, $line=42);
        $this->assertEquals($line, $e->getLine());
    }

    public function testTitleMirrorsClassName()
    {
        $e = new Mad_Support_Exception();
        $this->assertEquals(get_class($e), $e->getTitle());
    }

}
