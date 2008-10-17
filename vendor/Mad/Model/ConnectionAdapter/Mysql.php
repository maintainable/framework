<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage ConnectionAdapter
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage ConnectionAdapter
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_ConnectionAdapter_Mysql extends Mad_Model_ConnectionAdapter_Abstract
{
    /**
     * @return  string
     */
    public function adapterName()
    {
        return 'MySQL';
    }

    /**
     * @return  boolean
     */
    public function supportsMigrations()
    {
        return true;
    }

    /**
     * The db column types for this adapter
     * 
     * @return  array
     */
    public function nativeDatabaseTypes()
    {
        return array(
            'primaryKey' => "int(11) DEFAULT NULL auto_increment PRIMARY KEY",
            'string'     => array('name' => 'varchar',  'limit' => 255),
            'text'       => array('name' => 'text',     'limit' => null),
            'integer'    => array('name' => 'int',      'limit' => 11),
            'float'      => array('name' => 'float',    'limit' => null),
            'decimal'    => array('name' => 'decimal',  'limit' => null),
            'datetime'   => array('name' => 'datetime', 'limit' => null),
            'timestamp'  => array('name' => 'datetime', 'limit' => null),
            'time'       => array('name' => 'time',     'limit' => null),
            'date'       => array('name' => 'date',     'limit' => null),
            'binary'     => array('name' => 'blob',     'limit' => null),
            'boolean'    => array('name' => 'tinyint',  'limit' => 1),
        );
    }


    /*##########################################################################
    # Quoting
    ##########################################################################*/

    /**
     * @return  string
     */
    public function quoteColumnName($name) 
    {
        return "`$name`";
    }

    /**
     * @return  string
     */
    public function quoteTrue()
    {
        return '1';
    }

    /**
     * @return  string
     */
    public function quoteFalse()
    {
        return '0';
    }


    /*##########################################################################
    # Connection Management
    ##########################################################################*/

    /**
     * Check if the connection is active
     * 
     * @return  boolean
     */
    public function isActive()
    {
       return isset($this->_connection) && $this->_connection->query("SELECT 1");
    }


    /*##########################################################################
    # Database Statements
    ##########################################################################*/

    /**
     * Appends +LIMIT+ and +OFFSET+ options to a SQL statement.
     * 
     * @param   string  $sql
     * @param   array   $options
     * @return  string
     */
     public function addLimitOffset($sql, $options)
     {
        if (isset($options['limit']) && $limit = $options['limit']) {
            if (isset($options['offset']) && $offset = $options['offset']) {
                $sql .= " LIMIT $offset, $limit";
            } else {
                $sql .= " LIMIT $limit";
            }
        }
        return $sql;
    }


    /*##########################################################################
    # Schema Statements
    ##########################################################################*/

    /**
     * Dump entire schema structure or specific table
     * 
     * @param   string  $table
     * @return  string
     */
    public function structureDump($table=null)
    {
        foreach ($this->selectAll("SHOW TABLES") as $row) {
            if ($table && $table != current($row)) { continue; }
            $dump = $this->selectOne("SHOW CREATE TABLE ".current($row)."");
            $creates[] = $dump['Create Table'].';';
        }
        return isset($creates) ? implode("\n\n", $creates) : null;
    }

    /**
     * Recreate the given db
     * 
     * @param   string  $name
     */
    public function recreateDatabase($name)
    {
        $this->dropDatabase($name);
        return $this->createDatabase($name);
    }

    /**
     * Create the given db
     * 
     * @param   string  $name
     */
    public function createDatabase($name)
    {
        return $this->execute("CREATE DATABASE $name");
    }

    /**
     * Drop the given db
     * 
     * @param   string  $name
     */
    public function dropDatabase($name)
    {
        return $this->execute("DROP DATABASE IF EXISTS $name");
    }

    /**
     * Get the nam eof the current db
     * 
     * @return  string
     */
    public function currentDatabase()
    {
        $row = $this->selectOne("SELECT DATABASE() AS db");
        return $row["db"];
    }

    /**
     * List of tables for the db
     * 
     * @param   string  $name
     */
    public function tables($name=null)
    {
        $tables = array();
        foreach ($this->execute("SHOW TABLES") as $row) {
            $tables[] = current($row);
        }
        return $tables;
    }

    /**
     * List of indexes for the given table
     * 
     * @param   string  $tableName
     * @param   string  $name
     */
    public function indexes($tableName, $name=null)
    {
        $indexes = array();
        $currentIndex = null;
        foreach ($this->execute("SHOW KEYS FROM $tableName") as $row) {
            if ($currentIndex != $row[2]) {
                if ($row[2] == 'PRIMARY') continue;
                $currentIndex = $row[2];
                $indexes[] = (object)array('table'   => $row[0], 
                                           'name'    => $row[2], 
                                           'unique'  => $row[1] == "0", 
                                           'columns' => array());
            }
            $indexes[sizeof($indexes)-1]->columns[] = $row[4];
        }
        return $indexes;
    }

    /**
     * @param   string  $tableName
     * @param   string  $name
     */
    public function columns($tableName, $name=null)
    {
        // check cache
        if (Mad_Model_Base::$cacheTables) {
            $cacheFile = MAD_ROOT."/tmp/cache/tables/$tableName.php";
            @include($cacheFile);
        }

        // query to build rows
        if (!isset($rows)) {
            $results = $this->execute("SHOW FIELDS FROM $tableName", $name); 
            foreach ($results as $row) { $rows[] = $row; }

            // write cache
            if (Mad_Model_Base::$cacheTables) {
                file_put_contents($cacheFile, "<?php\n\$rows = ".var_export($rows, true).";");
            }
        }

        // create columns from rows
        $columns = array();
        foreach ($rows as $row) {
            $columns[] = new Mad_Model_ConnectionAdapter_Mysql_Column(
                $row[0], $row[4], $row[1], $row[2] == "YES");
        }
        return $columns;
    }

    /**
     * Override createTable to return a Mysql Table Definition
     * param    string  $name
     * param    array   $options
     */
    public function createTable($name, $options=array()) 
    {
        $pk = isset($options['primaryKey']) && $options['primaryKey'] === false ? false : 'id';
        $tableDefinition = 
            new Mad_Model_ConnectionAdapter_Mysql_TableDefinition($name, $this, $options);
        if ($pk != false) {
            $tableDefinition->primaryKey($pk);
        }
        return $tableDefinition;
    }

    /**
     * @param   string  $name
     * @param   array   $options
     */
    public function endTable($name, $options=array())
    {
        $inno = array('options' => 'ENGINE=InnoDB');
        return parent::endTable($name, array_merge($inno, $options));
    }

    /**
     * @param   string  $name
     * @param   string  $newName
     */
    public function renameTable($name, $newName)
    {
        $this->_clearTableCache($name);

        return $this->execute("RENAME TABLE $name TO $newName");
    }

    /**
     * @param   string  $tableName
     * @param   string  $columnName
     * @param   string  $default
     */
    public function changeColumnDefault($tableName, $columnName, $default)
    {
        $this->_clearTableCache($tableName);

        $sql = "SHOW COLUMNS FROM $tableName LIKE '$columnName'";
        $res = $this->selectOne($sql);
        $currentType = $res["Type"];

        $default = $this->quote($default);
        $sql = "ALTER TABLE $tableName CHANGE $columnName $columnName 
                $currentType DEFAULT $default";
        return $this->execute($sql);
    }

    /**
     * @param   string  $tableName
     * @param   string  $columnName
     * @param   string  $type
     * @param   array   $options
     */
    public function changeColumn($tableName, $columnName, $type, $options=array())
    {
        $this->_clearTableCache($tableName);

        if (!$this->_doOptionsIncludeDefault($options)) {
            if (!array_key_exists('default', $options)) {
                $row = $this->selectOne("SHOW COLUMNS FROM $tableName 
                                         LIKE '$columnName'");
                $options['default'] = $row['Default'];
            }
        }

        $limit     = !empty($options['limit'])     ? $options['limit']     : null;
        $precision = !empty($options['precision']) ? $options['precision'] : null;
        $scale     = !empty($options['scale'])     ? $options['scale']     : null;

        $typeSql = $this->typeToSql($type, $limit, $precision, $scale); 

        $sql = "ALTER TABLE $tableName CHANGE $columnName $columnName $typeSql";
        $sql = $this->addColumnOptions($sql, $options);
        $this->execute($sql);
    }

    /**
     * @param   string  $tableName
     * @param   string  $columnName
     * @param   string  $newColumnName
     */
    public function renameColumn($tableName, $columnName, $newColumnName)
    {
        $this->_clearTableCache($tableName);

        $sql = "SHOW COLUMNS FROM $tableName LIKE '$columnName'";
        $res = $this->selectOne($sql);
        $currentType = $res["Type"];

        $sql = "ALTER TABLE $tableName CHANGE ".
                $this->quoteColumnName($columnName)." ".
                $this->quoteColumnName($newColumnName)." ".
                $currentType;
        return $this->execute($sql);
    }

    /**
     * Add AFTER option
     * 
     * @param   string  $sql
     * @param   array   $options
     * @return  string
     */
    public function addColumnOptions($sql, $options)
    {
        $sql = parent::addColumnOptions($sql, $options);
        if (isset($options['after'])) {
            $sql .= " AFTER ".$this->quoteColumnName($options['after']);
        }
        return $sql;
    }
    
    
    /*##########################################################################
    # Protected
    ##########################################################################*/
    
    /**
     * Parse YAML configuration array into options for PDO constructor
     *
     * http://pecl.php.net/bugs/7234
     * Setting a bogus socket does not appear to work.
     *
     * @throws  Mad_Model_Exception
     * @return  array  [dsn, username, password]
     */
    protected function _parseConfig()
    {
        if (isset($this->_config['port'])) {
            if (empty($this->_config['host'])) {
                $msg = 'host is required if port is specified';
                throw new Mad_Model_Exception($msg);
            }
            
            if (preg_match('/[^\d\.]/', $this->_config['host'])) {
                $msg = 'pdo_mysql ignores port unless IP address is used for host';
                throw new Mad_Model_Exception($msg);
            }
        }
        
        return parent::_parseConfig();
    }    
}

