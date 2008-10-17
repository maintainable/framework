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
abstract class Mad_Model_ConnectionAdapter_Abstract
{
    /**
     * Config options from database.yml
     * @var array
     */
    protected $_config = array();

    /**
     * @var PDO
     */
    protected $_connection = null;

    /**
     * @var boolean
     */
    protected $_transactionStarted = false;

    /**
     * @var int
     */
    protected $_rowCount = null;

    /**
     * @var Logger
     */
    protected $_logger = null;

    /**
     * @var int
     */
    protected $_runtime = null;

    /**
     * @var boolean
     */
    protected $_active = null;


    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * @param   Connection $connection
     * @param   Logger     $logger
     */
    public function __construct($config, $logger=null) 
    {
        $this->_config  = $config;
        $this->_logger  = $logger;
        $this->_runtime = 0;

        $this->connect();
    }


    /*##########################################################################
    # Public 
    ##########################################################################*/

    /**
     * Returns the human-readable name of the adapter.  Use mixed case - one
     * can always use downcase if needed.
     * 
     * @return  string
     */
    public function adapterName()
    {
        return 'Abstract';
    }

    /**
     * Does this adapter support migrations?  Backend specific, as the
     * abstract adapter always returns +false+.
     *
     * @return  boolean
     */
    public function supportsMigrations() 
    {
        return false;
    }

    /**
     * Does this adapter support using DISTINCT within COUNT?  This is +true+
     * for all adapters except sqlite.
     * 
     * @return  boolean
     */
    public function supportsCountDistinct()
    {
        return true;
    }

    /**
     * Reset the timer
     * 
     * @return  int
     */
    public function resetRuntime() 
    {
        $runtime = $this->_runtime;
        $this->_runtime = 0;
        return $this->_runtime;
    }


    /*##########################################################################
    # Connection Management
    ##########################################################################*/

    /**
     * Connect to the db
     */
    public function connect()
    {
        list($dsn, $user, $pass) = $this->_parseConfig();

        try {
            $pdo = new PDO($dsn, $user, $pass);
        } catch (PDOException $e) {
            $msg = "Error instantiating PDO with DSN \"$dsn\".  PDOException: "
                 . $e->getMessage(); 
            throw new Mad_Model_Exception($msg);
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true); 

        $this->_connection = $pdo;
        $this->_active     = true;
    }

    /**
     * Reconnect to the db
     */
    public function reconnect()
    {
        $this->disconnect();
        $this->connect();
    }

    /**
     * Disconnect from db
     */
    public function disconnect()
    {
        $this->_connection = null;
        $this->_active = false;
    }

    /**
     * Is the connection active
     * 
     * @return  boolean
     */
    public function isActive()
    {
        return $this->_active;
    }


    /*##########################################################################
    # Database Statements
    ##########################################################################*/

    /**
     * Returns an array of records with the column names as keys, and 
     * column values as values. 
     * 
     * @param   string  $sql
     * @param   string  $name
     * @return  array
     */
    public function select($sql, $name=null)
    {
        $result = $this->execute($sql, $name);
        if ($result) {
            foreach ($result as $row) {
                $rows[] = $row;
            }
        }
        return isset($rows) ? $rows : array();
    }

    /**
     * Returns an array of record hashes with the column names as keys and
     * column values as values.
     * 
     * @param   string  $sql
     * @param   string  $name
     */
    public function selectAll($sql, $name=null)
    {
        return $this->select($sql, $name);
    }

    /**
     * Returns a record hash with the column names as keys and column values
     * as values.
     * 
     * @param   string  $sql
     * @param   string  $name
     * @return  array
     */
    public function selectOne($sql, $name=null)
    {
        $result = $this->select($sql, $name);
        return $result ? current($result) : array();
    }

    /**
     * Returns a single value from a record
     * 
     * @param   string  $sql
     * @param   string  $name
     * @return  string
     */
    public function selectValue($sql, $name=null)
    {
        $result = $this->selectOne($sql, $name);
        return $result ? current($result) : null;
    }

    /**
     * Returns an array of the values of the first column in a select:
     *   select_values("SELECT id FROM companies LIMIT 3") => [1,2,3]
     * 
     * @param   string  $sql
     * @param   string  $name
     */
    public function selectValues($sql, $name=null)
    {
        $result = $this->selectAll($sql, $name);
        foreach ($result as $row) {
            $values[] = current($row);
        }
        return isset($values) ? $values : array();
    }

