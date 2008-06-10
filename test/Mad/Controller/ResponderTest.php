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
    
    public function testRespondsToJsWhenUriEndsWithDotJs()
    {
        $request = new Mad_Controller_Request_Mock();
        $request->setServer('HTTP_ACCEPT', '');
        $request->setUri('foo.js');
        
        $responder = new Mad_Controller_Responder($request);
        $this->assertTrue($responder->js);
    }
    
    public function testRespondsToJsWhenAcceptHeaderIsTextJavascript()
    {
        $request = new Mad_Controller_Request_Mock();
        $request->setServer('HTTP_ACCEPT', 'text/javascript');
        $request->setUri('');
                
        $responder = new Mad_Controller_Responder($request);
        $this->assertTrue($responder->js);
    }
    
    // XML

    public function testRespondsToXmlWhenUriEndsWithDotXml()
    {
        $request = new Mad_Controller_Request_Mock();
        $request->setServer('HTTP_ACCEPT', '');
        $request->setUri('foo.xml');
        
        $responder = new Mad_Controller_Responder($request);
        $this->assertTrue($responder->xml);
    }
    
    public function testRespondsToXmlWhenAcceptHeaderIsTextXml()
    {
        $request = new Mad_Controller_Request_Mock();
        $request->setServer('HTTP_ACCEPT', 'text/xml');
        $request->setUri('');
                
        $responder = new Mad_Controller_Responder($request);
        $this->assertTrue($responder->xml);
    }

    // HTML
    
    public function testRespondsToHtmlWhenUriEndsWithDotHtml()
    {
        $request = new Mad_Controller_Request_Mock();
        $request->setServer('HTTP_ACCEPT', '');
        $request->setUri('foo.html');
        
        $responder = new Mad_Controller_Responder($request);
        $this->assertTrue($responder->html);
    }
    
    public function testRespondsToHtmlWhenAcceptHeaderIsTextHtml()
    {
        $request = new Mad_Controller_Request_Mock();
        $request->setServer('HTTP_ACCEPT', 'text/html');
        $request->setUri('');
                
        $responder = new Mad_Controller_Responder($request);
        $this->assertTrue($responder->html);
    }
    
    public function testRespondToDefaultsToHtml()
    {
        $request = new Mad_Controller_Request_Mock();
        $request->setServer('HTTP_ACCEPT', '');
        $request->setUri('');

        $responder = new Mad_Controller_Responder($request);
        $this->assertTrue($responder->html);
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