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
 * @group      controller
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Controller_Rescue_SourceExtractorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->extractor = new Mad_Controller_Rescue_SourceExtractor();
    }
    
    // stripWhitespace()
    
    public function testStripWhitespaceStripsAllRightWhitespace()
    {
        $lines    = array("foo\t \n \t", "  ");
        $expected = array("foo", "");

        $actual   = $this->extractor->stripWhitespace($lines);
        $this->assertEquals($expected, $actual);
    }
    
    public function testStripWhitespaceStripsNothingAtLeftWhenNoCommonLeftWhitespace()
    {
        $lines    = array('foo', 
                          ' bar');
        $expected = array('foo',
                          ' bar');

        $actual   = $this->extractor->stripWhitespace($lines);
        $this->assertEquals($expected, $actual);
    }

    public function testStripWhitespaceIgnoresEmptyLinesWhenCountingLeftWhitespace()
    {
        $lines = array('', 
                       ' foo', 
                       ' bar');
        
        $expected = array('',
                          'foo',
                          'bar');

        $actual = $this->extractor->stripWhitespace($lines);
        $this->assertEquals($expected, $actual);                            
    }

    public function testStripWhitespaceIgnoresWhitespaceLinesWhenCountingLeftWhitespace()
    {
        $lines = array("\t \t ", 
                       ' foo', 
                       ' bar');
        
        $expected = array('',
                          'foo',
                          'bar');

        $actual = $this->extractor->stripWhitespace($lines);
        $this->assertEquals($expected, $actual);                            
    }

    public function testStripWhitespaceStripsOnlyCommonLeftWhitespaceToPreserveIndentation()
    {
        $lines    = array("    Quick",
                          "     brown", 
                          "    fox", 
                          "   jumps");

        $expected = array(" Quick",
                          "  brown",
                          " fox",
                          "jumps");

        $actual = $this->extractor->stripWhitespace($lines);
        $this->assertEquals($expected, $actual);        
    }

}
