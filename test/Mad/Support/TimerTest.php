<?php
/**
 * @category   Mad
 * @package    Support
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
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
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Support_TimerTest extends Mad_Test_Unit
{
    public function setUp()
    {
    }

    // test instantiating a normal timer
    public function testTimer()
    {
        $t = new Mad_Support_Timer;
        $start   = $t->start();
        $elapsed = $t->finish();

        $this->assertTrue(is_array($start));
        $this->assertTrue(is_float($elapsed));
        $this->assertTrue($elapsed > 0);
    }

    // test getting the finish time before starting the timer
    public function testNotStartedYet()
    {
        try {
            $t = new Mad_Support_Timer();
            $t->finish();
        } catch (Mad_Support_Exception $e) {
            return;
        }
        $this->fail('Mad_Support_Exception was expected but not thrown');
    }
}
