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
 * @group      controller
 * @category   Mad
 * @package    Mad_Controller
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Controller_Rescue_SourceExtractorTest extends Mad_Test_Unit
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

    // convertTabsToSpaces
    
    public function testConvertTabsToSpacesHandlesLinesWithoutTabs()
    {
        $expected = array('', 'foo', ' ');
        $actual   = $this->extractor->convertTabsToSpaces($expected);
        $this->assertEquals($expected, $actual);
    }

    public function testConvertTabsToSpacesUsesDefaultTabStopOfFour()
    {
        $lines    = array("\t\t", 
                          "foo\tbar",
                          "\tfoo");
        $expected = array("        ",
                          "foo bar",
                          "    foo");
        $actual   = $this->extractor->convertTabsToSpaces($expected);
        $this->assertEquals($expected, $actual);
    }
    
    public function testConvertTabsToSpacesAllowsTabStopToBeChanged()
    {
        $lines    = array("\t\t", 
                          "foo\tbar");
        $expected = array("  ",
                          "foo bar",
                          "  foo");
        $actual   = $this->extractor->convertTabsToSpaces($expected, 2);
        $this->assertEquals($expected, $actual);        
    }
}
