<?php
/**
 * @category   Mad
 * @package    Mad_Test
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */

/**
 * Base class for all Unit Test classes.
 *
 * @category   Mad
 * @package    Mad_Test
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
abstract class Mad_Test_Unit extends PHPUnit_Framework_TestCase
{
    /**
     * PHPUnit configuration: Disable the backup and
     * restoration of the $GLOBALS array.
     * @var boolean
     */
    protected $backupGlobals = false;
        
    /**
     * Generic data struct for storing overloaded attributes
     * @var object
     */
    protected $_data = array();
    
    /**
     * Database connection spec
     * @var string
     */
    protected $_spec = 'test';

    /**
     * The fixture loaded for this test
     * @var	Mad_Test_Fixture_Collection
     */
    protected $_fixtures;
    
    /**
     * Names of the class for fixtures that cannot be inferred
     * from the table name (namespaced models)
     * @var array
     */
    protected $_fixtureClassNames;

    /**
     * Fixture records
     * @var array
     */
    protected $_records;

    /**
     * Overloaded methods proxy to our helper class
     *
     * @param   string  $name
     * @param   array   $args
     */
    public function __call($name, $args)
    {
        // check fixture methods
        if (!empty($this->_records[$name])) {
            $record = isset($args[0]) ? $args[0] : null;
            return $this->_records[$name][$record];
        }
        if (!class_exists('MadTestHelper')) {
            throw new Mad_Test_Exception("Call to undefined method '$name'");
        }
        $helper = new MadTestHelper;
        $helper->testInstance = $this;
        if (!method_exists($helper, $name)) {
            throw new Mad_Test_Exception("Call to undefined method '$name'");
        }
        return call_user_func_array(array($helper, $name), $args);
    }


    /*##########################################################################
    # Logger
    ##########################################################################*/

    /**
     * Returns the logger object.
     * 
     * @return  object
     */
    public static function logger()
    {
        return $GLOBALS['MAD_DEFAULT_LOGGER'];
    }


    /*##########################################################################
    # Methods available to Test subclasses
    ##########################################################################*/
    
    /**
     * Set the fixture class name for namespaced fixtures
     * 
     * @param   array   $fixturesNames
     */
    public function setFixtureClass($fixturesNames)
    {
        foreach ($fixturesNames as $name => $class) {
            $this->_fixtureClassNames[$name] = $class;
        }
    }

    /**
     * Load fixture(s) data into the database.
     *
     * <code>
     *  <?php
     *  ...
     *  // single
     *  $this->fixtures('briefcases');
     *
     *  // multiple
     *  $this->fixtures(array('briefcases', 'md_metadata'));
     *
     *  // 'only' for given test methods
     *  $this->fixtures('briefcases', array('only' => array('testMethod1', 'testMethod2')));
     *
     *  // all test methods 'except' given
     *  $this->fixtures('briefcases', array('except' => array('testMethod1', 'testMethod2')));
     *  ...
     *  ?>
     * </code>
     *
     * @param   string|array $ymlFiles
     * @param   array        $options
     */
    public function fixtures($args)
    {
        $ymlFiles = func_get_args();
        $last = end($ymlFiles);
        $options = is_array($last) ? array_pop($ymlFiles) : array();

        // don't load fixtures for these methods
        if (isset($options['except'])) {
            if (in_array($this->getName(), $options['except'])) return;
        }
        // only load fixtures for these methods
        if (isset($options['only'])) {
            if (!in_array($this->getName(), $options['only'])) return;
        }

        // Add fixtures to the existing fixtures when called more than once
        if (empty($this->_fixtures)) {
            $this->_fixtures = new Mad_Test_Fixture_Collection($this->_conn, $ymlFiles);
        } else {
            $this->_fixtures->addFixture($ymlFiles);
        }
        
        // Build models from fixture records
        foreach ($this->_fixtures->getFixtures() as $fixture) {
            $name  = $fixture->getYmlName();
            if (isset($this->_fixtureClassNames[$name])) {
                $class = $this->_fixtureClassNames[$name];
            } else {
                $table = $fixture->getTableName();
                $class = Mad_Support_Inflector::classify($table);
            }
            
            // skip building model if class doesn't exist
            if (!Mad_Support_Base::modelExists($class)) break;
            $model = new $class;

            $this->_records[$name] = array();
            foreach ($fixture->getRecords() as $recordName => $attrs) {
                $this->_records[$name][$recordName] = $model->instantiate($attrs); 
            }
        }

        // @deprecated - assign public properties based on fixture names
        foreach ($this->_fixtures->getRecords() as $recordName => $values) {            
            if (isset($this->$recordName)) {
                $this->$recordName = array_merge($this->$recordName, $values);
            } else {
                $this->$recordName = $values;
            }
            // make all values strings
            foreach ($this->$recordName as &$value) $value = (string) $value;
        }
    }

    /**
     * Use the mock logger so that we can assert against logged messages
     */
    public function useMockLogger()
    {
        $this->mock = new Zend_Log_Writer_Mock;
        $logger = new Zend_Log($this->mock);
        Mad_Model_Base::setLogger($logger);

        // changing logger requires us to reset our test's connection property
        $this->_conn = Mad_Model_Base::connection();
    }

    /**
     * Reinitialize mock to clear out log
     */ 
    protected function clearLog()
    {
        $this->mock->events = array();
    }

    /**
     * assert log contains given substring
     * 
     * @param   string  $substr
     * @param   string  $msg
     */ 
    protected function assertLogged($substr, $msg=null)
    {
        if (!$this->mock) {
            throw new Exception("You must use the mock log to match logging events");
        }
        $matched = false;
        foreach ($this->mock->events as $event) {
            $matched = strstr($event['message'], $substr) ? true : $matched;
        }
        $this->assertTrue($matched);
    }

    /**
     * assert the log does not contain given substring
     * 
     * @param   string  $substr
     * @param   string  $msg
     */ 
    protected function assertNotLogged($substr, $msg=null)
    {
        $matched = false;
        if (!$this->mock) {
            throw new Exception("You must use the mock log to match logging events");
        }
        $matched = false;
        foreach ($this->mock->events as $event) {
            $matched = strstr($event['message'], $substr) ? true : $matched;
        }
        $this->assertFalse($matched);
    }

    /**
     * assert difference of an expression in a simulated block of
     * code
     * 
     * {{code: php
     *  ...
     *  $diff = $this->assertDifference('User::count()');
     *    User::create(array('username' => 'Joe'));
     *  $diff->end();
     *  ...
     * }}
     * 
     * @param   string  $expression
     * @param   integer $difference
     * @param   string  $msg
     */
    protected function assertDifference($expression, $difference = 1, $msg = null)
    {
        return new Mad_Test_DifferenceAssertion($expression, $difference, $msg);
    }

    /**
     * assert difference of an expression in a simulated block of
     * code
     * 
     * {{code: php
     *  ...
     *  $diff = $this->assertNoDifference('User::count()');
     *    User::create(array('username' => ''));
     *  $diff->end();
     *  ...
     * }}
     * 
     * @param   string  $expression
     * @param   string  $msg
     */
    protected function assertNoDifference($expression, $msg = null)
    {
        return new Mad_Test_DifferenceAssertion($expression, 0, $msg);
    }

    /*##########################################################################
    # Override parent methods
    ##########################################################################*/

    /**
     * Make sure we always disconnect after tests
     */
    public function runBare()
    {
        // log test timing
        $test = get_class($this).'::'.$this->getName();
        $t = new Mad_Support_Timer;
        $t->start();
        $this->_logInfo($test, 'START ');

        $this->_connect();
        parent::runBare();
        $this->_disconnect();

        // log test timing
        $elapsed = $t->finish();
        $this->_logInfo($test, 'FINISH', $elapsed);
    }


    /*##########################################################################
    # Protected methods
    ##########################################################################*/

    /**
     * Connect to the database.
     * 
     * @return  object  {@link Mad_Model_ConnectionAdapter_Abstract}
     */
    protected function _connect()
    {
        if (!Mad_Model_Base::isConnected()) {
            Mad_Model_Base::setLogger(); // default logger
            Mad_Model_Base::establishConnection($this->_spec);
        }
        $this->_conn = Mad_Model_Base::connection();
    }

    /**
     * Disconnect from the database.
     */
    protected function _disconnect()
    {
        // run 'teardown' sql from the fixture
        if ($this->_fixtures) {
            $this->_fixtures->teardown();
        }

        if (Mad_Model_Base::isConnected()) {
            Mad_Model_Base::removeConnection();
        }
        $this->_conn = null;
    }

    /**
     * Logs the unit test run for debugging.
     * 
     * @param   string  $test
     * @param   string  $phase
     * @param   float   $runtime
     */
    protected function _logInfo($test, $phase, $runtime=null) 
    {
        $logger = Mad_Test_Unit::logger();
        if (! is_null($logger)) {
            $runtime = (is_null($runtime) ? '' : " ($runtime ms)");
            $logger->debug("***************** $phase $test $runtime");
        }
    }

}