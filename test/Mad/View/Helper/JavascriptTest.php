<?php
/**
 * @category   Mad
 * @package    Mad_View
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
 * @group      view
 * @category   Mad
 * @package    Mad_View
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_View_Helper_JavascriptTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->view = new Mad_View_Base();
        $this->view->addHelper(new Mad_View_Helper_Javascript($this->view));
    }

    public function testJsonEncode()
    {
        try {
            $this->assertEquals('{"foo":"bar"}', 
            $this->view->jsonEncode(array('foo'=>'bar')));
        } catch (Mad_View_Exception $e) {
            if (function_exists('json_encode')) { 
                throw $e; 
            } else {
                $this->assertRegExp('/json_encode/', $e->getMessage());
            }            
        }
    }

}
