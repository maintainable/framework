<?php
/**
 * Class to load/teardown fixtured data from the database
 *
 * @category   Mad
 * @package    Mad_Script
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
 
/**
 * Class to load/teardown fixtured data from the database
 *
 * @category   Mad
 * @package    Mad_Script
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Script_Fixtures extends Mad_Script_Base
{

    /**
     * The fixtures created by the given yml names
     * @var Array[Mad_Test_Fixture_Bases]
     */
    protected $_fixtures = array();


    /*##########################################################################
    # Construct
    ##########################################################################*/

    /**
     * Take the array of arguments given
     * @param   array   $args
     */
    public function __construct($args)
    {
        $this->_filename = array_shift($args);
        while ($ymlName = array_shift($args)) {
            // 'all' is reserved word to clear all data
            if ($ymlName == 'all') break;

            $this->_fixtures[] = new Mad_Test_Fixture_Base($this->_connection, $ymlName);
        }

        $this->_connection->beginDbTransaction();

        // display help if no fixtures given
        if (empty($this->_fixtures)) {
            $this->_displayHelp();

        // load fixtures to connection (teardown first)
        } elseif (strstr($this->_filename, 'load_fixtures')) {
            $this->_teardownFixtures();
            $this->_loadFixtures();

        // delete fixtures from connection
        } elseif (strstr($this->_filename, 'teardown_fixtures')) {
            if ($ymlName == 'all') {
                $this->_teardownAll();
            } else {
                $this->_teardownFixtures();
            }
        }

        $this->_connection->commitDbTransaction(true);
        $this->_disconnect();
    }

    /**
     * Load fixture data to the database
     */
    protected function _loadFixtures()
    {
        Mad_Test_Fixture_Base::resetToLoad();
        foreach ($this->_fixtures as $fixture) {
            $fixture->load();
            $this->_print("loading ".$fixture->getYmlName());
        }
    }

    /**
     * Delete fixture data from the database
     */
    protected function _teardownFixtures()
    {
        Mad_Test_Fixture_Base::resetToTeardown();
        foreach ($this->_fixtures as $fixture) {
            $fixture->teardown();
            $this->_print("deleting ".$fixture->getYmlName());
        }
    }

    /**
     * @todo Delete all fixture data from the db
     */
    protected function _teardownAll()
    {}


    /**
     * Display help guidelines
     */
    private function _displayHelp()
    {
        $msg =
          "\tUsage:                                                                       \n".
          "\t 1. Load fixture data                                                        \n".
          "\t    load_fixtures.php #fixture_name1 #fixture_name2                          \n".
          "\t      eg:                                                                    \n".
          "\t       php ./scripts/load_fixtures.php documents briefcases                  \n".
          "\t                                                                             \n".
          "\t 2. Teardown fixture data                                                    \n".
          "\t     teardown_fixtures.php #fixture_name1 #fixture_name2                     \n".
          "\t       eg:                                                                   \n".
          "\t       php ./scripts/teardown_fixtures.php documents briefcases              \n".
          "\t                                                                             \n".
          "\t 4. This help.                                                               \n".
          "\t     php {$this->_filename}                                                  \n".
          "\n";
        $this->_exit($msg);
    }
}

?>
