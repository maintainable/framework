<?php
/**
 * @category   Mad
 * @package    Mad_Test
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * A fixture is the maintained 'state' that a test starts out in. We maintain
 * this state using YAML files. The YAML is a simple text representation of the data
 * needed in the database to execute a test. Unit Tests have the option of loading
 * this sample(fixture) data into the database. Mad_Test_Fixture_Base is a 
 * representation of that data, and helps load/clear the data the database by 
 * parsing the YAML data.
 *
 * Most tables in the database use foreign keys which require the tables to
 * be populated in a specific order. The static methods in this class help maintain
 * which tables have been loaded/cleared for any given test to make sure that we
 * load and delete from all the tables in the correct order to honor the foreign
 * keys.
 *
 * @category   Mad
 * @package    Mad_Test
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Test_Fixture_Base
{
    /**
     * A cache of the records read in by the YAML parser. This way we don't
     * need to re-parse the same YAML file every test.
     */
    protected static $_ymlCache = array();

    /**
     * Remember which fixtures have been parsed. This is remembered staticly
     * as to not to duplicate any load/teardown operations on the same YAML
     * file from any two different fixtures.
     *
     * @var array
     */
    protected static $_parsed   = array();

    /**
     * Keep track of which specific fixture files have already been loaded
     * for this request so that we do not double-load.
     *
     * @var array
     */
    protected static $_toLoad  = array();

    /**
     * Keep track of which specific fixture files have been torn down so far.
     * When two different fixtures "require" the same parent fixture. We can only
     * delete that parent on the last teardown because of FK constraints in the
     * database.
     *
     * @var array
     */
    protected static $_toTeardown = array();

    /**
     * A counter used for suppressing/resuming application logging.
     *
     * This is usually initialized to zero. Change this to 1 to defeat the log
     * suppression (to debug this class, for example).
     *
     * @var integer
     */
    private static $_suppressLogDepth = 0;

    /**
     * Log filter used for suppressing/resuming application logging.
     *
     * @var Zend_Log_Filter_Suppress
     */
    private static $_suppressLogFilter = null;

    /**
     * The connection object - used to load data from yml into the tables
     * @var Mad_Model_ConnectionAdapter_Abstract
     */
    protected $_connection = null;

    /**
     * Name of the yml file for this fixtue
     * @var string
     */
    protected $_ymlName = null;
    
    /**
     * The yml records to load for this fixture
     * @var array
     */
    protected $_records = array();

    /**
     * The options for the yml load
     * @var array
     */
    protected $_options = array();

    /**
     * Required fixtures to setup before this fixture
     * @var	Array[Fixtures]
     */
    protected $_required = array();


    /*##########################################################################
    # Construct
    ##########################################################################*/

    /**
     * Load a fixture by name
     * @param   Mad_Model_ConnectionAdapter_Abstract  $conn
     * @param   string  $ymlName
     * @param   boolean $save
     */
    public function __construct(Mad_Model_ConnectionAdapter_Abstract $conn, $ymlName, $fixturesPath=null)
    {
        $this->_connection = $conn;
        $this->_ymlName = $ymlName;

        if (empty($fixturesPath)) { $fixturesPath = MAD_ROOT.'/test/fixtures'; }
        $this->_fixturesPath = $fixturesPath;
        
        $this->_parseYml($ymlName, $fixturesPath);

        // Remember which yaml files have been parsed for load.
        // Helps us to eliminate duplicate loads/teardowns
        if (isset(self::$_parsed[$ymlName])) {
            self::$_parsed[$ymlName]++;
        } else {
            self::$_parsed[$ymlName] = 1;
        }
        self::$_toLoad = self::$_toTeardown = self::$_parsed;
    }


    /*##########################################################################
    # Public
    ##########################################################################*/

    /**
     * Load the data for this fixture
     */
    public function load()
    {
        self::_suppressLogging();
        // load in required fixtures.
        foreach ($this->_required as $fixture) {
            $fixture->load();
        }
        // only load if we haven't loaded it yet
        if (isset(self::$_toLoad[$this->_ymlName])) {
            $this->_loadYml();
            unset(self::$_toLoad[$this->_ymlName]);
        }
        self::_resumeLogging();
    }

    /**
     * Execute the sql after the test finishes. Teardown required
     * fixtures in reverse order
     */
    public function teardown()
    {
        self::_suppressLogging();
        // only teardown if there are no more dependencies
        if (self::$_toTeardown[$this->_ymlName] == 1) {
            if ($this->_options['teardown']) {
                $sql = $this->_options['teardown'];
            } else {
                $sql = "TRUNCATE TABLE ".$this->_options['table'];
            }
            $this->_executeSql($sql);
        }
        self::$_toTeardown[$this->_ymlName]--;

        // teardown required in reverse
        foreach (array_reverse($this->_required) as $fixture) {
            $fixture->teardown();
        }
        self::_resumeLogging();
    }

    /**
     * Get the list of records in the yml file. Look recursively through
     * all required fixtures as well
     *
     * @return  array
     */
    public function getRecords()
    {
        return $this->_totalRecords();
    }

    /**
     * Get a single row from the fixture. Look recursively through
     * all required fixtures as well
     *
     * @param   string  $name
     * @return  array
     */
    public function getRecord($name)
    {
        $totalRecords = $this->_totalRecords();
        return isset($totalRecords[$name]) ? $totalRecords[$name] : array();
    }

    /**
     * Get the name of the yml file for this fixture
     * @return  string
     */
    public function getYmlName()
    {
        return $this->_ymlName;
    }

    /**
     * Get the table associated with this fixture
     * @return  string
     */
    public function getTableName()
    {
        return $this->_options['table'];
    }

    /*##########################################################################
    # Static
    ##########################################################################*/

    /**
     * Get the YAML file names to be included & how many times they appear.
     * @return  array
     */
    public static function getParsed()
    {
        return self::$_parsed;
    }

    /**
     * Reset parsed counts
     */
    public static function resetParsed()
    {
        self::$_parsed     = array();
        self::$_toLoad     = array();
        self::$_toTeardown = array();
    }

    /**
     * Get the yaml files to be loaded
     * @return  array
     */
    public static function getToLoad()
    {
        return self::$_toLoad;
    }

    /**
     * Reset loaded counts
     */
    public static function resetToLoad()
    {
        self::$_toLoad = self::$_parsed;
    }

    /**
     * Get the yaml files to be torn down
     * @return  array
     */
    public static function getToTeardown()
    {
        return self::$_toTeardown;
    }

    /**
     * Reset torndown counts
     */
    public static function resetToTeardown()
    {
        self::$_toTeardown = self::$_parsed;
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
    # Protected
    ##########################################################################*/

    /**
     * Parse the fixture data from the given array. This will
     * take the yaml file and validate and set the records/options.
     */
    private function _parseYml($ymlName, $fixturesPath)
    {
        // only parse if not in cache
        if (!isset(self::$_ymlCache[$ymlName])) {
            $fixtureFile = "{$fixturesPath}/{$ymlName}.yml";

            // Parse yml file
            if (!file_exists($fixtureFile)) {
                throw new Mad_Test_Exception('The fixture file: "'.$fixtureFile.'" does not exist.');
            }

            // dynamic fixtures
            ob_start();
            include "madview://$fixtureFile";
            $fixtureData = ob_get_clean();
            $records = Horde_Yaml::load($fixtureData);

            // Parse options
            $options = isset($records['options']) ? $records['options'] : array();
            $valid = array('table'  => $ymlName, 'log'   => null,    'requires' => array(),
                           'before' => array(),  'after' => array(), 'teardown' => array());
            $options = Mad_Support_Base::assertValidKeys($options, $valid);
            unset($records['options']);

            self::$_ymlCache[$ymlName] = array('records' => $records, 'options' => $options);
        }
        $this->_records = self::$_ymlCache[$ymlName]['records'];
        $this->_options = self::$_ymlCache[$ymlName]['options'];

        // Parse required fixtures for this load
        foreach ($this->_options['requires'] as $required) {
            $this->_required[] = new Mad_Test_Fixture_Base($this->_connection, $required);
        }
    }

    /**
     * Load the YAML data to the database. This will execute the 'before' and
     * 'after' statements as well as insert rows generated from the yml records.
     */
    private function _loadYml()
    {
        $this->_executeSql($this->_options['before'], true);
        foreach ($this->_records as $fixtureName => $attributes) {
            $this->_insertRow($this->_options['table'], $attributes);
        }
        $this->_executeSql($this->_options['after'], true);
    }


    /**
     * Execute a list of sql statements. Before/After sql statements need to
     * commit immediately.
     *
     * @todo add transaction support
     * 
     * @param   array   $sqlStatements
     * @param   boolean $commit
     */
    private function _executeSql($sqlStatements, $commit=false)
    {
        if (!empty($sqlStatements) && $commit) {
            // $this->_connection->commitDbTransaction();
        }

        foreach ((array) $sqlStatements as $sql) {
            $this->_connection->execute($sql);
        }

        if (!empty($sqlStatements) && $commit) {
            // $this->_connection->beginDbTransaction();
        }
    }

    /**
     * Insert a row of data into the table
     * @param   string  $tableName
     * @param   array   $attributes  (column_name => value)
     */
    private function _insertRow($tableName, $attributes)
    {
        foreach ($attributes as $col => $value) {
            $cols[] = $this->_connection->quoteColumnName($col);
            $vals[] = $this->_connection->quote($value);
        }
        $colStr   = implode(', ', $cols);
        $valStr   = implode(', ', $vals);

        // build & execute SQL
        $sql = "INSERT INTO $tableName (".
               "    $colStr".
               ") VALUES (".
               "    $valStr".
               ")";
        $this->_connection->execute($sql);
    }

    /**
     * Recursively get total records set by fixture and all required fixtures
     * @return  array
     */
    private function _totalRecords()
    {
        $totalRecords = $this->_records;
        foreach ($this->_required as $fixture) {
            foreach ($fixture->getRecords() as $key => $val) {
                if (isset($totalRecords[$key])) {
                    $totalRecords[$key] = array_merge($totalRecords[$key], $val);
                } else {
                    $totalRecords[$key] = $val;
                }
            }
        }
        return $totalRecords;
    }
    
    /**
     * Temporarily suppresses all application logging. Used to hide the numerous
     * SQL statements involved in setting up and tearing down fixtures.
     *
     * IMPORTANT: This call suppresses logging for the entire application! Always
     * remember to balance this with a call to {@link _resumeLogging()}
     *
     * @see _resumeLogging()
     */
    private static function _suppressLogging()
    {
        /* A counter is used since fixture loading can be recursive. Logging
         * is suppressed and resumed only at the top level.
         */
        if (++self::$_suppressLogDepth > 1) {
            return;
        }
        if (is_null(self::$_suppressLogFilter)) {
            self::$_suppressLogFilter = new Zend_Log_Filter_Suppress();
            $logger = self::logger();
            if (! is_null($logger)) {
                $logger->addFilter(self::$_suppressLogFilter);
            }
        }
        self::$_suppressLogFilter->suppress(true);
    }

    /**
     * Resumes suppressed application logging.
     *
     * @see _suppressLogging()
     */
    private static function _resumeLogging()
    {
        if (--self::$_suppressLogDepth > 0) {
            return;
        }        
        self::$_suppressLogFilter->suppress(false);
    }
     
}
