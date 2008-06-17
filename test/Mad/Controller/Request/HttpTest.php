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
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config/environment.php';
}

/**
 * Represents an HTTP request to the server. This class handles all headers/cookies/session
 * data so that it all has one point of entry for being written/retrieved.
 *
 * @group      controller
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Controller_Request_HttpTest extends Mad_Test_Unit
{
    // simulate http request data
    public function setUp()
    {
        $this->_req = new Mad_Controller_Request_Mock();
    }

    /*##########################################################################
    # Instantiation Tests
    ##########################################################################*/

    // make sure all request Id's are unique
    public function testRequestId()
    {
        $req1 = new Mad_Controller_Request_Mock();
        $reqId1 = $req1->getRequestId();

        $req2 = new Mad_Controller_Request_Mock();
        $reqId2 = $req2->getRequestId();

        $this->assertFalse($reqId1 == $reqId2);
    }




    /*##########################################################################
    # Staging Tests
    ##########################################################################*/

    // test domain
    public function testGetDomain()
    {
        $this->assertEquals('www.maintainable.com', $this->_req->getDomain());
    }

    // test getting host
    public function testGetHostWithPort()
    {
        $this->assertEquals('https://www.maintainable.com:33443', $this->_req->getHost(true));
    }

    // test getting host
    public function testGetHostWithoutPort()
    {
        $this->assertEquals('https://www.maintainable.com', $this->_req->getHost());
    }

    // test getting host
    public function testGetHostDefaultPort()
    {
        $this->_req->setPort('80');
        $this->assertEquals('https://www.maintainable.com', $this->_req->getHost());
    }

    // test uri on staging
    public function testGetUri()
    {
        $this->assertEquals("hello/?test=true", $this->_req->getUri());
    }

    // test path on staging - should strip out /~ddevries2/bsf19/web/ && get params
    public function testGetPath()
    {
        $this->assertEquals('hello', $this->_req->getPath());
    }

    // test method
    public function testGetMethod()
    {
        $this->assertEquals('GET', $this->_req->getMethod());
    }

    // test getting server vars
    public function testGetServer()
    {
        $this->assertEquals('127.0.0.1', $this->_req->getServer('SERVER_ADDR'));
    }

    // test getting env vars
    public function testGetEnv()
    {
        $this->assertEquals('my unit test data', $this->_req->getEnv('TEST_DATA'));
    }

    // test getting combined params
    public function testgetParameters()
    {
        $this->_req->setPathParams(array('test3' => 'true'));

        $expected = array(
            'test3'      => 'true',
            'get_test1'  => 'true',
            'get_test2'  => 'go mets',
            'post_test1' => 'false',
            'post_test2' => 'go yanks', 

            'picture' => new Mad_Controller_FileUpload(array(
                'name'     => 'my_picture.gif',
                'type'     => 'image/gif',
                'size'     => '1234567',
                'tmp_name' => '/tmp/test1')), 
            'document' => array(
                'name'     => 'hey',
                'filesize' => 100,
                'icon' => new Mad_Controller_FileUpload(array(
                    'name'     => 'dummy.gif',
                    'type'     => 'image/gif',
                    'size'     => '32',
                    'tmp_name' => '/tmp/test2')), 
                'photo' => new Mad_Controller_FileUpload(array(
                    'name'     => 'dummy.jpg',
                    'type'     => 'image/jpeg',
                    'size'     => '45',
                    'tmp_name' => '/tmp/test3'))
            ));
        $this->assertEquals($expected, $this->_req->getParameters());
    }

    // test getting the $_GET vars
    public function testGetGetParams()
    {
        $expected = array('document' => array('filesize' => 100),
                          'get_test1'  => 'true', 'get_test2' => 'go mets');
        $this->assertEquals($expected, $this->_req->getGetParams());
    }

    // test getting the $_POST vars
    public function testGetPostParams()
    {
        // simulate a post
        $expected = array('document' => array('name' => 'hey'),
                          'post_test1' => 'false', 'post_test2' => 'go yanks');
        $this->assertEquals($expected, $this->_req->getPostParams());
    }

    // test getting the $_FILES vars
    public function testGetFilesParams()
    {
        $files = $this->_req->getFilesParams();
        $picture = $files['picture'];
        $this->assertType('Mad_Controller_FileUpload', $picture);

        $icon = $files['document']['icon'];
        $this->assertType('Mad_Controller_FileUpload', $icon);

        $photo = $files['document']['photo'];
        $this->assertType('Mad_Controller_FileUpload', $photo);
    }
    
    // test getting the path vars set from routing
    public function testGetPathParams()
    {
        $this->_req->setPathParams(array('test3' => 'true'));
        $expected = array('test3' => 'true');
        $this->assertEquals($expected, $this->_req->getPathParams());
    }


    /*##########################################################################
    # Test Method/URI
    ##########################################################################*/

    // test setting the request method
    public function testSetMethod()
    {
        $this->_req->setMethod('SOMETHING');
        $this->assertEquals('SOMETHING', $this->_req->getMethod());
    }

    // test setting the remote ip of the request
    public function testSetRemoteIp()
    {
        $this->_req->setRemoteIp('999.99.998.9');
        $this->assertEquals('999.99.998.9', $this->_req->getRemoteIp());
    }

    // test setting the uri
    public function testSetUri()
    {
        $this->_req->setUri('something/to/test');
        $this->assertEquals('something/to/test', $this->_req->getUri());
    }

    // test setting the uri
    public function testSetPathUriTrim()
    {
        $this->_req->setUri('/something/to/test/');
        $this->assertEquals('something/to/test', $this->_req->getUri());
    }


    /*##########################################################################
    # Session tests
    ##########################################################################*/

    // test getting cookie
    public function testGetCookie()
    {
        $this->assertEquals('cookie value', $this->_req->getCookie('my_test_cookie'));
    }

    // test getting cookie array
    public function testGetCookieArray()
    {
        $expected = array('my_test_cookie'  => 'cookie value', 
                          'my_other_cookie' => 'cookie stuff');
        $this->assertEquals($expected, $this->_req->getCookie());
    }

    // test getting cookie using default value
    public function testGetCookieDefault()
    {
        $val = $this->_req->getCookie('DOESNT_EXIST', 'default value');
        $this->assertEquals('default value', $val);
    }


    // test getting session data
    public function testGetSession()
    {
        $this->assertEquals('session value', $this->_req->getSession('my_test_session'));
    }

    // test getting session data array
    public function testGetSessionArray()
    {
        $expected = array('my_test_session'  => 'session value', 
                          'my_other_session' => 'session stuff');
        $this->assertEquals($expected, $this->_req->getSession());
    }

    // test getting session using default value
    public function testGetSessionDefault()
    {
        $val = $this->_req->getSession('DOESNT_EXIST', 'default value');
        $this->assertEquals('default value', $val);
    }


    // test getting flash
    public function testGetFlash()
    {
        $this->assertEquals('flash value', $this->_req->getFlash('my_test_flash'));
    }

    // test getting flash array
    public function testGetFlashArray()
    {
        $expected = array('my_test_flash'  => 'flash value', 
                          'my_other_flash' => 'flash stuff');
        $this->assertEquals($expected, $this->_req->getFlash());
    }

    // test getting flash using default value
    public function testGetFlashDefault()
    {
        $val = $this->_req->getFlash('DOESNT_EXIST', 'default value');
        $this->assertEquals('default value', $val);
    }


    // test setting flash
    public function testSetFlashSingle()
    {
        $this->assertEquals(null, $this->_req->getFlash('foo'));
        $this->_req->setFlash('foo', 'bar');
        $this->assertEquals('bar', $this->_req->getFlash('foo'));
    }

    // test setting flash
    public function testSetFlashArray()
    {
        $this->assertEquals(null, $this->_req->getFlash('foo'));
        $this->_req->setFlash(array('foo' => 'bar'));
        $this->assertEquals('bar', $this->_req->getFlash('foo'));
    }


    // test setting flash
    public function testSetSessionSingle()
    {
        $this->assertEquals(null, $this->_req->getSession('foo'));
        $this->_req->setSession('foo', 'bar');
        $this->assertEquals('bar', $this->_req->getSession('foo'));
    }

    // test setting flash
    public function testSetSessionArray()
    {
        $this->assertEquals(null, $this->_req->getSession('foo'));
        $this->_req->setSession(array('foo' => 'bar'));
        $this->assertEquals('bar', $this->_req->getSession('foo'));
    }


    // test setting flash
    public function testSetCookieSingle()
    {
        $this->assertEquals(null, $this->_req->getCookie('foo'));
        $this->_req->setCookie('foo', 'bar');
        $this->assertEquals('bar', $this->_req->getCookie('foo'));
    }

    // test setting flash
    public function testSetCookieArray()
    {
        $this->assertEquals(null, $this->_req->getCookie('foo'));
        $this->_req->setCookie(array('foo' => 'bar'));
        $this->assertEquals('bar', $this->_req->getCookie('foo'));
    }


    /*##########################################################################
    # Mime/Content type methods
    ##########################################################################*/

    public function testGetBody()
    {
        $xml = '<people type="array"><person><id>1</id></person></people>';
        $req = new Mad_Controller_Request_Mock();
        $req->setBody($xml);

        $this->assertEquals($xml, $req->getBody());
    }

    public function testGetContentLength()
    {
        $xml = '<people type="array"><person><id>1</id></person></people>';
        $req = new Mad_Controller_Request_Mock();
        $req->setBody($xml);

        $this->assertEquals(57, $req->getContentLength());
    }

    public function testGetContentType()
    {
        $this->_req->setServer('CONTENT_TYPE', 'text/javascript');
        
        $this->assertEquals('js', (string)$this->_req->getContentType());
    }

    public function testGetAcceptsWithJavascriptHttpAccept()
    {
        $name  = 'text/javascript';
        $this->_req->setServer('HTTP_ACCEPT', 'text/javascript');
        
        $this->assertEquals('js', (string)current($this->_req->getAccepts()));
    }

    public function testGetAcceptsWithHtmlHttpAccept()
    {   
        $this->assertEquals('html', (string)current($this->_req->getAccepts()));
    }

    public function testGetFormatWithFormatParam()
    {
        $this->_req->setPathParams(array('format' => 'js'));

        $this->assertEquals('js', (string)$this->_req->getFormat());
    }

    public function testGetFormatFromHttpAccept()
    {
        $this->assertEquals('html', (string)$this->_req->getFormat());
    }

    public function testFormattedRequestParams()
    {
        $xml = '<people type="array"><person><id>1</id></person></people>';

        $req = new Mad_Controller_Request_Mock();
        $req->setContentType('xml');
        $req->setBody($xml);

        $params = $req->getParameters();

        $people = $params['people'];
        $this->assertEquals(1, $people[0]['id']);
    }

    /*##########################################################################
    ##########################################################################*/
}
