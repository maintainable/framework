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

class Mad_View_Helper_FormTagTest_MockUrlHelper extends Mad_View_Helper_Base
{
    public function urlFor($options) 
    {
        return 'http://www.example.com';
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
class Mad_View_Helper_FormTagTest extends Mad_Test_Functional
{
    public function setUp()
    {
        $this->view = new Mad_View_Base();
        $this->view->addHelper(new Mad_View_Helper_FormTag($this->view));
        $this->view->addHelper(new Mad_View_Helper_Tag($this->view));
        $this->view->addHelper(new Mad_View_Helper_FormTagTest_MockUrlHelper($this->view));
    }
    
    public function testFormTag()
    {
        $actual   = $this->view->formTag();
        $expected = '<form action="http://www.example.com" method="post">';
        $this->assertEquals($expected, $actual);
    }

    public function testFormTagMultipart()
    {
        $actual   = $this->view->formTag(array(), array('multipart' => true));
        $expected = '<form action="http://www.example.com" enctype="multipart/form-data" method="post">';
        $this->assertEquals($expected, $actual);
    }

    public function testFormTagWithMethod()
    {
        $actual   = $this->view->formTag(array(), array('method' => 'put'));
        $expected = '<form action="http://www.example.com" method="post"><div style="margin:0;padding:0"><input name="_method" type="hidden" value="put" /></div>';
        $this->assertEquals($expected, $actual);
    }

    public function testCheckBoxTag()
    {
        $actual   = $this->view->checkBoxTag('admin');
        $expected = '<input id="admin" name="admin" type="checkbox" value="1" />';
        $this->assertDomEquals($expected, $actual);
    }
    
    public function testHiddenFieldTag()
    {
        $actual   = $this->view->hiddenFieldTag('id', 3);
        $expected = '<input id="id" name="id" type="hidden" value="3" />';
        $this->assertDomEquals($expected, $actual);
    }

    public function testFileFieldTag()
    {
        $actual   = $this->view->fileFieldTag('id');
        $expected = '<input id="id" name="id" type="file" />';
        $this->assertDomEquals($expected, $actual);
    }

    public function testPasswordFieldTag()
    {
        $actual   = $this->view->passwordFieldTag();
        $expected = '<input id="password" name="password" type="password" />';
        $this->assertDomEquals($expected, $actual);
    }
    
    public function testRadioButtonTag()
    {
        $actual   = $this->view->radioButtonTag('people', 'david');
        $expected = '<input id="people_david" name="people" type="radio" value="david" />';
        $this->assertDomEquals($expected, $actual);
        
        $actual   = $this->view->radioButtonTag('num_people', 5);
        $expected = '<input id="num_people_5" name="num_people" type="radio" value="5" />';
        $this->assertDomEquals($expected, $actual);

        $actual   = $this->view->radioButtonTag('gender', 'm') 
                  . $this->view->radioButtonTag('gender', 'f');
        $expected = '<input id="gender_m" name="gender" type="radio" value="m" />'
                  . '<input id="gender_f" name="gender" type="radio" value="f" />';
        $this->assertEquals($expected, $actual); // @todo assertDomEquals

        $actual   = $this->view->radioButtonTag('opinion', '-1')
                  . $this->view->radioButtonTag('opinion', '1');
        $expected = '<input id="opinion_-1" name="opinion" type="radio" value="-1" />'          
                  . '<input id="opinion_1" name="opinion" type="radio" value="1" />';
        $this->assertEquals($expected, $actual); // @todo assertDomEquals
    }
    
    public function testSelectTag()
    {
        $actual   = $this->view->selectTag('people', '<option>david</option>');
        $expected = '<select id="people" name="people"><option>david</option></select>';
        $this->assertDomEquals($expected, $actual);
    }

    public function testTextAreaTagSizeString()
    {
        $actual   = $this->view->textAreaTag('body', 'hello world', array('size' => '20x40'));
        $expected = '<textarea cols="20" id="body" name="body" rows="40">hello world</textarea>';
        $this->assertDomEquals($expected, $actual);
    }
    
    public function testTextAreaTagShouldDisregardSizeIfGivenAsAnInteger()
    {
        $actual   = $this->view->textAreaTag('body', 'hello world', array('size' => 20));
        $expected = '<textarea id="body" name="body">hello world</textarea>';
        $this->assertDomEquals($expected, $actual);
    }
    public function testTextFieldTag()
    {
        $actual   = $this->view->textFieldTag('title', 'Hello!');
        $expected = '<input id="title" name="title" type="text" value="Hello!" />';
        $this->assertDomEquals($expected, $actual);
    }
    
    public function testTextFieldTagClassString()
    {
        $actual   = $this->view->textFieldTag('title', 'Hello!', array('class' => 'admin'));
        $expected = '<input class="admin" id="title" name="title" type="text" value="Hello!" />';
        $this->assertDomEquals($expected, $actual);
    }

    public function testBooleanOptions()
    {
        $this->assertDomEquals('<input checked="checked" disabled="disabled" id="admin" name="admin" readonly="readonly" type="checkbox" value="1" />',
                               $this->view->checkBoxTag("admin", 1, true, array('disabled' => true, 'readonly' => "yes")));
        
        $this->assertDomEquals('<input checked="checked" id="admin" name="admin" type="checkbox" value="1" />',
                               $this->view->checkBoxTag('admin', 1, true, array('disabled' => false, 'readonly' => null)));

        $this->assertDomEquals('<select id="people" multiple="multiple" name="people"><option>david</option></select>',
                               $this->view->selectTag('people', '<option>david</option>', array('multiple' => true)));

        $this->assertDomEquals('<select id="people" name="people"><option>david</option></select>',
                               $this->view->selectTag('people', '<option>david</option>', array('multiple' => null)));
    }
    
    public function testSubmitTag()
    {
        $expected = '<input name="commit" onclick="this.setAttribute(\'originalValue\', this.value);this.disabled=true;this.value=\'Saving...\';alert(\'hello!\');result = (this.form.onsubmit ? (this.form.onsubmit() ? this.form.submit() : false) : this.form.submit());if (result == false) { this.value = this.getAttribute(\'originalValue\'); this.disabled = false };return result" type="submit" value="Save" />';
        $actual   = $this->view->submitTag('Save', array('disableWith' => 'Saving...', 'onclick' => "alert('hello!')"));
        $this->assertDomEquals($expected, $actual);
    }

}
        