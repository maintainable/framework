<?php
/**
 * @category   Mad
 * @package    Mad_Script
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Command line task runner
 *
 * @category   Mad
 * @package    Mad_Script
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Script_TaskRunner extends Mad_Script_Base
{
    /**
     * @var Mad_Task_Loader
     */
    protected $_loader;

    /**
     * @var Mad_Task_Runner
     */
    protected $_runner;
    
    /**
     * Class Constructor
     *
     * @param array $argv
     */
    public function __construct($argv)
    {
        // load all framework and application tasks
        $this->_loader = new Mad_Task_Loader();
        $this->_loader->loadAll();

        // runner for all tasks
        $this->_runner = new Mad_Task_Runner();
        $this->_runner->addGlobalTaskSets();

        // do command
        if (empty($argv[1])) {
            $this->displayHelp();

        } else if ($argv[1] == '--tasks' || $argv[1] == '-T') {
            $filter = isset($argv[2]) ? $argv[2] : null;
            $this->displayTasks($filter);

        } else if ($argv[1] == '--yaml') {
            $this->dumpYaml();

        } else {
            $this->runTask($argv[1]);
        }
    }

    /*##########################################################################
    # Commands
    ##########################################################################*/

    /** 
     * Display task runner help.
     */
    public function displayHelp()
    {
        $usage = "Usage: ./script/task <taskname>   Run task"             . PHP_EOL
               . "       ./script/task --tasks      List available tasks" . PHP_EOL;
        echo $usage;
    }

    /**
     * Display a table of available tasks.  If $filter is set,
     * only task names beginning with $filter will be shown.
     *
     * @param  string|null  $filter  Filter string
     */
    public function displayTasks($filter = null)
    {
        $tasks = $this->getTasks();

        // filter tasks if optional filter arg is given
        if (isset($filter)) {
            foreach ($tasks as $name => $desc) {
                if (substr($name, 0, strlen($filter)) != $filter) {
                    unset($tasks[$name]);
                }
            }
        }        

        // find longest task name to format column
        $width = 0;
        foreach ($tasks as $name => $desc) { 
            $l = strlen($name); 
            if ($l > $width) { $width = $l; }
        }

        // display table
        foreach ($tasks as $name => $desc) {
            $line = str_pad($name, $width + 2, ' ', STR_PAD_RIGHT)
                  . '# ' . $desc;
            if (strlen($line) > 78) { $line = substr($line, 0, 75) . '...'; }

            echo $line . PHP_EOL;
        }
    }

    /**
     * Undocumented command that dumps all tasks in YAML format.  This is used
     * by the Rake file to allow tasks to optionally be run through Rake.
     */
    public function dumpYaml()
    {
        echo Horde_Yaml::dump($this->getTasks());
    }

    /** 
     * Run a task named $task.
     * 
     * @param  string  $task  Name of task to run.
     */
    public function runTask($task)
    {
        try {
            $name = str_replace(':', '_', $task);
            $this->_runner->run($name);        
        } catch (InvalidArgumentException $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    /*##########################################################################
    # Utility methods
    ##########################################################################*/

    /**
     * Get a sorted list of all available tasks.
     */
    public function getTasks()
    {
        $tasks = array();
        foreach ($this->_runner->getTasks() as $name => $desc) {
            $tasks[ str_replace('_', ':', $name) ] = $desc;
        }
        ksort($tasks);
        return $tasks;
    }
    
}
