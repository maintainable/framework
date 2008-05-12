<?= "<?php\n" ?>

if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) require_once dirname(dirname(dirname(__FILE__))).'/config/environment.php';

/**
 * @group functional
 */
class <?= $this->className ?>Test extends Mad_Test_Functional
{
    // set up test
    public function setUp()
    {
        $this->request  = new Mad_Controller_Request_Mock();
        $this->response = new Mad_Controller_Response_Mock();
    }

    // replace with your test
    public function testTrue()
    {
        $this->assertTrue(true);
    }
}
