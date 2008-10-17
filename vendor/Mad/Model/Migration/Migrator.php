<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Migration_Migrator
{
    /**
     * @var string
     */
    protected $_direction = null;

    /**
     * @var string
     */
    protected $_migrationsPath = null;

    /**
     * @var int
     */
    protected $_targetVersion = null;


    /**
     * @param   string  $direction
     * @param   string  $migrationsPath
     * @param   int     $targetVersion
     */
    public function __construct($direction, $migrationsPath, $targetVersion=null)
    {
        if (!Mad_Model_Base::connection()->supportsMigrations()) {
            $msg = "This database does not yet support migrations";
            throw new Mad_Model_Exception_Migration($msg);
        }
        $this->_direction      = $direction;
        $this->_migrationsPath = $migrationsPath;
        $this->_targetVersion  = $targetVersion;

        Mad_Model_Base::connection()->initializeSchemaInformation();
    }


    /*##########################################################################
    # Public
    ##########################################################################*/

    /**
     * Perform migration
     */
    public function doMigrate()
    {
        foreach ($this->_getMigrationClasses() as $migration) {
            if ($this->_hasReachedTargetVersion($migration->version)) {
                $msg = "Reached target version: $this->_targetVersion";
                Mad_Model_Base::logger()->info($msg);
                return;
            }
            if ($this->_isIrrelevantMigration($migration->version)) { continue; }

            // log
            $msg = "Migrating to ".get_class($migration)." (".$migration->version.")";
            Mad_Model_Base::logger()->info($msg);

            // migrate
            $migration->migrate($this->_direction);
            $this->_setSchemaVersion($migration->version);
        }
    }


    /*##########################################################################
    # Static
    ##########################################################################*/

    /**
     * @param   string  $migrationsPath
     * @param   string  $targetVersion
     */
    public static function migrate($migrationsPath, $targetVersion=null)
    {
        Mad_Model_Base::connection()->initializeSchemaInformation();
        $currentVersion = self::getCurrentVersion();

        if ($targetVersion == null || $currentVersion < $targetVersion) {
            self::up($migrationsPath, $targetVersion);

        // migrate down
        } elseif ($currentVersion > $targetVersion) {
            self::down($migrationsPath, $targetVersion);

        // You're on the right version
        } elseif ($currentVersion == $targetVersion) {
            return; 
        }
    }

    /**
     * @param   string  $migrationsPath
     * @param   string  $targetVersion
     */
    public static function up($migrationsPath, $targetVersion = null)
    {
        $mig = new Mad_Model_Migration_Migrator('up', $migrationsPath, $targetVersion);
        $mig->doMigrate();
    }

    /**
     * @param   string  $migrationsPath
     * @param   string  $targetVersion
     */
    public static function down($migrationsPath, $targetVersion = null)
    {
        $mig = new Mad_Model_Migration_Migrator('down', $migrationsPath, $targetVersion);
        $mig->doMigrate();
    }

    /**
     * @return  int
     */
    public static function getCurrentVersion()
    {
        $sql = "SELECT version FROM schema_info";
        return Mad_Model_Base::connection()->selectValue($sql);
    }


    /*##########################################################################
    # Protected
    ##########################################################################*/

    /**
     * @return  array
     */
    protected function _getMigrationClasses()
    {
        $migrations = array();
        foreach ($this->_getMigrationFiles() as $migrationFile) {
            require_once $migrationFile;
            list($version, $name) = $this->_getMigrationVersionAndName($migrationFile);
            $this->_assertUniqueMigrationVersion($migrations, $version);
            $migrations[$version] = $this->_getMigrationClass($name, $version);
        }

        // sort by version
        ksort($migrations);
        $sorted = array_values($migrations);
        return $this->_isDown() ? array_reverse($sorted) : $sorted;
    }

    /**
     * @param   array   $migrations
     * @param   integer $version
     */
    protected function _assertUniqueMigrationVersion($migrations, $version)
    {
        if (isset($migrations[$version])) {
            $msg = "Multiple migrations have the version number $version";
            throw new Mad_Model_Exception_Migration($msg);
        }
    }

    /**
     * Get the list of migration files
     * @return  array
     */
    protected function _getMigrationFiles()
    {
        $files = glob("$this->_migrationsPath/[0-9]*_*.php");
        return $this->_isDown() ? array_reverse($files) : $files;
    }

    /**
     * Actually return object, and not class
     *
     * @param   string  $migrationName
     * @param   int     $version
     * @return  Mad_Model_Migration_Base
     */
    protected function _getMigrationClass($migrationName, $version)
    {
        $className = Mad_Support_Inflector::camelize($migrationName);
        $migration = new $className;
        $migration->version = $version;
        return $migration;
    }

    /**
     * @param   string  $migrationFile
     * @return  array   ($version, $name)
     */
    protected function _getMigrationVersionAndName($migrationFile)
    {
        preg_match_all('/([0-9]+)_([_a-z0-9]*).php/', $migrationFile, $matches);
        return array($matches[1][0], $matches[2][0]);
    }

    /**
     * @param   integer $version
     */
    protected function _setSchemaVersion($version)
    {
        $version = $this->_isDown() ? $version - 1 : $version;
        $sql = "UPDATE schema_info SET version = ".(int)$version;
        Mad_Model_Base::connection()->update($sql);
    }

    /**
     * @return  boolean
     */
    protected function _isUp()
    {
        return $this->_direction == 'up';
    }

    /**
     * @return  boolean
     */
    protected function _isDown()
    {
        return $this->_direction == 'down';
    }

    /**
     * @return  boolean
     */
    protected function _hasReachedTargetVersion($version)
    {
        if ($this->_targetVersion === null) { return false; }

        return ($this->_isUp()   && $version-1 >= $this->_targetVersion) || 
               ($this->_isDown() && $version   <= $this->_targetVersion);
    }

    /**
     * @param   integer $version
     * @return  boolean
     */
    protected function _isIrrelevantMigration($version)
    {
        return ($this->_isUp()   && $version <= self::getCurrentVersion()) || 
               ($this->_isDown() && $version >  self::getCurrentVersion());
    }
}