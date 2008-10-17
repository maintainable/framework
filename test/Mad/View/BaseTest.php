<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config/environment.php';
}

/**
 * @group      view
 * @category   Mad
 * @package    Mad_View
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_View_BaseTest extends Mad_Test_Unit
{
    protected $_view = null;

    public function setUp()
    {
        $this->_view = new Mad_View_Base();
        $this->_view->addPath('test/Mad/View/views/');
    }

    /*##########################################################################
    # Assignment
    ##########################################################################*/

    // test setting/getting dynamic properties
    public function testSet()
    {
        $this->_view->publicVar = 'test';
        $this->assertEquals('test', $this->_view->publicVar);
    }

    // test accessing variable
    public function testAccessVar()
    {
        $this->_view->testVar = 'test';
        $this->assertTrue(!empty($this->_view->testVar));

        $this->_view->testVar2 = '';
        $this->assertTrue(empty($this->_view->testVar2));

        $this->assertTrue(isset($this->_view->testVar2));
        $this->assertTrue(!isset($this->_view->testVar3));
    }

    // test adding a template path
    public function testAddTemplatePath()
    {
        $this->_view->addPath('app/views/shared/');
        
        $expected = array(MAD_ROOT . '/app/views/shared/',
                          MAD_ROOT . '/test/Mad/View/views/',
                          MAD_ROOT . '/app/views/');
        $this->assertEquals($expected, $this->_view->getPaths());
    }

    // test adding a template path
    public function testAddTemplatePathAddSlash()
    {
        $this->_view->addPath('app/views/shared');
        $expected = array(MAD_ROOT . '/app/views/shared/',
                          MAD_ROOT . '/test/Mad/View/views/',
                          MAD_ROOT . '/app/views/');
        $this->assertEquals($expected, $this->_view->getPaths());
    }


    /*##########################################################################
    # Rendering
    ##########################################################################*/

    // test rendering
    public function testRender()
    {
        $this->_view->myVar = 'test';

        $expected = "<div>test</div>";
        $this->assertEquals($expected, $this->_view->render('testRender.html'));
    }

    // test rendering
    public function testRenderNoExtension()
    {
        $this->_view->myVar = 'test';

        $expected = "<div>test</div>";
        $this->assertEquals($expected, $this->_view->render('testRender'));
    }

    // test that the
    public function testRenderPathOrder()
    {
        $this->_view->myVar = 'test';

        // we should be rendering the testRender.html in Views/views/
        $expected = "<div>test</div>";
        $this->assertEquals($expected, $this->_view->render('testRender'));

        // after we specify the 'subdir' path, it should read from subdir path first
        $this->_view->addPath('test/Mad/View/views/subdir/');
        $expected = "<div>subdir test</div>";
        $this->assertEquals($expected, $this->_view->render('testRender'));
    }


    /*##########################################################################
    # Partials
    ##########################################################################*/

    // test rendering partial
    public function testRenderPartial()
    {
        $this->_view->myVar1 = 'main';
        $this->_view->myVar2 = 'partial';

        $expected = '<div>main<p>partial</p></div>';
        $this->assertEquals($expected, $this->_view->render('testPartial'));
    }

    // test rendering partial with object passed in
    public function testRenderPartialObject()
    {
        $this->_view->myObject = (object)array('string_value' => 'hello world');
        $expected = '<div><p>hello world</p></div>';
        $this->assertEquals($expected, $this->_view->render('testPartialObject'));
    }

    // test rendering partial with locals passed in
    public function testRenderPartialLocals()
    {
        $expected = '<div><p>hello world</p></div>';
        $this->assertEquals($expected, $this->_view->render('testPartialLocals'));
    }

    // test rendering partial with collection passed in
    public function testRenderPartialCollection()
    {
        $this->_view->myObjects = array((object)array('string_value' => 'hello'),
                                        (object)array('string_value' => 'world'));
        $expected = '<div><p>hello</p><p>world</p></div>';
        $this->assertEquals($expected, $this->_view->render('testPartialCollection'));
    }

    // test rendering partial with empty set as collection
    public function testRenderPartialCollectionEmpty()
    {
        $this->_view->myObjects = null;

        $expected = '<div></div>';
        $this->assertEquals($expected, $this->_view->render('testPartialCollection'));
    }

    // test rendering partial with empty array as collection
    public function testRenderPartialCollectionEmptyArray()
    {
        $this->_view->myObjects = array();

        $expected = '<div></div>';
        $this->assertEquals($expected, $this->_view->render('testPartialCollection'));
    }

    // partial collection is a model collection
    public function testRenderPartialModelCollection()
    {
        $this->fixtures('unit_tests');
        $this->_view->myObjects = UnitTest::find('all', array('limit' => 2));

        $expected = '<div><p>name a</p><p>name b</p></div>';
        $this->assertEquals($expected, $this->_view->render('testPartialCollection'));
    }

    // test that our stream wrapper works for our custom syntax
    public function testViewStream()
    {
        $this->_view->quoted = 'my "quoted" text';

        $expected = "<div>my &quot;quoted&quot; text</div>\n".
                    "<div>my \"quoted\" text</div>\n".
                    "<div>&quot;test var&quot;</div>";
        $this->assertEquals($expected, $this->_view->render('testViewStream'));
    }
    
    public function testViewStreamBrackets()
    {
        $expected = "<input name=\"user[id]\" />\nfoo:bar";
        $this->assertEquals($expected, $this->_view->render('testBrackets'));
    }


    /*##########################################################################
    # Escape output
    ##########################################################################*/

    public function testEscapeTemplate()
    {
        $this->_view->myVar = '"escaping"';
        $this->_view->addHelper(new Mad_View_Helper_Text($this->_view));

        $expected = "<div>test &quot;escaping&quot; quotes</div>";
        $this->assertEquals($expected, $this->_view->render('testEscape'));
    }

    // test adding a helper
    public function testAddMad_View_Helper_Text()
    {
        $str = 'The quick brown fox jumps over the lazy dog tomorrow morning.';

        // helper doesn't exist
        try {
            $this->_view->truncateMiddle($str, 40);
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof Mad_View_Exception);

        // add text helper
        $this->_view->addHelper(new Mad_View_Helper_Text($this->_view));
        $expected = 'The quick brown fox... tomorrow morning.';
        $this->assertEquals($expected, $this->_view->truncateMiddle($str, 40));
    }

    // test adding a helper where methods conflict
    public function testAddMad_View_Helper_TextMethodOverwrite()
    {
        // add text helper
        $this->_view->addHelper(new Mad_View_Helper_Text($this->_view));

        // sucessfull when trying to add it again
        $this->_view->addHelper(new Mad_View_Helper_Text($this->_view));
    }

}
