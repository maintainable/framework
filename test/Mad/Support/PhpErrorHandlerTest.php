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
class Mad_Support_PhpErrorHandlerTest extends Mad_Test_Unit
{
    public function testHandleThrowsPhpErrorAsMadSupportException()
    {
        Mad_Support_PhpErrorHandler::install();

        try {
            trigger_error('should be thrown', E_USER_ERROR);
            restore_error_handler();
            $this->fail();
        } catch (Mad_Support_Exception $e) {
            $this->assertEquals('should be thrown', $e->getMessage());
            $this->assertEquals(E_USER_ERROR, $e->getCode());
        }

        restore_error_handler();
    }

    public function testHandleDoesNotThrowSilencedErrors()
    {
        Mad_Support_PhpErrorHandler::install();
        @trigger_error("should never be thrown", E_USER_ERROR);
        restore_error_handler();
    }
}