    /**
     * Executes the SQL statement in the context of this connection.
     * 
     * @param   string  $sql
     * @param   string  $name
     */
    public function execute($sql, $name=null)
    {
        $t = new Mad_Support_Timer();
        $t->start();

        try {
            $stmt = $this->_connection->query($sql);            
        } catch (PDOException $e) {
            $this->_logInfo($sql, 'QUERY FAILED: ' . $e->getMessage());
            $this->_logInfo($sql, $name);
            throw $e;
        }

        $this->_logInfo($sql, $name, $t->finish());

        $this->_rowCount = $stmt ? $stmt->rowCount() : 0;
        return $stmt;
    }

    /**
     * Returns the last auto-generated ID from the affected table.
     * 
     * @param   string  $sql
     * @param   string  $name
     * @param   string  $pk
     * @param   int     $idValue
     * @param   string  $sequenceName
     */
    public function insert($sql, $name=null, $pk=null, $idValue=null, $sequenceName=null)
    {
        $this->execute($sql, $name);
        return isset($idValue) ? $idValue : $this->_connection->lastInsertId();
    }

    /**
     * Executes the update statement and returns the number of rows affected.
     * 
     * @param   string  $sql
     * @param   string  $name
     */
    public function update($sql, $name=null)
    {
        $this->execute($sql, $name);
        return $this->_rowCount;
    }

    /**
     * Executes the delete statement and returns the number of rows affected.
     * 
     * @param   string  $sql
     * @param   string  $name
     */
    public function delete($sql, $name=null)
    {
        $this->execute($sql, $name);
        return $this->_rowCount;
    }

    /**
     * Check if a transaction has been started
     */
    public function transactionStarted() 
    {
        return $this->_transactionStarted;
    }

    /**
     * Begins the transaction (and turns off auto-committing).
     */
    public function beginDbTransaction() 
    {
        $this->_transactionStarted = true;
        $this->_connection->beginTransaction();
    }

    /**
     * Commits the transaction (and turns on auto-committing).
     */
    public function commitDbTransaction() 
    {
        $this->_connection->commit();
        $this->_transactionStarted = false;
    }

    /**
     * Rolls back the transaction (and turns on auto-committing). Must be
     * done if the transaction block raises an exception or returns false.
     */
    public function rollbackDbTransaction() 
    {
        if (! $this->_transactionStarted) { return; }

        $this->_connection->rollBack();
        $this->_transactionStarted = false;
    }

    /**
     * Appends +LIMIT+ and +OFFSET+ options to a SQL statement.
     * 
     * @param   string  $sql
     * @param   array   $options
     * @return  string
     */
    abstract public function addLimitOffset($sql, $options);


    /*##########################################################################
    # Quoting
    ##########################################################################*/

    /**
     * Quotes the column value to help prevent
     * {SQL injection attacks}[http://en.wikipedia.org/wiki/SQL_injection].
     * 
     * @param   string  $value
     * @param   string  $column
     * @return  string
     */
    public function quote($value, $column=null) 
    {
        $type = isset($column) ? $column->getType() : null;

        if (is_null($value)) {
            return "NULL";
        } elseif ($value === true) {
            return $type == "integer" ? "1" : $this->quoteTrue();
        } elseif ($value === false) {
            return $type == "integer" ? "0" : $this->quoteFalse();
        } else {
            return $this->quoteString($value);
        }
    }

    /**
     * Quotes a string, escaping any ' (single quote) and \ (backslash)
     * characters..
     * 
     * @param   string  $string
     * @return  string
     */
    public function quoteString($string) 
    {
        return $this->_connection->quote($string);
    }

    /**
     * Returns a quoted form of the column name. This is highly adapter
     * specific.
     * 
     * @param   string  $name
     * @return  string
     */
    public function quoteColumnName($name) 
    {
        return $name;
    }

    /**
     * @return  string
     */
    public function quoteTrue() 
    {
        return "'t'";
    }

    /**
     * @return  string
     */
    public function quoteFalse() 
    {
        return "'f'";
    }


    /*##########################################################################
    # Schema Statements
    ##########################################################################*/

