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
class Mad_View_Helper_ModelTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->view = new Mad_View_Base();
        $this->view->addHelper(new Mad_View_Helper_Text($this->view));
        $this->view->addHelper(new Mad_View_Helper_Tag($this->view));
        $this->view->addHelper(new Mad_View_Helper_Model($this->view));

        $this->setupPost();
        $this->setupUser();
    }

    public function setupPost()
    {
        $post = new Mad_View_Helper_ModelTest_MockModel();
        $post->errors->add('authorName', "can't be empty");

        $this->view->post = $post;
    }

    public function setupUser()
    {
        $user = new Mad_View_Helper_ModelTest_MockModel();
        $user->errors->add('email', "can't be empty");

        $this->view->user = $user;
    }


    public function testErrorMessageOnHandlesNull()
    {
        $this->assertEquals('', $this->view->errorMessageOn('notthere', 'notthere'));
    }

    public function testErrorMessageOn()
    {
        $errorMsg = $this->view->errorMessageOn('post', 'authorName');
        $this->assertTrue(! empty($errorMsg));
    }

    public function testErrorMessagesForHandlesNull()
    {
        $this->assertEquals('', $this->view->errorMessagesFor('notthere'));
    }
    
    public function testErrorMessagesFor()
    {
        $this->assertEquals(
            '<div class="errorExplanation" id="errorExplanation"><h2>1 error prohibited this post from being saved</h2><p>There were problems with the following fields:</p><ul><li>Author name can\'t be empty</li></ul></div>',
            $this->view->errorMessagesFor('post'));
        $this->assertEquals(
            '<div class="errorDeathByClass" id="errorDeathById"><h1>1 error prohibited this post from being saved</h1><p>There were problems with the following fields:</p><ul><li>Author name can\'t be empty</li></ul></div>',
            $this->view->errorMessagesFor('post', array('class' => "errorDeathByClass", 'id' => "errorDeathById", 'headerTag' => "h1")));
        $this->assertEquals(
            '<div id="errorDeathById"><h1>1 error prohibited this post from being saved</h1><p>There were problems with the following fields:</p><ul><li>Author name can\'t be empty</li></ul></div>',
            $this->view->errorMessagesFor('post', array('class' => null, 'id' => "errorDeathById", 'headerTag' => "h1")));
        $this->assertEquals(
            '<div class="errorDeathByClass"><h1>1 error prohibited this post from being saved</h1><p>There were problems with the following fields:</p><ul><li>Author name can\'t be empty</li></ul></div>', 
            $this->view->errorMessagesFor('post', array('class' => "errorDeathByClass", 'id' => null, 'headerTag' => "h1")));
        
    }
}

// Mock Object

class Mad_View_Helper_ModelTest_MockModel
{
    public function __construct()
    {
        $this->errors = new Mad_Model_Errors($this);
    }
    
    public function humanAttributeName($attr)
    {
        $attr = Mad_Support_Inflector::underscore($attr);
        return ucfirst(str_replace('_', ' ', $attr));
    }    
}