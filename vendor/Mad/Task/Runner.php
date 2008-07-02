<?php
/**
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/** 
 * Ensure class is loaded before use by ReflectionClass.
 */
require_once 'Mad/Task/Set.php';

/**
 * Runs sets of tasks defined in Mad_Task_Set objects.
 *
 * @category   Mad
 * @package    Mad_Task
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Task_Runner
{
    /**
     * array<Mad_Task_Set>
     */
    protected $_taskSets = array();

    /**
     * Add a task set to this runner.
     */
    public function addTaskSet($taskSet)
    {
        $this->_taskSets[] = $taskSet;
    }

    /**
     * Search the global space for classes extending Mad_Task_Set
     * and add all of them to this runner.
     */
    public function addGlobalTaskSets()
    {
        foreach (get_declared_classes() as $class) {
            $r = new ReflectionClass($class);
            if ($r->isSubclassOf('Mad_Task_Set')) {
                $this->addTaskSet(new $class($this));
            }
        }
    }

    /**
     * Run a task by name
     *
     * @param  string  $taskName  Name of task to invoke
     * @return mixed              Return value of task
     */
    public function run($taskName)
    {
        foreach ($this->_taskSets as $set) {
            foreach ($set->getTasks() as $name => $desc) {
                if ($name == $taskName) { return $set->$name(); }
            }
        }
        throw new InvalidArgumentException("Task $taskName not found");
    }

    /**
     * Get all available tasks from all sets.
     */ 
    public function getTasks()
    {
        $tasks = array();
        foreach ($this->_taskSets as $set) {
            foreach ($set->getTasks() as $name => $desc) {            
                if (! isset($tasks[$name])) { $tasks[$name] = $desc; }
            }
        }
        return $tasks;
    }

}
