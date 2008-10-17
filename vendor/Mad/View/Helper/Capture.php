<?php
/**
 * Capture lets you extract parts of code which can be used
 * in other points of the template or even layout file.
 *
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Capture lets you extract parts of code which can be used
 * in other points of the template or even layout file.
 * 
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_View_Helper_Capture extends Mad_View_Helper_Base
{
    /**
     * Capture allows you to extract a part of the template into an
     * instance variable. You can use this instance variable anywhere
     * in your templates and even in your layout.  Example:
     * 
     *  <? $capture = $this->capture() ?>
     *    Welcome To my shiny new web page!
     *  <? $this->greeting = $capture->end() ?>
     *
     * @return Mad_View_Helper_Capture_Base
     */
    public function capture()
    {
        return new Mad_View_Helper_Capture_Base();
    }
    
    /**
     * Calling contentFor() stores the block of markup for later use.
     * Subsequently, you can retrieve it inside an instance variable
     * that will be named "contentForName" in another template 
     * or in the layout.  Example:
     * 
     *    <? $capture = $this->contentFor("header") %>
     *      <script type="text/javascript"> alert('hello world') </script>
     *    <? $capture->end() %>
     *
     * You can then use $this->contentForHeader anywhere in your templates:
     *
     *    <?= $this->contentForHeader ?>
     *
     * @param string $name  Name of the content that becomes the instance
     *                      variable name. "foo" -> "$this->contentForFoo"
     * @return Mad_View_Helper_Capture_ContentFor
     */    
    public function contentFor($name)
    {
        return new Mad_View_Helper_Capture_ContentFor($name, $this->_view);
    }

}