    /**
     * Returns a Hash of mappings from the abstract data types to the native
     * database types.  See TableDefinition#column for details on the recognized
     * abstract data types.
     * 
     * @return  array
     */
    public function nativeDatabaseTypes()
    {
        return array();
    }

    /**
     * This is the maximum length a table alias can be
     * 
     * @return  int
     */
    public function tableAliasLength()
    {
        return 255;
    }

    /**
     * Truncates a table alias according to the limits of the current adapter.
     * 
     * @param   string  $tableName
     * @return  string
     */
    public function tableAliasFor($tableName)
    {
        $alias = substr($tableName, 0, $this->tableAliasLength());
        return str_replace('.', '_', $alias);
    }

    /**
     * @param   string  $name
     * @return  array
     */
    abstract public function tables($name=null);

    /**
     * Returns an array of indexes for the given table.
     * 
     * @param   string  $tableName
     * @param   string  $name
     * @return  array
     */
    abstract public function indexes($tableName, $name=null);

    /**
     * Returns an array of Column objects for the table specified by +table_name+.
     * See the concrete implementation for details on the expected parameter values.
     * 
     * @param   string  $tableName
     * @param   string  $name
     * @return  array
     */
    abstract public function columns($tableName, $name=null);

    /**
     * Creates a new table
     * There are two ways to work with #create_table.  You can use the block
     * form or the regular form, like this:
     *
     * === Block form
     *  # create_table() yields a TableDefinition instance
     *  create_table(:suppliers) do |t|
     *    t.column :name, :string, :limit => 60
     *    # Other fields here
     *  end
     *
     * === Regular form
     *  create_table(:suppliers)
     *  add_column(:suppliers, :name, :string, {:limit => 60})
     *
     * The +options+ hash can include the following keys:
     * [<tt>:id</tt>]
     *   Set to true or false to add/not add a primary key column
     *   automatically.  Defaults to true.
     * [<tt>:primary_key</tt>]
     *   The name of the primary key, if one is to be added automatically.
     *   Defaults to +id+.
     * [<tt>:options</tt>]
     *   Any extra options you want appended to the table definition.
     * [<tt>:temporary</tt>]
     *   Make a temporary table.
     * [<tt>:force</tt>]
     *   Set to true or false to drop the table before creating it.
     *   Defaults to false.
     *
     * ===== Examples
     * ====== Add a backend specific option to the generated SQL (MySQL)
     *  create_table(:suppliers, :options => 'ENGINE=InnoDB DEFAULT CHARSET=utf8')
     * generates:
     *  CREATE TABLE suppliers (
     *    id int(11) DEFAULT NULL auto_increment PRIMARY KEY
     *  ) ENGINE=InnoDB DEFAULT CHARSET=utf8
     *
     * ====== Rename the primary key column
     *  create_table(:objects, :primary_key => 'guid') do |t|
     *    t.column :name, :string, :limit => 80
     *  end
     * generates:
     *  CREATE TABLE objects (
     *    guid int(11) DEFAULT NULL auto_increment PRIMARY KEY,
     *    name varchar(80)
     *  )
     *
     * ====== Do not add a primary key column
     *  create_table(:categories_suppliers, :id => false) do |t|
     *    t.column :category_id, :integer
     *    t.column :supplier_id, :integer
     *  end
     * generates:
     *  CREATE TABLE categories_suppliers_join (
     *    category_id int,
     *    supplier_id int
     *  )
     *
     * See also TableDefinition#column for details on how to create columns.
     * 
     * @param   string  $name
     * @param   array   $options
     */
    public function createTable($name, $options=array()) 
    {
        $pk = isset($options['primaryKey']) && 
              $options['primaryKey'] === false ? false : 'id';
        $tableDefinition = 
            new Mad_Model_ConnectionAdapter_Abstract_TableDefinition($name, $this, $options);
        if ($pk != false) {
            $tableDefinition->primaryKey($pk);
        }
        return $tableDefinition;
    }

