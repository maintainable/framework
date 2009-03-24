<?php
/**
 * @category   Mad
 * @package    Mad_Controller
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
 * Represents an HTTP request to the server. This class handles all headers/cookies/session
 * data so that it all has one point of entry for being written/retrieved.
 *
 * @group      controller
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Controller_Mime_TypeTest extends Mad_Test_Unit
{
    public function setUp()
    {
        // default types
        Mad_Controller_Mime_Type::registerTypes();
    }

    public function testNewMimeType()
    {
        $string   = 'text/html';
        $symbol   = 'html';
        $synonyms = array('application/xhtml+xml');

        $type = new Mad_Controller_Mime_Type($string, $symbol, $synonyms);
        
        $this->assertEquals('text/html', $type->string);
        $this->assertEquals('html',      $type->symbol);
        $this->assertEquals(array('application/xhtml+xml'), $type->synonyms);
    }
    
    public function testToString()
    {
        $string   = 'text/html';
        $symbol   = 'html';
        $synonyms = array('application/xhtml+xml');

        $type = new Mad_Controller_Mime_Type($string, $symbol, $synonyms);
        
        $this->assertEquals('html', (string)$type);
    }
    
    public function testLookup()
    {
        $xml = Mad_Controller_Mime_Type::lookup("text/xml");
        $this->assertNotNull($xml);
    }
    
    public function testLookupByExtension()
    {   
        $xml = Mad_Controller_Mime_Type::lookupByExtension("xml");
        $this->assertNotNull($xml);
    }
    
    public function testRegister()
    {
        $xml = Mad_Controller_Mime_Type::lookupByExtension("iphone");
        $this->assertNull($xml);
        
        Mad_Controller_Mime_Type::register('text/iphone', 'apl', array('text/phone'), array('aap'));

        $iphone = Mad_Controller_Mime_Type::lookup("text/iphone");
        $this->assertNotNull($iphone);

        $iphone = Mad_Controller_Mime_Type::lookupByExtension("apl");
        $this->assertNotNull($iphone);
    }
    
    public function testParseJavascript()
    {
        $accept = 'text/javascript';
        $result = Mad_Controller_Mime_Type::parse($accept);
        $this->assertEquals(1, count($result));
        
        $this->assertEquals('js', (string)$result[0]);
    }
    
    public function testParseHtml()
    {
        $accept = 'text/html';
        $result = Mad_Controller_Mime_Type::parse($accept);
        $this->assertEquals(1, count($result));

        $this->assertEquals('html', (string)$result[0]);
    }    

    public function testParseXml()
    {
        $accept = 'text/xml';
        $result = Mad_Controller_Mime_Type::parse($accept);
        $this->assertEquals(1, count($result));

        $this->assertEquals('xml', (string)$result[0]);
    }

    public function testRegisterTypes()
    {
        Mad_Controller_Mime_Type::$registered = false;
        Mad_Controller_Mime_Type::$set = array();
        
        $this->assertTrue(empty(Mad_Controller_Mime_Type::$set));
        
        Mad_Controller_Mime_Type::registerTypes();
        $this->assertFalse(empty(Mad_Controller_Mime_Type::$set));
    }
}