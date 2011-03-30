<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
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
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_View_Helper_CaptureTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->view   = new Mad_View_Base();
        $this->helper = new Mad_View_Helper_Capture($this->view);
    }
    
    public function testCapture()
    {
        $capture = $this->helper->capture();
        echo $expected = '<span>foo</span>';
        
        $this->assertEquals($expected, $capture->end());
    }
    
    public function testCaptureThrowsWhenAlreadyEnded()
    {
        $capture = $this->helper->capture();
        $capture->end();
        
        try {
            $capture->end();
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf('Mad_View_Exception', $e);
            $this->assertRegExp('/capture already ended/i', $e->getMessage());
        }
    }
    
    public function testContentFor()
    {
        $capture = $this->helper->contentFor('foo');
        echo $expected = '<span>foo</span>';
        $capture->end();

        $this->assertEquals($expected, $this->view->contentForFoo);
    }
    
}
