<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Migration
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Migration
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Migration_Base
{
    /**
     * Print messages as migrations happen
     * @var boolean
     */
    public static $verbose = true;

    /**
     * The migration version
     * @var integer
     */
    public $version = null;

    
    public function __contruct() {}

    /**
     * Proxy methods over to the connection
     * @param   string  $method
     * @param   array   $args
     */
    public function __call($method, $args)
    {
        foreach ($args as $arg) {
            if (is_array($arg)) {
                $vals = array();
                foreach ($arg as $key => $value) {
                    $vals[] = "$key => ".var_export($value, true);
                }
                $a[] = 'array('.join(', ', $vals).')';
            } else {
                $a[] = $arg;
            }
        }
        $this->say("$method(".join(", ", $a).")");

        // benchmark method call
        $t = new Horde_Support_Timer();
        $t->push();
            $connection = Mad_Model_Base::connection();
            $result = call_user_func_array(array($connection, $method), $args);
        $time = $t->pop();

        // print stats
        $this->say(sprintf("%.4fs", $time), 'subitem');
        if (is_int($result)) { $this->say("$result rows", 'subitem'); }

        return $result;
    }


    /*##########################################################################
    # Public
    ##########################################################################*/

    public function upWithBechmarks()
    {
        $this->migrate('up');
    }

    public function downWithBenchmarks()
    {
        $this->migrate('down');
    }
    
    /**
     * Execute this migration in the named direction
     */
    public function migrate($direction)
    {
        if (!method_exists($this, $direction)) { return;  }

        if ($direction == 'up')   { $this->announce("migrating"); }
        if ($direction == 'down') { $this->announce("reverting"); }

        $result = null;
        $t = new Horde_Support_Timer;
        $t->push();
            $result = $this->$direction();
        $time = $t->pop();

        if ($direction == 'up')   { 
            $this->announce("migrated (".sprintf("%.4fs", $time).")"); 
            $this->write();
        }
        if ($direction == 'down') { 
            $this->announce("reverted (".sprintf("%.4fs", $time).")"); 
            $this->write();
        }
        return $result;
    }

    /**
     * @param   string  $text
     */
    public function write($text='')
    {
        if (self::$verbose) print "$text\n";
    }

    /**
     * Announce migration
     * @param   string  $message
     */
    public function announce($message)
    {
        $text = "$this->version ".get_class($this).": $message";
        $length = 75-strlen($text) > 0 ? 75-strlen($text) : 0;

        $this->write(sprintf("== %s %s", $text, str_repeat('=', $length)));
    }

    /**
     * @param   string  $message
     * @param   boolean $subitem
     */
    public function say($message, $subitem=false)
    {
        $this->write(($subitem ? "   ->" : "--"). " $message");
    }
}