    /**
     * Execute table creation
     * 
     * @param   string  $name
     * @param   array   $options
     */
    public function endTable($name, $options=array())
    {
        if ($name instanceof Mad_Model_ConnectionAdapter_Abstract_TableDefinition) {
            $tableDefinition = $name;
            $options = array_merge($tableDefinition->getOptions(), $options);
        } else {
            $tableDefinition = $this->createTable($name, $options);
        }

        // drop previous
        if (isset($options['force'])) {
            $this->dropTable($tableDefinition->getName(), $options);
        }

        $temp = !empty($options['temporary']) ? 'TEMPORARY'           : null;
        $opts = !empty($options['options'])   ? $options['options']   : null;

        $sql  = "CREATE $temp TABLE ".$tableDefinition->getName()." (\n".
                  $tableDefinition->toSql()."\n".
                ") $opts";
        return $this->execute($sql);
    }

    /**
     * Renames a table.
     * ===== Example
     *  rename_table('octopuses', 'octopi')
     * 
     * @param   string  $name
     * @param   string  $newName
     */
    abstract public function renameTable($name, $newName);

    /**
     * Drops a table from the database.
     * 
     * @param   string  $name
     */
    public function dropTable($name)
    {
        $this->_clearTableCache($name);
        return $this->execute("DROP TABLE $name");
    }

    /**
     * Adds a new column to the named table.
     * See TableDefinition#column for details of the options you can use.
     * 
     * @param   string  $tableName
     * @param   string  $columnName
     * @param   string  $type
     * @param   array   $options
     */
    public function addColumn($tableName, $columnName, $type, $options=array())
    {
        $this->_clearTableCache($tableName);

        $limit     = isset($options['limit'])     ? $options['limit']     : null;
        $precision = isset($options['precision']) ? $options['precision'] : null;
        $scale     = isset($options['scale'])     ? $options['scale']     : null;

        $sql = "ALTER TABLE $tableName ADD ".$this->quoteColumnName($columnName).
               " ".$this->typeToSql($type, $limit, $precision, $scale);
        $sql = $this->addColumnOptions($sql, $options);
        return $this->execute($sql);
    }

    /**
     * Removes the column from the table definition.
     * ===== Examples
     *  remove_column(:suppliers, :qualification)
     * 
     * @param   string  $tableName
     * @param   string  $columnName
     */
    public function removeColumn($tableName, $columnName)
    {
        $this->_clearTableCache($tableName);

        $sql = "ALTER TABLE $tableName DROP ".$this->quoteColumnName($columnName);
        return $this->execute($sql);
    }

    /**
     * Changes the column's definition according to the new options.
     * See TableDefinition#column for details of the options you can use.
     * ===== Examples
     *  change_column(:suppliers, :name, :string, :limit => 80)
     *  change_column(:accounts, :description, :text)
     * 
     * @param   string  $tableName
     * @param   string  $columnName
     * @param   string  $type
     * @param   array   $options
     */
    abstract public function changeColumn($tableName, $columnName, $type, $options=array());

    /**
     * Sets a new default value for a column.  If you want to set the default
     * value to +NULL+, you are out of luck.  You need to
     * DatabaseStatements#execute the apppropriate SQL statement yourself.
     * ===== Examples
     *  change_column_default(:suppliers, :qualification, 'new')
     *  change_column_default(:accounts, :authorized, 1)
     * 
     * @param   string  $tableName
     * @param   string  $columnName
     * @param   string  $default
     */
    abstract public function changeColumnDefault($tableName, $columnName, $default);

    /**
     * Renames a column.
     * ===== Example
     *  rename_column(:suppliers, :description, :name)
     * 
     * @param   string  $tableName
     * @param   string  $columnName
     * @param   string  $newColumnName
     */
    abstract public function renameColumn($tableName, $columnName, $newColumnName);

