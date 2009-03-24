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
class Mad_Madness_ConfigurationTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $config = Mad_Madness_Configuration::getInstance();
        $config->mailer->deliveryMethod = 'test';
    }
    
    public function tearDown()
    {
        $config = Mad_Madness_Configuration::getInstance();
        $config->mailer->deliveryMethod = 'test';
    }
    
    public function testSetControllerConfiguration()
    {
    }

    public function testSetMailerConfiguration()
    {
        $config = Mad_Madness_Configuration::getInstance();
        $config->mailer->deliveryMethod = 'test';
        $config->end();
        $this->assertEquals('test', Mad_Mailer_Base::$deliveryMethod);

        $config->mailer->deliveryMethod = 'sendmail';
        $config->end();
        $this->assertEquals('sendmail', Mad_Mailer_Base::$deliveryMethod);
    }

    public function testSetViewConfiguration()
    {
    }

    public function testSetModelConfiguration()
    {
        $config = Mad_Madness_Configuration::getInstance();
        $config->model->cacheTables = true;
        $config->end();
        $this->assertTrue(Mad_Model_Base::$cacheTables);

        $config->model->cacheTables = false;
        $config->end();
        $this->assertFalse(Mad_Model_Base::$cacheTables);
    }

    public function testSetTestConfiguration()
    {
    }
}