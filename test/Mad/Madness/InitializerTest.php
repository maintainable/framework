<?php
/**
 * @category   Mad
 * @package    Mad_Madness
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
 * @group      madness
 * @category   Mad
 * @package    Mad_Madness
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Madness_InitializerTest extends Mad_Test_Unit
{
    public function testRun()
    {
        $config = Mad_Madness_Initializer::run();
        $this->assertType('Mad_Madness_Configuration', $config);
    }
}