    /**
     * Adds a new index to the table.  +column_name+ can be a single Symbol, or
     * an Array of Symbols.
     *
     * The index will be named after the table and the first column names,
     * unless you pass +:name+ as an option.
     *
     * When creating an index on multiple columns, the first column is used as a name
     * for the index. For example, when you specify an index on two columns
     * [+:first+, +:last+], the DBMS creates an index for both columns as well as an
     * index for the first colum +:first+. Using just the first name for this index
     * makes sense, because you will never have to create a singular index with this
     * name.
     *
     * ===== Examples
     * ====== Creating a simple index
     *  add_index(:suppliers, :name)
     * generates
     *  CREATE INDEX suppliers_name_index ON suppliers(name)
     * 
     * ====== Creating a unique index
     *  add_index(:accounts, [:branch_id, :party_id], :unique => true)
     * generates
     *  CREATE UNIQUE INDEX accounts_branch_id_index ON accounts(branch_id, party_id)
     * 
     * ====== Creating a named index
     *  add_index(:accounts, [:branch_id, :party_id], :unique => true, :name => 'by_branch_party')
     * generates
     *  CREATE UNIQUE INDEX by_branch_party ON accounts(branch_id, party_id)
     * 
     * @param   string  $tableName
     * @param   string  $columnName
     * @param   array   $options
     */
    public function addIndex($tableName, $columnName, $options=array())
    {
        $this->_clearTableCache($tableName);

        $columnNames = (array)($columnName);
        $indexName = $this->indexName($tableName, array('column' => $columnNames));

        $indexType = !empty($options['unique']) ? "UNIQUE"         : null;
        $indexName = !empty($options['name'])   ? $options['name'] : $indexName;

        foreach ($columnNames as $colName) {
            $quotedCols[] = $this->quoteColumnName($colName);
        }
        $quotedColumnNames = implode(', ', $quotedCols);
        $sql = "CREATE $indexType INDEX ".$this->quoteColumnName($indexName).
               "ON $tableName ($quotedColumnNames)";
        return $this->execute($sql);
    }

    /**
     * Remove the given index from the table.
     *
     * Remove the suppliers_name_index in the suppliers table (legacy support, use the second or third forms).
     *   remove_index :suppliers, :name
     * Remove the index named accounts_branch_id in the accounts table.
     *   remove_index :accounts, :column => :branch_id
     * Remove the index named by_branch_party in the accounts table.
     *   remove_index :accounts, :name => :by_branch_party
     *
     * You can remove an index on multiple columns by specifying the first column.
     *   add_index :accounts, [:username, :password]
     *   remove_index :accounts, :username
     * 
     * @param   string  $tableName
     * @param   array   $options
     */
    public function removeIndex($tableName, $options=array())
    {
        $this->_clearTableCache($tableName);

        $index = $this->indexName($tableName, $options);
        $sql = "DROP INDEX ".$this->quoteColumnName($index)." ON $tableName";
        return $this->execute($sql);
    }

    /**
     * Get the name of the index
     * 
     * @param   string  $tableName
     * @param   array   $options
     */
    public function indexName($tableName, $options=array())
    {
        if (is_array($options)) {
            if (isset($options['column'])) {
                $columns = (array)$options['column'];
                return "index_{$tableName}_on_".implode('_and_', $columns);

            } elseif (isset($options['name'])) {
                return $options['name'];

            } else {
                throw new Mad_Model_Exception("You must specify the index name");
            }
        } else {
            return $this->indexName($tableName, array('column' => $options));
        }
    }


    /**
     * Returns a string of <tt>CREATE TABLE</tt> SQL statement(s) for recreating the
     * entire structure of the database.
     */
    abstract public function structureDump();

    /**
     * Should not be called normally, but this operation is non-destructive.
     * The migrations module handles this automatically.
     */
    public function initializeSchemaInformation()
    {
        try {
            $this->execute("CREATE TABLE schema_info (".
                           "  version ".$this->typeToSql('integer').
                           ")");
            return $this->execute("INSERT INTO schema_info (version) VALUES (0)");
        } catch (Exception $e) {}
    }

    /**
     * The sql for this column type
     * 
     * @param   string  $type
     * @param   string  $limit
     */
    public function typeToSql($type, $limit=null, $precision=null, $scale=null)
    {
        $natives = $this->nativeDatabaseTypes();
        $native = isset($natives[$type]) ? $natives[$type] : null;
        if (empty($native)) { return $type; }

        $sql = is_array($native) ? $native['name'] : $native;
        if ($type == 'decimal') {
            $nativePrec  = isset($native['precision']) ? $native['precision'] : null;
            $nativeScale = isset($native['scale'])     ? $native['scale']     : null;

            $precision = !empty($precision) ? $precision : $nativePrec;
            $scale     = !empty($scale)     ? $scale     : $nativeScale;
            if ($precision) {
                $sql .= $scale ? "($precision, $scale)" : "($precision)";
            }
        } else {
            $nativeLimit = is_array($native) ? $native['limit'] : null;
            if ($limit = !empty($limit) ? $limit : $nativeLimit) {
                $sql .= "($limit)";
            }
        }
        return $sql;
    }

