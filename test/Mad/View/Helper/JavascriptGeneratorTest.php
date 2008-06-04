<?php
/**
 * @category   Mad
 * @package    Mad_View
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

class Mad_View_Helper_JavascriptGeneratorTest_MockUrlHelper extends Mad_View_Helper_Base
{
    public function urlFor($options)
    {
        return is_string($options) ? $options : 'http://www.example.com/';
    }
}

/**
 * @group      view
 * @category   Mad
 * @package    Mad_View
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_View_Helper_JavascriptGeneratorTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->view = new Mad_View_Base();
        $this->view->addHelper(new Mad_View_Helper_JavascriptGeneratorTest_MockUrlHelper($this->view));
        $this->view->addHelper(new Mad_View_Helper_Javascript($this->view));
        $this->view->addHelper(new Mad_View_Helper_Prototype($this->view));
        $this->view->addHelper(new Mad_View_Helper_Scriptaculous($this->view));
        $this->generator = new Mad_View_Helper_Prototype_JavaScriptGenerator($this->view);
    }

    public function testInsertHtmlWithString()
    {
        $this->assertEquals(
            'new Insertion.Top("element", "<p>This is a test</p>");',
            $this->generator->insertHtml('top', 'element', '<p>This is a test</p>'));
        $this->assertEquals(
            'new Insertion.Bottom("element", "<p>This is a test</p>");',
            $this->generator->insertHtml('bottom', 'element', '<p>This is a test</p>'));
        $this->assertEquals(
            'new Insertion.Before("element", "<p>This is a test</p>");',
            $this->generator->insertHtml('before', 'element', '<p>This is a test</p>'));
        $this->assertEquals(
            'new Insertion.After("element", "<p>This is a test</p>");',
            $this->generator->insertHtml('after', 'element', '<p>This is a test</p>'));
    }

    public function testReplaceHtmlWithString()
    {
        $this->assertEquals(
            'Element.update("element", "<p>This is a test</p>");',
            $this->generator->replaceHtml('element', '<p>This is a test</p>'));
    }

    public function testReplaceElementWithString()
    {
        $this->assertEquals(
            'Element.replace("element", "<div id=\"element\"><p>This is a test</p></div>");',
            $this->generator->replace('element', '<div id="element"><p>This is a test</p></div>'));
    }

    public function testRemove()
    {
        $this->assertEquals(
            'Element.remove("foo");',
            $this->generator->remove('foo'));
        $this->assertEquals(
            '["foo","bar","baz"].each(Element.remove);',
            $this->generator->remove('foo', 'bar', 'baz'));
    }

    public function testShow()
    {
        $this->assertEquals(
            'Element.show("foo");',
            $this->generator->show('foo'));
        $this->assertEquals(
            '["foo","bar","baz"].each(Element.show);',
            $this->generator->show('foo', 'bar', 'baz'));
    }

    public function testHide()
    {
        $this->assertEquals(
            'Element.hide("foo");',
            $this->generator->hide('foo'));
        $this->assertEquals(
            '["foo","bar","baz"].each(Element.hide);',
            $this->generator->hide('foo', 'bar', 'baz'));
    }

    public function testToggle()
    {
        $this->assertEquals(
            'Element.toggle("foo");',
            $this->generator->toggle('foo'));
        $this->assertEquals(
            '["foo","bar","baz"].each(Element.toggle);',
            $this->generator->toggle('foo', 'bar', 'baz'));
    }

    public function testAlert()
    {
        $this->assertEquals(
            'alert("hello");',
            $this->generator->alert('hello'));
    }

    /** @todo redirectTo() should get an array, not hardcoded url */
    public function testRedirectTo()
    {
        $this->assertEquals(
            'window.location.href = "http://www.example.com/welcome";',
            $this->generator->redirectTo("http://www.example.com/welcome"));
    }
