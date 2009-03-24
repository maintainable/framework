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
class Mad_View_Helper_TagTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->helper = new Mad_View_Helper_Tag(new Mad_View_Base());
    }

    public function testTag()
    {
        $this->assertEquals('<br />', $this->helper->tag('br'));
        $this->assertEquals('<br clear="left" />', 
                            $this->helper->tag('br', array('clear' => 'left')));
        $this->assertEquals('<br>', 
                            $this->helper->tag('br', null, true));
    }
    
    public function testTagOptions()
    {
        $this->assertRegExp('/\A<p class="(show|elsewhere)" \/>\z/',
                            $this->helper->tag('p', array('class' => 'show',
                                                          'class' => 'elsewhere')));
    }
    
    public function testTagOptionsRejectsNullOption() 
    {
        $this->assertEquals('<p />', 
                            $this->helper->tag('p', array('ignored' => null)));
    }
    
    public function testTagOptionsAcceptsBlankOption()
    {
        $this->assertEquals('<p included="" />',
                            $this->helper->tag('p', array('included' => '')));
    }
    
    public function testTagOptionsConvertsBooleanOption()
    {
        $this->assertEquals('<p disabled="disabled" multiple="multiple" readonly="readonly" />',
                            $this->helper->tag('p', array('disabled' => true,
                                                          'multiple' => true,
                                                          'readonly' => true)));
    }

    public function testContentTag()
    {
        $this->assertEquals('<a href="create">Create</a>',
                            $this->helper->contentTag('a', 'Create', array('href' => 'create')));
    }
    
    public function testCdataSection()
    {
        $this->assertEquals('<![CDATA[<hello world>]]>', $this->helper->cdataSection('<hello world>'));
    }
    
    public function testEscapeOnce()
    {
        $this->assertEquals('1 &lt; 2 &amp; 3', $this->helper->escapeOnce('1 < 2 &amp; 3'));
    }

    public function testDoubleEscapingAttributes()
    {
        $attributes = array('1&amp;2', '1 &lt; 2', '&#8220;test&#8220;');
        foreach ($attributes as $escaped) {
            $this->assertEquals("<a href=\"$escaped\" />",
                                $this->helper->tag('a', array('href' => $escaped)));
        }
    }

    public function testSkipInvalidEscapedAttributes()
    {
        $attributes = array('&1;', '&#1dfa3;', '& #123;');
        foreach ($attributes as $escaped) {
            $this->assertEquals('<a href="' . str_replace('&', '&amp;', $escaped) . '" />',
                                $this->helper->tag('a', array('href' => $escaped)));
        }
    }
}
