<?php
/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config/environment.php';
}

class Mad_View_Helper_ScriptaculousTest_MockUrlHelper extends Mad_View_Helper_Base
{
    public function urlFor($options)
    {
        $url = 'http://www.example.com/';
        if (is_array($options) && isset($options['action'])) { 
            $url .= $options['action']; 
        }
        return $url;
    }
}

/**
 * @group      view
 * @category   Mad
 * @package    Mad_View
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential
 */
class Mad_View_Helper_ScriptaculousTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->view = new Mad_View_Base();
        $this->view->addHelper(new Mad_View_Helper_Tag($this->view));
        $this->view->addHelper(new Mad_View_Helper_ScriptaculousTest_MockUrlHelper($this->view));
        $this->view->addHelper(new Mad_View_Helper_Javascript($this->view));
        $this->view->addHelper(new Mad_View_Helper_Prototype($this->view));
        $this->view->addHelper(new Mad_View_Helper_Scriptaculous($this->view));
    }

    public function testEffect()
    {
        $this->assertEquals(
            'new Effect.Highlight("posts",{});',
            $this->view->visualEffect('highlight', 'posts'));

        $this->assertEquals(
            'new Effect.Fade("fademe",{duration:4.0});',
            $this->view->visualEffect('fade', 'fademe', array('duration' => 4.0)));

        $this->assertEquals(
            'new Effect.Shake(element,{});',
            $this->view->visualEffect('shake'));

        $this->assertEquals(
            'new Effect.DropOut("dropme",{queue:\'end\'});',
            $this->view->visualEffect('dropOut', 'dropme', array('queue' => 'end')));
            
        // @todo remaining tests
    }

    public function testToggleEffects()
    {
        $this->assertEquals(
            'Effect.toggle("posts",\'appear\',{});',
            $this->view->visualEffect('toggleAppear', 'posts'));

        $this->assertEquals(
            'Effect.toggle("posts",\'slide\',{});',
            $this->view->visualEffect('toggleSlide', 'posts'));

        $this->assertEquals(
            'Effect.toggle("posts",\'blind\',{});',
            $this->view->visualEffect('toggleBlind', 'posts'));
    }

    // @todo change "http://www.example.com/order" to array('action' => 'order')
    public function testSortableElement()
    {
        $this->assertEquals(
            "<script type=\"text/javascript\">\n//<![CDATA[\nSortable.create(\"mylist\", {onUpdate:function(){new Ajax.Request('http://www.example.com/order', {asynchronous:true, evalScripts:true, parameters:Sortable.serialize(\"mylist\")})}})\n//]]>\n</script>",
            $this->view->sortableElement('mylist', array('url' => array('action' => 'order'))));

        $this->assertEquals(
            "<script type=\"text/javascript\">\n//<![CDATA[\nSortable.create(\"mylist\", {constraint:'horizontal', onUpdate:function(){new Ajax.Request('http://www.example.com/order', {asynchronous:true, evalScripts:true, parameters:Sortable.serialize(\"mylist\")})}, tag:'div'})\n//]]>\n</script>",
            $this->view->sortableElement('mylist', array('tag' => 'div', 'constraint' => 'horizontal', 'url' => array('action' => 'order'))));

        $this->assertEquals(
            "<script type=\"text/javascript\">\n//<![CDATA[\nSortable.create(\"mylist\", {constraint:'horizontal', containment:['list1','list2'], onUpdate:function(){new Ajax.Request('http://www.example.com/order', {asynchronous:true, evalScripts:true, parameters:Sortable.serialize(\"mylist\")})}})\n//]]>\n</script>",
            $this->view->sortableElement('mylist', array('containment' => array('list1', 'list2'), 'constraint' => 'horizontal', 'url' => array('action' => 'order'))));

        $this->assertEquals(
            "<script type=\"text/javascript\">\n//<![CDATA[\nSortable.create(\"mylist\", {constraint:'horizontal', containment:'list1', onUpdate:function(){new Ajax.Request('http://www.example.com/order', {asynchronous:true, evalScripts:true, parameters:Sortable.serialize(\"mylist\")})}})\n//]]>\n</script>",
            $this->view->sortableElement('mylist', array('containment' => 'list1', 'constraint' => 'horizontal', 'url' => array('action' => 'order'))));
    }
    
    public function testDraggableElement()
    {
        $this->assertEquals(
            "<script type=\"text/javascript\">\n//<![CDATA[\nnew Draggable(\"product_13\", {})\n//]]>\n</script>",
            $this->view->draggableElement('product_13'));
        
        $this->assertEquals(
            "<script type=\"text/javascript\">\n//<![CDATA[\nnew Draggable(\"product_13\", {revert:true})\n//]]>\n</script>",
            $this->view->draggableElement('product_13', array('revert' => true)));
    }
    
    public function testDropReceivingElement()
    {
        $this->assertEquals(
            "<script type=\"text/javascript\">\n//<![CDATA[\nDroppables.add(\"droptarget1\", {onDrop:function(element){new Ajax.Request('http://www.example.com/', {asynchronous:true, evalScripts:true, parameters:'id=' + encodeURIComponent(element.id)})}})\n//]]>\n</script>",
            $this->view->dropReceivingElement('droptarget1'));
            
        $this->assertEquals(
            "<script type=\"text/javascript\">\n//<![CDATA[\nDroppables.add(\"droptarget1\", {accept:'products', onDrop:function(element){new Ajax.Request('http://www.example.com/', {asynchronous:true, evalScripts:true, parameters:'id=' + encodeURIComponent(element.id)})}})\n//]]>\n</script>",
            $this->view->dropReceivingElement('droptarget1', array('accept' => 'products')));
            
        $this->assertEquals(
            "<script type=\"text/javascript\">\n//<![CDATA[\nDroppables.add(\"droptarget1\", {accept:'products', onDrop:function(element){new Ajax.Updater('infobox', 'http://www.example.com/', {asynchronous:true, evalScripts:true, parameters:'id=' + encodeURIComponent(element.id)})}})\n//]]>\n</script>",
            $this->view->dropReceivingElement('droptarget1', array('accept' => 'products', 'update' => 'infobox')));
        
        $this->assertEquals(
            "<script type=\"text/javascript\">\n//<![CDATA[\nDroppables.add(\"droptarget1\", {accept:['tshirts','mugs'], onDrop:function(element){new Ajax.Updater('infobox', 'http://www.example.com/', {asynchronous:true, evalScripts:true, parameters:'id=' + encodeURIComponent(element.id)})}})\n//]]>\n</script>",
            $this->view->dropReceivingElement('droptarget1', array('accept' => array('tshirts', 'mugs'), 'update' => 'infobox')));
    }
}