    /**
     * Add default/null options to column sql
     * 
     * @param   string  $sql
     * @param   array   $options
     */
    public function addColumnOptions($sql, $options)
    {
        if ($this->_doOptionsIncludeDefault($options)) {
            $default = isset($options['default']) ? $options['default'] : null;
            $column  = isset($options['column'])  ? $options['column']  : null;
            $sql .= ' DEFAULT '.$this->quote($default, $column);
        }
        if (isset($options['null']) && $options['null'] === false) {
            $sql .= " NOT NULL";
        }
        return $sql;
    }

    /**
     * SELECT DISTINCT clause for a given set of columns and a given 
     * ORDER BY clause. Both PostgreSQL and Oracle override this for
     * custom DISTINCT syntax.
     * 
     * $connection->distinct("posts.id", "posts.created_at desc")
     * 
     * @param   string  $columns
     * @param   string  $orderBy
     */
    public function distinct($columns, $orderBy=null)
    {
        return "DISTINCT $columns";
    }

    /**
     * ORDER BY clause for the passed order option.
     * PostgreSQL overrides this due to its stricter standards compliance.
     * 
     * @param   string  $sql
     * @param   array   $options
     * @return  string
     */
    public function addOrderByForAssocLimiting($sql, $options)
    {
        return $sql.'ORDER BY '.$options['order'];
    }


    /*##########################################################################
    # Protected
    ##########################################################################*/
    
    /**
     * Parse YAML configuration array into options for PDO constructor
     *
     * @throws  Mad_Model_Exception
     * @return  array  [dsn, username, password]
     */
    protected function _parseConfig()
    {
        // check required config keys are present
        $required = array('adapter', 'username', 'password');
        $diff = array_diff_key(array_flip($required), $this->_config);
        if (! empty($diff)) {
            $msg = 'Required config missing: ' . implode(', ', array_keys($diff));
            throw new Mad_Model_Exception($msg);
        }

        // collect options to build PDO Data Source Name (DSN) string
        $dsnOpts = $this->_config;
        unset($dsnOpts['adapter'], $dsnOpts['username'], $dsnOpts['password']);

        // rewrite rails config key names to pdo equivalents
        $rails2pdo = array('database' => 'dbname', 'socket' => 'unix_socket');
        foreach ($rails2pdo as $from => $to) {
            if (isset($dsnOpts[$from])) {
                $dsnOpts[$to] = $dsnOpts[$from];
                unset($dsnOpts[$from]);
            }
        }

        // build DSN string
        $dsn = $this->_config['adapter'] . ':';
        foreach ($dsnOpts as $k => $v) {
            $dsn .= "$k=$v;";
        }
        $dsn = rtrim($dsn, ';');

        // return DSN and user/pass for connection
        return array($dsn,
                     $this->_config['username'],
                     $this->_config['password']);
    }
    
    /**
     * We need to clear cache for tables when altering them at all
     */
    protected function _clearTableCache($tableName)
    {
        $tableCache = MAD_ROOT.'/tmp/cache/tables/'.$tableName.'.php';
        if (file_exists($tableCache)) { unlink($tableCache); }
    }

    /**
     * @param   array   $options
     */
    protected function _doOptionsIncludeDefault($options) 
    {
        $null = isset($options['null']) ? $options['null'] : null;
        return isset($options['default']) && $null !== false;
    }

    /**
     * Logs the SQL query for debugging.
     * 
     * @param   string  $sql
     * @param   string  $name
     * @param   float   $runtime
     */
    protected function _logInfo($sql, $name, $runtime=null) 
    {
        if (! is_null($this->_logger)) {
            $name = (empty($name) ? '' : $name)
                  . (empty($runtime) ? '' : " ($runtime ms)");
            $this->_logger->info($this->_formatLogEntry($name, $sql));
        }
    }

    /**
     * Formats the log entry.
     * 
     * @param   string  $message
     * @param   string  $sql
     */
    protected function _formatLogEntry($message, $sql)
    {
        $sql = preg_replace("/\s+/", ' ', $sql);
        $sql = "\n\t".wordwrap($sql, 70, "\n\t  ", 1);
        return "SQL $message  $sql";
    }
}
