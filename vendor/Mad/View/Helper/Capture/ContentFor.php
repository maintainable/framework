<?php
/**
 * An instance of this class is returned by
 * Mad_View_Helper_Capture::contentFor().
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * @category   Mad
 * @package    Mad_View
 * @subpackage Helper
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_View_Helper_Capture_ContentFor extends Mad_View_Helper_Capture_Base
{
    /**
     * Name that will become "$this->contentForName"
     *
     * @var string
     */
    private $_name;

    /**
     * Start capturing content that will be stored as
     * $view->contentForName.
     *
     * @param string $name  Name of the content that becomes the instance
     *                      variable name. "foo" -> "$this->contentForFoo"
     * @param Mad_View_Base $view
     */
    public function __construct($name, $view)
    {
        $this->_name = $name;
        $this->_view = $view;
        parent::__construct();
    }
    
    /**
     * Stop capturing content and store it in the view.
     */
    public function end()
    {
        $name = 'contentFor' . ucfirst($this->_name);
        $this->_view->$name = parent::end();
    }

}