// 
//     // @todo implementation... no closures in php
//     public function testDelay()
//     {
//         return $this->markTestSkipped();
// 
//         // @generator.delay(20) do
//         //   @generator.hide('foo')
//         // assert_equal "setTimeout(function() {\n;\nElement.hide(\"foo\");\n}, 20000);", @generator.to_s
//     }
// 
    public function test__toString()
    {
        $this->generator->insertHtml('top', 'element', '<p>This is a test</p>');
        $this->generator->insertHtml('bottom', 'element', '<p>This is a test</p>');
        $this->generator->remove('foo', 'bar');
        $this->generator->replaceHtml('baz', '<p>This is a test</p>');

        $this->assertEquals(trim('
new Insertion.Top("element", "<p>This is a test</p>");
new Insertion.Bottom("element", "<p>This is a test</p>");
["foo","bar"].each(Element.remove);
Element.update("baz", "<p>This is a test</p>");
'),     $this->generator->__toString());
    }
 
    // public function testElementAccess()
    // {
    //     $this->generator['hello'];
    //     $this->assertEquals('$("hello");', $this->generator->__toString());
    // }

    public function testElementProxyOneDeep()
    {
        $this->generator['hello']->hide();        
        $this->assertEquals('$("hello").hide();', 
                            $this->generator->__toString());
    }
    
//     public function testElementProxyVariableAccess()
//     {
//         return $this->markTestSkipped();
// 
//         $this->generator['hello']['style'];
//         $this->assertEquals('$("hello").style;', 
//                             $this->generator->__toString());
//     }
// 
//     public function testElementProxyVariableAccessWithAssignment()
//     {
//         return $this->markTestSkipped();
// 
//         $this->generator['hello']['style']['color'] = 'red';
//         $this->assertEquals('$("hello").style.color = "red";',
//                             $this->generator->__toString());
//     }
// 
//     public function testElementProxyAssignment()
//     {
//         return $this->markTestSkipped();
// 
//         $this->generator['hello']->width = 400;
//         $this->assertEquals('$("hello").width = 400;',
//                             $this->generator->__toString());
//     }
// 
//     public function testElementProxyTwoDeep()
//     {
//         return $this->markTestSkipped();
// 
//         $this->generator['hello']->hide('first')->cleanWhitespace();
//         $this->assertEquals(
//             '$("hello").hide("first").cleanWhitespace();',
//             $this->generator->__toString());
//     }
// 
//     public function testSelectAccess()
//     {
//         return $this->markTestSkipped();
// 
//         $this->assertEquals(
//             '$$("div.hello");', 
//             $this->generator->select('div.hello'));
//     }
// 
//     public function testSelectProxyOneDeep()
//     {
//         return $this->markTestSkipped();
// 
//         $this->generator->select('p.welcome b')->first()->hide();
//         $this->assertEquals(
//             '$$("p.welcome b").first().hide();',
//             $this->generator->__toString());
//     }
// 
    public function testVisualEffect()
    {
        $this->assertEquals(
            'new Effect.Puff("blah",{});',
            $this->generator->visualEffect('puff', 'blah'));
    }

    public function testVisualEffectToggle()
    {
        $this->assertEquals(
            'Effect.toggle("blah",\'appear\',{});',
            $this->generator->visualEffect('toggleAppear', 'blah'));
    }

    /** @todo url should use array('action' => 'order') */
    public function testSortable()
    {
        $this->assertEquals(
            'Sortable.create("blah", {onUpdate:function(){new Ajax.Request(\'http://www.example.com/order\', {asynchronous:true, evalScripts:true, parameters:Sortable.serialize("blah")})}});', 
            $this->generator->sortable('blah', array('url' => 'http://www.example.com/order')));
    }
    
    public function testDraggable()
    {
        $this->assertEquals(
            'new Draggable("blah", {});',
            $this->generator->draggable('blah'));
    }

    /** @todo url should use array('action' => 'order') */
    public function testDropReceiving()
    {
        $this->assertEquals(
            'Droppables.add("blah", {onDrop:function(element){new Ajax.Request(\'http://www.example.com/order\', {asynchronous:true, evalScripts:true, parameters:\'id=\' + encodeURIComponent(element.id)})}});',
            $this->generator->dropReceiving('blah', array('url' => 'http://www.example.com/order')));
    }
// 
//     public function testCollectionFirstAndLast()
//     {
//         return $this->markTestSkipped();
// 
//         $this->generator->select('p.welcome b')->first()->hide();
//         $this->generator->select('p.welcome b')->last()->show();
//         
//         $this->assertEquals(trim("
// $$(\"p.welcome b\").first().hide();
// $$(\"p.welcome b\").last().show();
// "), $this->generator->__toString());
//     }
//     
//     public function testCollectionProxyWithPluck()
//     {
//         return $this->markTestSkipped();
// 
//         $this->generator->select('p')->pluck('a', 'className');
//         $this->assertEquals(
//             'var a = $$("p").pluck("className");',
//             $this->generator->__toString());
//     }
// 
// 
//     public function testClassProxy()
//     {
//         return $this->markTestSkipped();
// 
//         $this->generator->form()->focus('my_field');
//         $this->assertEquals(
//             'Form.focus("my_field");',
//             $this->generator->__toString());
//     }
//  
//     public function testLiteral()
//     {
//         return $this->markTestSkipped();
// 
//         $literal = $this->generator->literal('function() {}');
//         $this->assertEquals('function() {}', $this->jsonEncode($literal));
//         $this->assertEquals('', $this->generator->__toString());
//     }

}