<?php
/**
 * @category   Mad
 * @package    Mad_Madness
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Configuration Options
 *  controller: 
 *  mailer: 
 *    deliveryMethod - The way in which to send e-mail 
 *                     sendmail || test
 *  view: 
 *  model: 
 *  test: 
 *  
 *  cacheModels
 * 
 * 
 * @category   Mad
 * @package    Mad_Madness
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Madness_Configuration
{
    // singleton instance
    protected static $_instance = null;

    // options for diff components
    public $controller = array();
    public $mailer     = array();
    public $view       = array();
    public $model      = array();
    public $test       = array();

    /**
     * Singleton - don't instantiate directly
     * @see Mad_Madness_Configuration::getInstance
     */
    protected function __construct() 
    {
        // cast arrays into objects for nicer api access
        $this->controller = (object)$this->controller;
        $this->mailer     = (object)$this->mailer;
        $this->view       = (object)$this->view;
        $this->model      = (object)$this->model;
        $this->test       = (object)$this->test;
    }

    /**
     * Singleton getInstance method
     *
     * @return  Mad_Controller_Dispatcher
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Finish up config by connecting to db
     */
    public function end()
    {
        // set config for each component
        foreach ((array)$this->controller as $key => $value) {
            Mad_Controller_Base::${$key} = $value;
        }
        foreach ((array)$this->mailer as $key => $value) {
            Mad_Mailer_Base::${$key} = $value;
        }
        foreach ((array)$this->view as $key => $value) {
            Mad_View_Base::${$key} = $value;
        }
        foreach ((array)$this->model as $key => $value) {
            Mad_Model_Base::${$key} = $value;
        }
        foreach ((array)$this->test as $key => $value) {
            Mad_Test_Base::${$key} = $value;
        }

        // database connection
        Mad_Model_Base::establishConnection(MAD_ENV);
    }
}