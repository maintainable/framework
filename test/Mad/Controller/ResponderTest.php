<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
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
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Controller_ResponderTest extends Mad_Test_Unit
{
    // JavaScript
    
    public function testRespondsToJsWhenFormatIsJs()
    {
        $request = new Mad_Controller_Request_Mock();
        $request->setServer('HTTP_ACCEPT', '');
        $request->setPathParams(array('format' => 'js'));
        $request->setUri('');

        $responder = new Mad_Controller_Responder($request);
        $this->assertTrue($responder->js);
        $this->assertFalse($responder->xml);
        $this->assertFalse($responder->html);
    }

    public function testRespondsToJsWhenAcceptHeaderIsTextJavascript()
    {
        $request = new Mad_Controller_Request_Mock();
        $request->setServer('HTTP_ACCEPT', 'text/javascript');
        $request->setUri('');
                
        $responder = new Mad_Controller_Responder($request);
        $this->assertTrue($responder->js);
        $this->assertFalse($responder->xml);
        $this->assertFalse($responder->html);
    }
    
    // XML

    public function testRespondsToXmlWhenFormatIsXml()
    {
        $request = new Mad_Controller_Request_Mock();
        $request->setServer('HTTP_ACCEPT', '');
        $request->setPathParams(array('format' => 'xml'));
        $request->setUri('');
        
        $responder = new Mad_Controller_Responder($request);
        $this->assertTrue($responder->xml);
        $this->assertFalse($responder->js);
        $this->assertFalse($responder->html);
    }
    
    public function testRespondsToXmlWhenAcceptHeaderIsTextXml()
    {
        $request = new Mad_Controller_Request_Mock();
        $request->setServer('HTTP_ACCEPT', 'text/xml');
        $request->setUri('');
                
        $responder = new Mad_Controller_Responder($request);
        $this->assertTrue($responder->xml);
        $this->assertFalse($responder->js);
        $this->assertFalse($responder->html);
    }

    // HTML
    
    public function testRespondsToHtmlWhenFormatIsHtml()
    {
        $request = new Mad_Controller_Request_Mock();
        $request->setServer('HTTP_ACCEPT', '');
        $request->setPathParams(array('format' => 'html'));
        $request->setUri('');
        
        $responder = new Mad_Controller_Responder($request);
        $this->assertTrue($responder->html);
        $this->assertFalse($responder->js);
        $this->assertFalse($responder->xml);
    }
    
    public function testRespondsToHtmlWhenAcceptHeaderIsTextHtml()
    {
        $request = new Mad_Controller_Request_Mock();
        $request->setServer('HTTP_ACCEPT', 'text/html');
        $request->setUri('');
                
        $responder = new Mad_Controller_Responder($request);
        $this->assertTrue($responder->html);
        $this->assertFalse($responder->js);
        $this->assertFalse($responder->xml);
    }
    
    public function testRespondToDefaultsToAll()
    {
        $request = new Mad_Controller_Request_Mock();
        $request->setServer('HTTP_ACCEPT', '');
        $request->setUri('');

        $responder = new Mad_Controller_Responder($request);
        $this->assertTrue($responder->html);
        $this->assertTrue($responder->js);
        $this->assertTrue($responder->xml);
    }
    
    // __call()
    
    public function test__CallThrowsBadMethodCallException()
    {
        $request = new Mad_Controller_Request_Mock();
        $responder = new Mad_Controller_Responder($request);
        
        try {
            $responder->xml(); 
        } catch (BadMethodCallException $e) {
            return;
        }
        $this->fail();
    }
}