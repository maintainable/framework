<?php
/**
 * Mad_View_Base is the "V" in MVC and provides encapsulation for
 * presentation logic.  It allows for templates to be written in 
 * mostly HTML with lightweight embedded PHP helpers.
 *
 * @category   Mad
 * @package    Mad_View
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Mad_View_Base is the "V" in MVC and provides encapsulation for
 * presentation logic.  It allows for templates to be written in 
 * mostly HTML with lightweight embedded PHP helpers.
 *
 * @category   Mad
 * @package    Mad_View
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_View_Base
{
    public static $defaultFormBuilder = 'Mad_View_Helper_Form_Builder';
    
    /**
     * Template path stack
     * @var array
     */
    private $_paths = array();

    /**
     * Stack of helper objects methods with associated objects
     * @var array
     */
    private $_helpers = array();

    /**
     * The controller to delegate component rendering to
     * @var object
     */
    public $controller = null;

    /**
     * Constructor. Sets default paths. If we want to use component rendering,
     * we must have a controller to delegate the request to
     *
     * @param   object  $controller
     */
    public function __construct($controller = null)
    {
        $this->addPath('app/views/');
        $this->controller = $controller;
    }

    /**
     * Accesses a helper object from within a template.
     *
     * @param   string  $name The helper name.
     * @param   array   $args The parameters for the helper.
     * @return  string  The result of the helper output.
     * @throws  Mad_View_Exception
     */
    public function __call($name, $args)
    {
        if (!isset($this->_helpers[$name])) {
            throw new Mad_View_Exception("The helper method \"$name\" does not exist");
        }

        // call the helper method
        return call_user_func_array(array($this->_helpers[$name], $name), $args);
    }

    /**
     * Undefined variables return null.
     *
     * @return null
     */
    public function __get($offset)
    {
        return null;
    }

    /*##########################################################################
    # Public
    ##########################################################################*/

    /**
     * Add to the list of template filepaths in LIFO order.  Relative
     * paths are automatically prepended with MAD_ROOT.
     *
     * <code>
     *  <?php
     *  ...
     *  $view->addPath('app/views/layout/');
     *  $view->addPath('app/views/shared/');
     *  $view->addPath('app/views/FooBar/');
     *  ...
     *  ?>
     * </code>
     *
     * This would look for the template file in the order:
     *  1. {MAD_ROOT}/app/views/FooBar/template.html
     *  2. {MAD_ROOT}/app/views/shared/template.html
     *  3. {MAD_ROOT}/app/views/layout/template.html
     *
     * @see     Mad_Controller_Base::_initViewPaths();
     * @param   string   $path
     * @param   boolean  $relative  Is $path relative to MAD_ROOT?
     */
    public function addPath($path, $relative = true)
    {
        if ($relative) {
            $path = MAD_ROOT .'/'. $path; 
        }

        if (substr($path, -1) != '/') {
            $path .= '/';
        }
        array_unshift($this->_paths, $path);
    }

    /**
     * Get the list of paths to the template files
     * @return  array
     */
    public function getPaths()
    {
        return $this->_paths;
    }

    /**
     * Adds all of the built-in Mad_View_Helpers to this instance
     *
     * @todo We'll come up with a lazy-load strategy in the future.
     */
    public function addBuiltinHelpers()
    {
        $dir = dirname(__FILE__) . '/Helper';
        foreach (new DirectoryIterator($dir) as $f) {
            if ($f->isFile()) {
                $class = "Mad_View_Helper_"
                       . str_replace('.php', '', $f->getFilename());
                if ($class != 'Mad_View_Helper_Base') {
                    $this->addHelper(new $class($this));
                }
            }
        }
    }

    /**
     * Add a helper to this view. This will make all the methods available
     * in the helper class accessible from the view template.
     * Performs reflection to get list of public methods available as helpers.
     * One thing to watch out for is that helper method names do not collide
     *
     * <code>
     *  <?php
     *  ...
     *  $view->addHelper(new HelperObject);
     *  ...
     *  ?>
     *
     *  // now we can call methods defined in HelperObject from our template
     *  <div>
     *  <?= $this->helperObjectMethod($data) ?>
     *  </div>
     *
     * </code>
     *
     * @param   object  $helper
     * @throws  Mad_View_Exception
     */
    public function addHelper(Mad_View_Helper_Base $helpers)
    {
        foreach (get_class_methods($helpers) as $method) {
            if (substr($method, 0, 1) != '_') {
                $this->_helpers[$method] = $helpers;
            }
        }
    }


    /*##########################################################################
    # Rendering Methods
    ##########################################################################*/

    /**
     * Processes a view template and returns the output. Add .html extension
     * as default if no extension has been given.
     *
     * <code>
     *   <?php
     *   ...
     *   $result = $view->render('index');
     *   ...
     *   ?>
     * </code>
     *
     * @param   string  $name   The template name to process.
     * @param   array   $locals
     * @return  string  The template output.
     */
    public function render($name, $locals = array())
    {
        // render partial
        if (is_array($name) && $partial = $name['partial']) {
            unset($name['partial']);
            return $this->renderPartial($partial, $name);
        }
        // append missing html
        if (!strstr($name, '.')) { $name .= '.html'; }

        return $this->_template($name, $locals);
    }

    /**
     * Render a partial template. Partial template filenames are named with
     * a leading underscore, although this underscore is not used when
     * specifying the name of the partial.
     *
     * we would reference the file /views/shared/_sidebarInfo.html
     * in our template using:
     *
     * <code>
     *   <div>
     *   <?= $this->renderPartial('sidebarInfo'); ?>
     *   </div>
     * </code>
     *
     * @param   string  $name
     * @param   array   $options
     * @return  string  The template output
     */
    public function renderPartial($name, $options=array())
    {
        // pop name off of the path
        $parts = strstr($name, '/') ? explode('/', $name) : array($name);
        $name = array_pop($parts);
        $path = implode('/', $parts)."/";

        // check if they passed in a collection before validating keys
        $useCollection = array_key_exists('collection', $options);

        $valid = array('object', 'locals' => array(), 'collection' => array());
        $options = Mad_Support_Base::assertValidKeys($options, $valid);
        $locals = array($name => null);

        // set the object variable
        if ($options['object']) {
            $locals[$name] = $options['object'];
        }

        // set local variables to be used in the partial
        foreach ($options['locals'] as $key => $val) {
            $locals[$key] = $val;
        }

        // collection
        if ($useCollection) {
            $rendered = '';
            if ($options['collection'] instanceof Mad_Model_Collection ||
                $options['collection'] instanceof Mad_Model_PaginatedCollection ||
                is_array($options['collection'])) {

                $sz = count($options['collection']);
                for ($i = 0; $i < $sz; $i++) {
                    $locals["{$name}Counter"] = $i;
                    $locals[$name] = $options['collection'][$i];
                    $rendered .= $this->render("{$path}_{$name}", $locals);
                }
            }

        // single render
        } else {
            $rendered = $this->render("{$path}_{$name}", $locals);
        }
        return $rendered;
    }


    /*##########################################################################
    # Private Methods
    ##########################################################################*/

    /**
     * Finds a view template from the available directories.
     *
     * @param   string  $name   The base name of the template.
     * @param   array   $locals The array of local variables to make available
     * @throws  Mad_View_Exception
     */
    private function _template($name, $locals)
    {
        // set local variables
        foreach ($locals as $key => $value) {
            ${$key} = $value;
        }

        foreach ($this->_paths as $dir) {
            $path = $dir.$name;
            if (is_readable($path)) {
                ob_start();
                include "madview://$path";
                $buffer = ob_get_clean();
                return $buffer;
            }
        }
        throw new Mad_View_Exception("template '$name' not found");
    }

}
