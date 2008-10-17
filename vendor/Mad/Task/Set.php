<?php
/**
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Set of tasks run by Mad_Test_Runner.  Extend this class with your
 * own tasks as public methods.  See the built-in sets for examples.
 *
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
abstract class Mad_Task_Set
{
    /**
     * @var Mad_Task_Runner
     */
    protected $_runner;

    /**
     * Constructor
     *
     * @param  $runner  Mad_Task_Runner
     */
    public function __construct($runner)
    {
        $this->_runner = $runner;
    }

    /**
     * Undefined methods are delegated to the task runner.  This
     * allows tasks to invoke other tasks.
     */
    public function __call($method, $args)
    {
        $callback = array($this->method, $args);
        return call_user_func_array($callback, $args);
    }

    /**
     * Return an array of task definitions for this set of tasks
     * in the form of array($name => $description)
     *
     * @return array
     */
    public function getTasks()
    {
        $tasks = array();
        $builtins = get_class_methods('Mad_Task_Set');

        $reflector = new ReflectionClass($this);
        foreach ($reflector->getMethods() as $method) {
            if (in_array($method->name, $builtins)) { continue; }

            $desc = $method->getDocComment();
            if ($desc !== false) {
                $desc = trim(str_replace(array('/*', '*/', '*'), '', $desc));
            }
            $tasks[$method->name] = $desc;
        }

        return $tasks;
    }
    
}
