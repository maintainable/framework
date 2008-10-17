<?php
/**
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Built-in framework tasks for testing.
 *
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Task_BuiltinSet_Testing extends Mad_Task_Set
{
    /**
     * Test all units and functionals
     */
    public function test()
    {
        chdir(MAD_ROOT . '/test');
        passthru('phpunit AllTests');
    }

    /**
     * Run the unit tests in test/unit
     */
    public function test_units()
    {
        chdir(MAD_ROOT . '/test');
        passthru('phpunit --group functional AllTests');
    }

    /**
     * Run the functional tests in test/functional
     */
    public function test_functionals()
    {
        chdir(MAD_ROOT . '/test');
        passthru('phpunit --group unit AllTests');        
    }

}
