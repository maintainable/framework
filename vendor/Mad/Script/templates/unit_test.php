<?= "<?php\n" ?>

if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
<? if (strstr($this->className, '_')): ?>
if (!defined('MAD_ROOT')) require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config/environment.php';
<? else: ?>
if (!defined('MAD_ROOT')) require_once dirname(dirname(dirname(__FILE__))).'/config/environment.php';
<? endif ?>

/**
 * @group unit
 */
class <?= $this->className ?>Test extends Mad_Test_Unit
{
    // set up test
    public function setUp()
    {
    }

    // replace with your test
    public function testTrue()
    {
        $this->assertTrue(true);
    }
}
