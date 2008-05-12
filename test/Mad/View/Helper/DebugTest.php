<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential 
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
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential
 */
class Mad_View_Helper_DebugTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->helper = new Mad_View_Helper_Debug(new Mad_View_Base());
    }

    // test truncate
    public function testDebug()
    {
        $expected = '<pre class="debug_dump">string(7) &quot;foo&amp;bar&quot;';
        $this->assertContains($expected, $this->helper->debug('foo&bar'));
    }
    
}
