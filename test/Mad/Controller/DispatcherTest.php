<?php
/**
 * @category   Mad
 * @package    Mad_Controller
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
 * Used for functional testing of controller classes
 *
 * @group      controller
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential
 */
class Mad_Controller_DispatcherTest extends Mad_Test_Unit
{
    // set up
    public function setUp()
    {
    }
    
    public function testTruth() 
    {
        $this->assertTrue(true);
    }
}