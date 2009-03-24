<?php
/**
 * @category   Mad
 * @package    Mad_Test
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * TestFixtues is a collection of {@link Mad_Test_Fixture_Base} objects. This
 * object takes care of load/teardown of multiple test fixture objects
 * while maintaining that the multiple fixtures don't collide in
 * inserting the same data twice or deleting the data out of order
 *
 * @category   Mad
 * @package    Mad_Test
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Test_Fixture_Collection
{
    /**
     * The connection object - used to load data from yml into the tables
     * @var Mad_Model_ConnectionAdapter_Abstract
     */
    protected $_connection = null;

    /**
     * The list of fixtures
     * @var array
     */
    protected $_fixtures = array();


    /*##########################################################################
    # Construct
    ##########################################################################*/

    /**
     * Load a fixture by name
     * @param   object        $conn       Connection Adapter
     * @param   string|array  $ymlNames
     */
    public function __construct($conn, $ymlNames)
    {
        Mad_Test_Fixture_Base::resetParsed();
        $this->_connection = $conn;

        $this->_parseFixtures($ymlNames);
    }


    /*##########################################################################
    # Public
    ##########################################################################*/

    /**
     * Add a fixture to the list of fixtures
     * @param   string|array  $ymlNames
     */
    public function addFixture($ymlNames)
    {
        $this->_parseFixtures($ymlNames);
    }

    /**
     * Load test fixture data
     * 
     * @todo suppress log noise of SQL from fixture loads
     */
    public function load()
    {
        Mad_Test_Fixture_Base::resetToLoad();
        foreach ($this->_fixtures as $fixture) {
            $fixture->load();
        }
    }

    /**
     * Teardown fixture data. Use transaction to speed up deletes
     * 
     * @todo suppress log noise of SQL from fixture teardowns
     */
    public function teardown()
    {
        Mad_Test_Fixture_Base::resetToTeardown();
        foreach ($this->_fixtures as $fixture) {
            $fixture->teardown();
        }
    }

    /**
     * Get the test fixtures
     * @return  Array[Mad_Test_Fixture_Base]
     */
    public function getFixtures()
    {
        return $this->_fixtures;
    }

    /**
     * Get all the yml records
     * @return  array
     */
    public function getRecords()
    {
        foreach ($this->_fixtures as $fixture) {
            if (!empty($records)) {
                $records = array_merge($records, $fixture->getRecords());
            } else {
                $records = $fixture->getRecords();
            }
        }
        return !empty($records) ? $records : array();
    }


    /*##########################################################################
    # Private methods
    ##########################################################################*/

    /**
     * Parse and load the YAML fixtures
     * 
     * @todo suppress log noise of SQL from sub-fixture loads
     * @todo add transaction support
     * 
     * @param   string|array    $ymlNames
     */
    private function _parseFixtures($ymlNames)
    {
        // $this->_connection->beginDbTransaction();

        foreach ((array)$ymlNames as $ymlName) {
            $this->_fixtures[] = new Mad_Test_Fixture_Base($this->_connection, $ymlName);
        }
        $this->teardown();
        $this->load();

        // $this->_connection->commitDbTransaction();
    }
}

?>