<?php
/**
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
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
 * Represents an HTTP response to the user.
 *
 * @group      controller
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Controller_Response_HttpTest extends Mad_Test_Unit
{
    // simulate http response data
    public function setUp()
    {
        $this->_response = new Mad_Controller_Response_Mock();
    }

    /*##########################################################################
    # Headers
    ##########################################################################*/

    // test setting a header
    public function testSetHeader()
    {
        $this->_response->setHeader('Test Header', false);
        $headers = $this->_response->getHeaders();

        $this->assertEquals(false, $headers['Test Header']);
    }

    // test setting a header that replaces
    public function testSetHeaderReplace()
    {
        $this->_response->setHeader('Test Header');
        $headers = $this->_response->getHeaders();

        $this->assertEquals(true, $headers['Test Header']);
    }

    // test setting the status
    public function testSetStatus()
    {
        $this->_response->setStatus('303 See Other');
        $this->assertEquals('303 See Other', $this->_response->getStatus());
    }

    public function testSetContentType()
    {
        $this->_response->setContentType($mimeType = 'application/xml');
        $headers = $this->_response->getHeaders();

        $this->assertTrue(isset($headers["Content-Type: $mimeType"]));        
    }

    // test getting headers for a 200 status
    public function testGetHeaders200()
    {
        $this->_response->setStatus('200 OK');
        $expected = array(
            'HTTP/1.1 200 OK' => true,
            'Connection: close' => true,
            'Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT' => true,
            'Expires: Mon, 26 Jul 1997 05:00:00 GMT' => true,
            'Cache-Control: no-store, no-cache, must-revalidate' => true,
            'Pragma: no-cache' => true
        );

        $this->assertEquals($expected, $this->_response->getHeaders());
    }

    // test getting headers for a 302 status
    public function testGetHeaders302()
    {
        $this->_response->setStatus('302 Found');
        $expected = array('HTTP/1.1 302 Found' => true);
        $this->assertEquals($expected, $this->_response->getHeaders());
    }

    // test getting headers for a 404 status
    public function testGetHeaders404()
    {
        $this->_response->setStatus('404 Page Not Found');
        $expected = array('HTTP/1.1 404 Page Not Found' => true);
        $this->assertEquals($expected, $this->_response->getHeaders());
    }


    // test setting a redirection url
    public function testRedirect()
    {
        $this->_response->redirect('/to/some/url');
        $expected = array('HTTP/1.1 302 Found'     => true,
                          'Location: /to/some/url' => true);
        $this->assertEquals($expected, $this->_response->getHeaders());

        $this->assertEquals('/to/some/url', $this->_response->getRedirectUrl());
    }

    // test setting the resource as not found
    public function testPageNotFound()
    {
        $this->_response->pageNotFound();
        $expected = array('HTTP/1.1 404 Page Not Found' => true);
        $this->assertEquals($expected, $this->_response->getHeaders());
    }


    // test getting status code for a 200 status
    public function testGetStatusCode200()
    {
        $this->_response->setStatus('200 OK');
        $this->assertEquals(200, $this->_response->getStatusCode());
    }

    // test getting status code for a 200 status
    public function testGetStatusCode302()
    {
        $this->_response->setStatus('302 Found');
        $this->assertEquals(302, $this->_response->getStatusCode());
    }

    // test getting status code for a 404 status
    public function testGetStatusCode404()
    {
        $this->_response->setStatus('404 Page Not Found');
        $this->assertEquals(404, $this->_response->getStatusCode());
    }

    // test getting if response is OK
    public function testGetIsOk()
    {
        $this->_response->setStatus('200 OK');
        $this->assertTrue($this->_response->getIsOk());
    }

    // test getting if response is NOT OK
    public function testGetIsNotOk()
    {
        $this->_response->setStatus('302 Found');
        $this->assertFalse($this->_response->getIsOk());
    }

    // test getting if response is a redirect
    public function testGetIsRedirect()
    {
        $this->_response->setStatus('302 Found');
        $this->assertTrue($this->_response->getIsRedirect());
    }

    // test getting if response is NOT a redirect
    public function testGetIsNotRedirect()
    {
        $this->_response->setStatus('200 OK');
        $this->assertFalse($this->_response->getIsRedirect());
    }


    /*##########################################################################
    # Body
    ##########################################################################*/

    // test setting the body of the response
    public function testSetBody()
    {
        $this->_response->setBody('Some Body Text');
        $this->assertEquals('Some Body Text', $this->_response->getBody());
    }


    /*##########################################################################
    # Setting Cookie/Session/Flash
    ##########################################################################*/
    
    // test setting cookies
    public function testSetCookie()
    {
        $this->_response->setCookie('test_cookie', 'hey cookie');
        $expected = array('value'      => 'hey cookie',
                          'expiration' => '0',
                          'path'       => '/');
        $this->assertEquals($expected, $this->_response->getCookie('test_cookie'));
    }
    
    // test setting cookies
    public function testSetCookieExpiration()
    {
        $this->_response->setCookie('test_cookie', 'hey cookie', strtotime('2008-01-01 12:00 pm'));
        $expected = array('value'      => 'hey cookie',
                          'expiration' => '1199217600',
                          'path'       => '/');
        $this->assertEquals($expected, $this->_response->getCookie('test_cookie'));
    }

    // test setting cookies
    public function testSetCookieExpirationPath()
    {
        $this->_response->setCookie('test_cookie', 'hey cookie', strtotime('2008-01-01 12:00 pm'), '/test');
        $expected = array('value'      => 'hey cookie',
                          'expiration' => '1199217600',
                          'path'       => '/test');
        $this->assertEquals($expected, $this->_response->getCookie('test_cookie'));
    }

    // test setting session data
    public function testSetSession()
    {
        $this->_response->setSession('test_session', 'hey session');
        $this->assertEquals('hey session', $this->_response->getSession('test_session'));
    }

    // test setting flash data
    public function testSetFlash()
    {
        $this->_response->setFlash('test_flash', 'hey flash');
        $this->assertEquals('hey flash', $this->_response->getFlash('test_flash'));
    }
}

?>