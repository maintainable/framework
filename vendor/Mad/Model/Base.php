<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Object Relation Mapper (ORM) Layer. Tables are represented as classes, rows in
 *  the table correspond to objects from that class, and columns map to the object
 *  attributes. Handles all basic CRUD operations (Create, Read, Update, Delete).
 *
 * Model subclasses should always be created with the generator to ensure creation of
 * all correct components (including data objects, unit tests, and fixtures):
 *
 * <code>
 *  php ./script/generate.php model {ModelName} {table_name}
 * </code>
 *
 * Each model class has a Mad_Model_DO class that maps it's attributes to the database.
 * This is stored under the /app/mappings directory:
 *  model: /app/models/Folder.php
 *  data object: /app/mappings/FolderDO.php.
 *
 * @category   Mad
 * @package    Mad_Model
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
abstract class Mad_Model_Base extends Mad_Support_Object
{
    /*##########################################################################
    # Configuration options
    ##########################################################################*/

    /**
     * Should the table introspection data be cached
     *  - true:  Cache table introspection data to /tmp/cache/tables 
     *  - false: Introspect database table on every request
     */
    public static $cacheTables = true;


    /**
     * Include the root level in json serialization
     */
    public static $includeRootInJson = false;


    /*##########################################################################
    # Connection
    ##########################################################################*/

    /**
     * @var object
     */
    protected static $_connectionSpec;

    /**
     * @var array
     */
    protected static $_activeConnection;

    /**
     * @var Logger
     */
    protected static $_logger;

    /**
     * Database adapter instance
     * @var Mad_Model_ConnectionAdapter_Abstract
     */
    public $connection;


    /*##########################################################################
    # Attributes
    ##########################################################################*/

    /**
     * List of attributes excluded from mass assignment
     * @var array
     */
    protected $_attrProtected = array();

    /**
     * List of attribute name=>value pairs
     * @var array
     */
    protected $_attributes = array();

    /**
     * Name of this class
     * @var string
     */
    protected $_className = null;

    /**
     * Name of the database table
     * @var string
     */
    protected $_tableName = null;

    /**
     * Name of the primary key db column
     * @var string
     */
    protected $_primaryKey = null;

    /**
     * Has subclasses through a types table with class_name column
     * @var boolean
     */
    protected $_inheritanceColumn = 'type';

    /**
     * @var array
     */
    protected $_columns = array();
    
    /**
     * @var array
     */
    protected $_columnsHash = array();
    
    /**
     * @var array
     */
    protected $_columnNames = array();
    
    /**
     * An object cannot allow attribute access once it has been destroyed
     * @var boolean
     */
    protected $_frozen = false;

    /**
     * Is this a new record to be inserted?
     * @var boolean
     */
    protected $_newRecord = true;


    /*##########################################################################
    # Associations
    ##########################################################################*/

    /**
     * Has the association changed (even though the actual model might not have)
     * @var boolean
     */
    protected $_assocChanged = false;

    /**
     * A list of associations for this model define in concrete _initialize()
     * Lazy initialized if an unknown property/method is called
     * 
     * @var array
     */
    protected $_associationList;

    /**
     * The list of association objects for this model
     * Lazy initialized if an unknown property/method is called
     * 
     * @var array
     */
    protected $_associations;

    /**
     * The list of methods that are available for the associations of this model
     * $_associationMethods['createDocument'] = $hasOneAssociationObject;
     * This is lazy initialized if an unknown propery/method is called
     *
     * @var array
     */
    protected $_associationMethods;


    /*##########################################################################
    # Validations
    ##########################################################################*/

    /**
     * The list of validations that thie model enforces before an update/insert
     * @var array
     */
    protected $_validations = array();
    
    /**
     * Should we throw exceptions when validations fail
     * @var array
     */
    protected $_throw = false;

    /**
     * An array of messages stored when validations fail
     * @var array
     */
    public $errors;


    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * Initialize any values given for the model.
     *
     * Load the model by attributes
     * <code>
     *  <?php
     *  ...
     *  $attributes = array('documentname' => 'My Folder',
     *                      'description' => 'My Description');
     *  $folder = new Folder($attributes);
     *  ...
     *  ?>
     * </code>
     *
     * @param   array   $attributes  construct by attribute list
     * @param   array   $options     'include' associations
     * @throws  Mad_Model_Exception
     */
    public function __construct($attributes=null, $options=null)
    {
        $this->_className = get_class($this);

        // establish connection to db
        $this->connection = $this->retrieveConnection();
        $this->errors     = new Mad_Model_Errors($this);

        // Initialize relationships/data-validation from subclass
        $this->_initialize();

        // init table/fields
        $this->_tableName  = $this->tableName();
        $this->_primaryKey = $this->primaryKey();
        $this->_attributes = $this->_attributesFromColumnDefinition();

        // set values by attribute list
        if (isset($attributes)) {
            $this->setAttributes($attributes);
        }
    }

    /**
     * Clone the object without the values. All objects need to be explicitly
     * copied or we get them referencing the same data
     */
    public function __clone()
    {
        // reset attributes, errors, and associations
        $this->_attributes = $this->_attributesFromColumnDefinition();
        $this->errors->clear();
        $this->_resetAssociations();

        // only need to clone validations if they exist
        if (isset($this->_validations)) {
            foreach ($this->_validations as &$validation) {
                $validation = clone $validation;
            }
        }
    }

    /**
     * Initialize relationships and Data validation from subclass
     */
    abstract protected function _initialize();


    /*##########################################################################
    # Magic Accessor methods
    ##########################################################################*/

    /**
     * Dynamically get value for a attribute. Attributes cannot be retrieved once
     * an object has been destroyed.
     *
     * @param   string  $name
     * @return  string
     * @throws  Mad_Model_Exception
     */
    public function _get($name)
    {
        // active-record primary key value
        if ($name == 'id') { $name = $this->primaryKey(); }

        // active-record || attribute-reader value
        if (array_key_exists($name, $this->_attributes)) {
            return $this->readAttribute($name);
        }

        // dynamic attribute added by an association
        $this->_initAssociations();
        if (isset($this->_associationMethods[$name])) {
            return $this->_associationMethods[$name]
                        ->callMethod($name, array());

        // unknown attribute
        } else {
            throw new Mad_Model_Exception("Unrecognized attribute '$name'");
        }
    }

    /**
     * Dynamically set value for a attribute. Attributes cannot be set once an
     * object has been destroyed. Primary Key cannot be changed if the data was
     * loaded from a database row
     *
     * @param   string  $name
     * @param   mixed   $value
     * @throws  Mad_Model_Exception
     */
    public function _set($name, $value)
    {
        if ($this->_frozen) {
            $msg = "You cannot set attributes of a destroyed object";
            throw new Mad_Model_Exception($msg);
        }
        // active-record primary key value
        if ($name == 'id') { $name = $this->primaryKey(); }

        // cannot change pk if it's already set
        if (($name == $this->primaryKey()) && !$this->isNewRecord()) {
            // ignore assignment of pk so that this works with activeresource
            return;
        }

        // active-record || attribute-reader value
        if (array_key_exists($name, $this->_attributes)) {
            return $this->writeAttribute($name, $value);
        }

        // dynamic attribute added by an association
        $this->_initAssociations();
        if (isset($this->_associationMethods[$name.'='])) {
            return $this->_associationMethods[$name.'=']
                        ->callMethod($name.'=', array($value));

        // unknown attribute
        } else {
            throw new Mad_Model_Exception("Unrecognized attribute '$name'");
        }
    }

    /**
     * Allows testing with empty() and isset() to work inside templates
     *
     * @param   string  $key
     * @return  boolean
     */
    public function _isset($name)
    {
        // association methods
        $this->_initAssociations();
        if (isset($this->_associationMethods[$name])) {
            return count($this->_get($name)) > 0;

        // active-record attribue
        } else {
            return isset($this->_attributes[$name]);
        }

        return isset($this->_attributes[$name]);
    }

    /**
     * Association methods are added at runtime and use dynamic methods.
     *
     * @param   string  $name
     * @param   array   $args
     */
    public function __call($name, $args)
    {
        // dynamic attribute added by an association
        $this->_initAssociations();
        if (isset($this->_associationMethods[$name])) {
            return $this->_associationMethods[$name]->callMethod($name, $args);

        // unknown method
        } else {
            throw new Mad_Model_Exception("Unrecognized method '$name'");
        }
    }

    /**
     * Print out a string describing this object's attributes
     *
     * @return  string
     */
    public function __toString()
    {
        foreach ($this->_attributes as $name => $value) {
            $str[] = "$name => ".(isset($value) ? "'$value'" : 'null');
        }
        return isset($str) ? "\n".$this->_className." Object: \n".join(", \n", $str) : null;
    }
    
    /*##########################################################################
    # Serialization
    ##########################################################################*/
    
    /**
     * Serialize only needs attributes
     */
    public function __sleep()
    {
        return array('_attributes', '_attrReaders', 
                     '_attrWriters', '_attrValues');
    }

    /**
     * Enables models to be used as URL parameters for routes automatically.
     *
     * @return null|string
     */
    public function toParam()
    {
        $pk = $this->primaryKey();

        if ($pk && isset($this->_attributes[$pk])) {
            return (string)$this->_attributes[$pk];
        } 
    }


    /*##########################################################################
    # Logger
    ##########################################################################*/

    /**
     * Set a logger object, defaulting to mad_default_logger. This needs to 
     * reset connection so that the correct log is passed to the connection 
     * adapter. 
     * 
     * @param   object  $logger
     */
    public static function setLogger($logger=null)
    {
        self::$_logger = isset($logger) ? $logger : $GLOBALS['MAD_DEFAULT_LOGGER'];
        self::establishConnection(self::removeConnection());
    }

    /**
     * Returns the logger object.
     * 
     * @return  object
     */
    public static function logger()
    {
        // set default logger 
        if (!isset(self::$_logger)) {
            self::setLogger();
        }
        return self::$_logger;
    }


    /*##########################################################################
    # Connection Management
    ##########################################################################*/

    /**
     * Establishes the connection to the database. Accepts a hash as input where
     * the :adapter key must be specified with the name of a database adapter (in lower-case)
     * 
     * Example for regular databases (MySQL, Postgresql, etc):
     * <code>
     *   Mad_Model_Base::establishConnection(array(
     *     "adapter"  => "mysql",
     *     "host"     => "localhost",
     *     "username" => "myuser",
     *     "password" => "mypass",
     *     "database" => "somedatabase"
     *   ));
     * </code>
     *
     * Example for SQLite database:
     * <code>
     *   Mad_Model_Base::establishConnection(array(
     *     "adapter"  => "sqlite",
     *     "database" => "path/to/dbfile"
     *   ));
     * </code>
     *
     * The exceptions AdapterNotSpecified, AdapterNotFound and ArgumentError
     * may be returned on an error.
     * 
     * @param   array   $spec
     * @return  Connection
     */
    public static function establishConnection($spec=null)
    {
        // $spec is empty: $spec defaults to MAD_ENV string like "development"
        // keep going to read YAML for this environment string
        if (empty($spec)) {
            if ( !defined('MAD_ENV') || !MAD_ENV ) {
                throw new Mad_Model_Exception('Adapter Not Specified');
            }
            $spec = MAD_ENV;
        } 

        // $spec is string: read YAML config for environment named by string
        // keep going to process the resulting array
        if (is_string($spec)) {
            $config = Horde_Yaml::loadFile(MAD_ROOT.'/config/database.yml');
            $spec = $config[$spec];
        }

        // $spec is an associative array            
        if (is_array($spec)) {
          
            // validation of array is handled by horde_db
            self::$_connectionSpec = $spec;

        } else {
            throw new Mad_Model_Exception("Invalid Connection Specification");
        }
    }

    /**
     * Returns true if a connection that's accessible to this class have already 
     * been opened.
     * 
     * @return  boolean
     */
    public static function isConnected()
    {
        return isset(self::$_activeConnection);
    }

    /**
     * Locate/Activate the connection
     * 
     * @return  Mad_Model_ConnectionAdapter_Abstract
     */
    public static function retrieveConnection()
    {
        // already have active connection
        if (self::$_activeConnection) {
            $conn = self::$_activeConnection;

        // connection based on spec
        } elseif ($spec = self::$_connectionSpec) {
            if (empty($spec['logger'])) { 
                $spec['logger'] = self::logger(); 
            }
            $adapter = Horde_Db_Adapter::getInstance($spec);

            $conn = self::$_activeConnection = $adapter; 
        }

        if (empty($conn)) {
            throw new Mad_Model_Exception("Connection Not Established");
        }
        return $conn;
    }

    /**
     * Remove the connection for this class. This will close the active
     * connection and the defined connection (if they exist). The result
     * can be used as argument for establishConnection, for easy
     */
    public static function removeConnection()
    {
        $spec = self::$_connectionSpec;
        $conn = self::$_activeConnection;

        self::$_connectionSpec   = null;
        self::$_activeConnection = null;

        if ($conn) { $conn->disconnect(); }
        return $spec ? $spec : '';
    }

    /**
     * Returns the connection currently associated with the class. This can
     * also be used to "borrow" the connection to do database work unrelated
     * to any of the specific Active Records.
     * 
     * @return  Mad_Model_ConnectionAdapter_Abstract
     */
    public static function connection() 
    {
        if (self::$_activeConnection) {
            return self::$_activeConnection;
        } else {
            return self::$_activeConnection = self::retrieveConnection();
        }
    }


    /*##########################################################################
    # DB Table column/keys
    ##########################################################################*/

    /**
     * Get the name of the table
     * @return  string
     */
    public function tableName()
    {
        if (isset($this->_tableName)) {
            return $this->_tableName;
        } else {
            return $this->resetTableName();
        }
    }
    
    /**
     * Reset the table name based on conventions
     * 
     */
    public function resetTableName()
    {
        return $this->_tableName = 
            Mad_Support_Inflector::tableize($this->baseClass());
    }

    /**
     * Get the name of the primary key column
     * @return  string
     */
    public function primaryKey()
    {
        if (isset($this->_primaryKey)) {
            return $this->_primaryKey;
        } else {
            return $this->resetPrimaryKey();
        }
    }
    
    /**
     * Rest primary key name based on conventions. 
     */
    public function resetPrimaryKey()
    {
        return $this->_primaryKey = 'id';
    }

    /**
     * Get class name column used for single-table inheritance
     *
     * @return  string
     */
    public function inheritanceColumn()
    {
        return $this->_inheritanceColumn;
    }

    /**
     * Set the name of the table for the model
     * @param   string  $table
     */
    public function setTableName($value)
    {
        $this->_tableName = $value;
    }

    /**
     * Set the name of the table for the model
     * @param   string  $value
     */
    public function setPrimaryKey($value)
    {
        $this->_primaryKey = $value;
    }

    /**
     * Change the default column used for single-table inheritance
     * @param  string  $col
     */
    public function setInheritanceColumn($col)
    {
        $this->_inheritanceColumn = $col;
    }

    /**
     * Returns an array of column objects for the table associated 
     * with this class.
     * 
     * @return  array
     */
    public function columns()
    {
        if (empty($this->_columns)) {
            $this->_columns = $this->connection->columns($this->tableName(), 
                                                  "$this->_className Columns");
            foreach ($this->_columns as $col) {
                $col->setPrimary($col->getName() == $this->_primaryKey);
            }
        }
        return $this->_columns;
    }

    /**
     * Returns a hash of column objects for the table associated with 
     * this class.
     * 
     * @return  array
     */
    public function columnsHash()
    {
        if (empty($this->_columnsHash)) {
            foreach ($this->columns() as $col) {
                $this->_columnsHash[$col->getName()] = $col;
            }
        }
        return $this->_columnsHash;
    }

    /**
     * Returns an array of column names as strings.
     * 
     * @return  array
     */
    public function columnNames()
    {
        if (empty($this->_columnNames)) {
            foreach ($this->columns() as $col) {
                $this->_columnNames[] = $col->getName();
            }
        }
        return $this->_columnNames;
    }

    /**
     * Reset the column info
     */
    public function resetColumnInformation()
    {
        $this->_columns     = $this->_columnsHash = 
        $this->_columnNames = $this->_inheritanceColumn = null;
    }

    /**
     * Get the base class for this model. Defined by subclass
     * 
     * @return  string
     */
    public function baseClass()
    {
        // go up single hierarchy if this is an STI model
        $parentClass = get_parent_class($this);
        if ($parentClass != 'Mad_Model_Base') {
            return $parentClass; 
        }
        return $this->_className;
    }


    /*##########################################################################
    # Attributes
    ##########################################################################*/

    /**
     * Set list of attributes protected from mass assignment
     * 
     * @todo implement this in save statements
     * @param   string  $attribute
     */
    public function attrProtected($attributes)
    {
        $names = func_get_args();
        $this->_attrProtected = array_unique(
            array_merge($this->_attrProtected, $names));
    }

    /**
     * Get the value for an attribute in this model
     * 
     * @param   string  $name
     * @return  string
     */
    public function readAttribute($name)
    {
        // active-record attributes
        if (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];

        // no value set yet
        } else {
            return null;
        }
    }

    /**
     * Set the value for an attribute in this model
     * 
     * @param   string  $name
     * @param   mixed   $value
     */
    public function writeAttribute($name, $value)
    {
        // active-record attributes
        if (array_key_exists($name, $this->_attributes)) {
            $this->_attributes[$name] = $value;
        }
    }

    /**
     * Get the human attribute name for a given attribute
     * 
     * @return  string
     */
    public function humanAttributeName($attr)
    {
        $col = $this->columnForAttribute($attr);
        return Mad_Support_Inflector::humanize($col->getName());
    }

    /**
     * Get the array of attribute fields
     * @return  array
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }
    
    /** 
     * Mass assign attributes for this model
     * @param   array   $attributes
     */
    public function setAttributes($attributes = array())
    {
        // Set attributes by array
        if (is_array($attributes)) {
            foreach ($attributes as $attribute => $value) {
                $this->$attribute = $value;
            }
        // Set primary key (Beware this does not instantiate other properties)
        } elseif (is_numeric($attributes)) {
            $this->{$this->primaryKey()} = $attributes;
        }
    }

    /**
     * Finder methods must instantiate through this method to work with the
     * single-table inheritance model that makes it possible to create
     * objects of different types from the same table.
     * 
     * @param   array   $record
     */
    public function instantiate($record)
    {
        // single table inheritance 
        $column = $this->inheritanceColumn();
        if (isset($record[$column]) && $className = $record[$column]) {
            if (!class_exists($className)) {
                $msg = "The single-table inheritance mechanism failed to ".
                 "locate the subclass: '$className'. This error is raised ".
                 "because the column '$column' is reserved for storing the ".
                 "class in case of inheritance. Please rename this column ".
                 "if you didn't intend it to be used for storing the ".
                 "inheritance class.";
                throw new Mad_Model_Exception($msg);
            }
            $model = new $className;
        } else {
            $model = clone $this;
        }
        return $model->setValuesByRow($record);
    }

    /**
     * Set the values for this object using a db result set.
     *
     * <code>
     *  <?php
     *  ...
     *  $folder = new Folder();
     *  $row = $result->fetchRow();
     *  $folder->setValuesByRow($row)
     *  ...
     *  ?>
     * </code>
     *
     * @param   array   $dbValues
     * @return  Mad_Model_Base
     */
    public function setValuesByRow($values)
    {
        // active-record attributes
        foreach ($this->_attributes as $name => $value) {
            if (array_key_exists($name, $values)) {
                $this->writeAttribute($name, $values[$name]);
            }
        }
        // attr-writers
        foreach ($this->_attrWriters as $name) {
            if (array_key_exists($name, $values)) {
                $this->$name = $values[$name];
            }
        }
        // this isn't a new record if we've loaded it from the db
        $this->_newRecord = false;
        return $this;
    }


    /**
     * Returns an array of names for the attributes available on this 
     * object sorted alphabetically.
     * 
     * @return  array
     */
    public function attributeNames()
    {
        $attrs = array_keys($this->_attributes);
        sort($attrs);
        return $attrs;
    }

    /**
     * Returns the column object for the named attribute
     * 
     * @param   string  $name
     * @return  object
     */
    public function columnForAttribute($name)
    {
        $colHash = $this->columnsHash();
        return $colHash[$name];
    }


    /*##########################################################################
    # Deprecated column accessors
    ##########################################################################*/

    /**
     * Get an array of columns
     * @deprecated
     * @param   string  $tblAlias prepend table alias to columns
     * @param   boolean $colAlias  Generate column aliases for TO_CHAR()s
     * @return  array
     */
    public function getColumns($tblAlias=null, $colAlias=true)
    {
        $tblAlias = isset($tblAlias) ? "$tblAlias." : null;
        foreach ($this->_attributes as $name => $value) {
            $cols[] = $tblAlias.strtolower($name);
        }
        return isset($cols) ? $cols : array();
    }

    /**
     * Construct the column string from the columns. Convert timestamps to string (TO_CHAR)
     * @deprecated
     * @param   string  $tblAlias  prepend table alias to columns
     * @param   boolean $colAlias  Generate column aliases for TO_CHAR()s
     * @return  string
     */
    public function getColumnStr($tblAlias=null, $colAlias=true)
    {
        foreach ($this->getColumns($tblAlias, $colAlias) as $col) {
            $parts = explode('.', $col);
            // has table alias
            if (isset($parts[1])) {
                $quoted[] = $this->connection->quoteColumnName($parts[0]).'.'.
                            $this->connection->quoteColumnName($parts[1]);
            // column only
            } else {
                $quoted[] = $this->connection->quoteColumnName($parts[0]);
            }
        }

        return join(', ', $quoted);
    }

    /**
     * Get the insert values string from the columns.
     * @deprecated
     * @return  string
     */
    public function getInsertValuesStr() 
    {
        $vals = array();
        foreach ($this->_attributes as $name => $value) {
            $vals[] = $this->_quoteValue($value);
        }
        return join(', ', $vals);
    }


    /*##########################################################################
    # Associations
    ##########################################################################*/

    /**
     * Returns the Association object for the named association
     * 
     * @param   string  $name
     * @return  Mad_Model_Association_Base
     */ 
    public function reflectOnAssociation($name)
    {
        $this->_initAssociations();
        if (! isset($this->_associations[$name])) {
            throw new Mad_Model_Exception("Association $name does not exist for ".get_class($this));
        }

        return $this->_associations[$name];
    }

    /**
     * Since the value associated with the association has change, force it to
     * reload
     */
    public function reloadAssociation($name)
    {
        if (isset($this->_associationMethods)) {
            $this->_associationMethods = null;
            $this->_associations       = null;
        }
    }

    /**
     * Set as association as being loaded
     * @param   string  $name
     */
    public function setAssociationLoaded($name)
    {
        $this->_initAssociations();
        if (isset($this->_associations[$name])) {
            $this->_associations[$name]->setLoaded();
        }
    }


    /*##########################################################################
    # CRUD Class methods
    ##########################################################################*/

    /**
     * <b>FIND BY PRIMARY KEY.</b>
     *
     * <code>
     *  $binder  = Binder::find(123);
     *  $binders = Binder::find(array(123, 234));
     * </code>
     *
     *
     * <b>FIND ALL</b>
     *
     * Retrieve using WHERE conditions using SQL:
     * <code>
     *  $binders = Binder::find('all', array(
     *                                 'conditions' => "name = 'Stubbed Images'")
     *                         );
     * </code>
     *
     * Retrieve using WHERE conditions and LIMIT:
     * <code>
     *  $binders = Binder::find('all', array('conditions' => 'name = :name',
     *                                       'order'      => 'name DESC'
     *                                       'limit'      => 10),
     *                                 array(':name' => 'Stubbed Images'));
     * </code>
     *
     * Retrieve using WHERE conditions and OFFSET (same as mysql LIMIT 20, 10):
     * <code>
     *  $binders = Binder::find('all', array('conditions' => 'name = :name',
     *                                       'order'      => 'name DESC'
     *                                       'offset'     => 20,
     *                                       'limit'      => 10),
     *                                 array(':name' => 'Stubbed Images'));
     * </code>
     *
     * Retrieve using WHERE conditions and FROM tables:
     * <code>
     *  $folders = Folder::find('all', array('conditions' => 'f.folderid=d.parent_folderid',
     *                                       'from'       => 'folders f, documents d'));
     * </code>
     *
     *
     * <b>FIND FIRST</b>
     *
     * Find the first record that matches the given criteria. (same options as find('all')
     * <code>
     *  $binder = Binder::find('first', array('conditions' => 'f.folderid=d.parent_folderid',
     *                                        'from'       => 'folders f, documents d'));
     * </code>
     *
     *
     * @param   mixed   $type    (pk/pks/all/first/count)
     * @param   array   $options
     * @param   array   $bindVars
     * @throws  Mad_Model_Exception_RecordNotFound
     */
    public static function find($type, $options=null, $bindVars=null)
    {
        // hack to get name of this class (because of static)
        $bt = debug_backtrace();
        $m = new $bt[1]['class'];
        return $m->_find($type, $options, $bindVars);
    }

    /**
     * Count how many records match the given criteria
     * <code>
     *  $binderCnt = Binder::count(array('name' => 'Stubbed Images'));
     * </code>
     */
    public static function count($options=null, $bindVars=null) 
    {
        // hack to get name of this class (because of static)
        $bt = debug_backtrace();
        $m = new $bt[1]['class'];
        return $m->_count($options, $bindVars);
    }

    /**
     * This method provides an interface for finding records using direct sql instead of
     * the componentized api of find(). This is however not always desired as find() does
     * some magic that this method cannot do.
     *
     * <b>FIND ALL RECORDS BY SQL</b>
     *
     * <code>
     *  $sql = 'SELECT *
     *            FROM briefcases
     *           WHERE name=:name';
     *  $collection = Binder::findBySql('all', $sql, array(':name'=>'Stubbed Images'));
     * </code>
     *
     *
     * <b>FIND FIRST RECORD BY SQL</b>
     *
     * <code>
     *  $sql = 'SELECT *
     *            FROM briefcases
     *           WHERE name=:name';
     *  $binder = Binder::findBySql('first', $sql, array(':name'=>'Stubbed Images'));
     * </code>
     *
     *
     * @param   string  $type
     * @param   string  $sql
     * @param   array   $bindVars
     */
    protected static function findBySql($type, $sql, $bindVars=null)
    {
        // hack to get name of this class (because of static)
        $bt = debug_backtrace();
        $m = new $bt[1]['class'];
        return $m->_findBySql($type, $sql, $bindVars);
    }

    /**
     * This method provides an interface for counting records using direct sql 
     * instead of the componentized api of find(). This is however not always 
     * desired as find() does some magic that this method cannot do.
     *
     * <b>COUNT RECORDS BY SQL</b>
     *
     * <code>
     *  $sql = 'SELECT COUNT(1)
     *            FROM briefcases
     *           WHERE name=:name';
     *  $binder = Binder::countBySql($sql, array(':name'=>'Stubbed Images'));
     * </code>
     *
     * @param   string  $sql
     * @param   array   $bindVars
     */
    protected static function countBySql($sql, $bindVars=null)
    {
        // hack to get name of this class (because of static)
        $bt = debug_backtrace();
        $m = new $bt[1]['class'];
        return $m->_countBySql($sql, $bindVars);
    }

    /**
     * Paginate records for find()
     * 
     * @param   array   $options
     * @param   array   $bindVars
     * @return  Mad_Model_Collection
     */
    protected static function paginate($options=null, $bindVars=null)
    {
        // hack to get name of this class (because of static)
        $bt = debug_backtrace();
        $m = new $bt[1]['class'];
        return $m->_paginate($options, $bindVars);
    }

    /**
     * Check if this record exists.
     *
     * <code>
     *  $folderExists = Folder::exists(123);
     * </code>
     *
     * @param   int     $id
     * @return  boolean
     */
    public static function exists($id)
    {
        // hack to get name of this class (because of static)
        $bt = debug_backtrace();
        $m = new $bt[1]['class'];
        return $m->_exists($id);
    }

    /**
     * Create a new record in the db from the attributes of the model
     *
     * Create single record
     * <code>
     *  $binder = Binder::create(array('name' => "derek's binder"));
     * </code>
     *
     * Create multiple records
     * <code>
     *  $binders = Binder::create(array(array('name' => "derek's binder"),
     *                                  array('name' => "dallas' binder")));
     * </code>
     *
     * @param   array   $attributes
     * @return  mixed   single model object OR array of model objects
     */
    public static function create($attributes)
    {
        // hack to get name of this class (because of static)
        $bt = debug_backtrace();
        $m = new $bt[1]['class'];
        return $m->_create($attributes);
    }

    /**
     * Update record in the db directly by pk or array of pks
     *
     * Single record update
     * <code>
     *  $binder = Binder::update(123, array('name' => 'My new name'));
     * </code>
     *
     * Multiple record update
     * <code>
     *  $binders = Binder::update(array(123, 456), array('name' => 'My new name'));
     * </code>
     *
     * @param   int     $id
     * @param   array   $attributes
     * @return  void
     */
    public static function update($id, $attributes=null)
    {
        // hack to get name of this class (because of static)
        $bt = debug_backtrace();
        $m = new $bt[1]['class'];
        return $m->_update($id, $attributes);
    }

    /**
     * Delete record(s) from the database by primary key
     *
     * Delete single record
     * <code>
     *  Binder::delete(123);
     * </code>
     *
     * Delete multiple records
     * <code>
     *  Binder::delete(array(123, 234));
     * </code>
     *
     * @param   mixed   $id (int or array of ints)
     * @return  void
     */
    public static function delete($id)
    {
        // hack to get name of this class (because of static)
        $bt = debug_backtrace();
        $m = new $bt[1]['class'];
        return $m->_delete($id);
    }

    /**
     * Update multiple records that match the given conditions
     *
     * <code>
     *  Binder::update("description = 'my tests'", 'name = :name',
     *                  array(':name' => 'My test binder'));
     * </code>
     *
     * @param   string  $set
     * @param   string  $conditions
     * @param   array   $bindVars
     * @return  void
     */
    public static function updateAll($set, $conditions=null, $bindVars=null)
    {
        // hack to get name of this class (because of static)
        $bt = debug_backtrace();
        $m = new $bt[1]['class'];
        return $m->_updateAll($set, $conditions, $bindVars);
    }

    /**
     * Delete multiple records that match the given conditions
     *
     * <code>
     *  Binder::delete('name = :name', array(':name' => 'My test binder'));
     * </code>
     *
     * @param   string  $conditions
     * @param   array   $bindVars
     */
    public static function deleteAll($conditions=null, $bindVars=null)
    {
        // hack to get name of this class (because of static)
        $bt = debug_backtrace();
        $m = new $bt[1]['class'];
        return $m->_deleteAll($conditions, $bindVars);
    }


    /*##########################################################################
    # CRUD Instance methods
    ##########################################################################*/

    /**
     * Save data stored in memory (the object) back into the database. Performs either
     * an insert or an update depending on if this is a new record
     *
     * Insert a row
     * <code>
     *  $binder = new Binder(array('name' => "Derek's binder"));
     *  $binder->save();
     * </code>
     *
     * Update a row
     * <code>
     *  $binder = Binder::find(123);
     *  $binder->name = "Derek's updated binder";
     *  $binder->save();
     * </code>
     *
     * @return  mixed   boolean or Mad_Model_Base
     * @throws  Mad_Model_Exception_Validation
     */
    public function save()
    {
        // All saves are atomic - only start transaction if one hasn't been
        $started = $this->connection->transactionStarted();
        if (!$started) { $this->connection->beginDbTransaction(); }

        try {
            // save associated models this model depends on & validate data
            $this->_saveAssociations('before');
            $this->_validateData();

            $this->_createOrUpdate();

            $this->_saveAssociations('after');
            $this->_newRecord = false;

            if (!$started) { $this->connection->commitDbTransaction(); }

            $this->_throw = false;
            return $this;

        } catch (Exception $e) {
            $this->connection->rollbackDbTransaction();
            if ($this->_throw) { 
                $this->_throw = false;
                throw $e; 
            }
            return false;
        }
    }

    /**
     * Attempts to save the record, but instead of just returning false if it 
     * couldn't happen, it throws a Mad_Model_Exception_Validation
     * 
     * @see Mad_Model_Base::save()
     * 
     * @return  object
     * @throws  Mad_Model_Exception_Validation
     */
    public function saveEx()
    {
        $this->_throw = true;
        $this->save();
    }

    /**
     * Update specific attributes for the current object
     *
     * Update single attribute
     * <code>
     *  $binder = Binder::find(123);
     *  $binder->updateAttributes('name', 'My New Briefcase');
     * </code>
     *
     * @param   string  $name
     * @param   string  $value
     * @return void
     */
    public function updateAttribute($name, $value)
    {
        $this->$name = $value;
        return $this->save();
    }

    /**
     * Update multiple attributes for the current object
     *
     * Update multiple attributes
     * <code>
     *  $binder = Binder::find(123);
     *  $binder->updateAttributes(array('name'        => 'The new name',
     *                                  'description' => 'The new description'));
     * </code>
     *
     * @param  array|Traversable   $attributes
     * @return void
     */
    public function updateAttributes($attributes)
    {
        if (! is_array($attributes)) {
            if (! $attributes instanceof Traversable) {
                return false;
            }
        }

        foreach ($attributes as $attribute => $value) {
            $this->$attribute = $value;
        }
        return $this->save();
    }

    /**
     * Destroy a record (delete from db)
     *
     * A custom implementation of destroy() can be written for a model by overriding
     * the _destroy() method. This will ensure that all callbacks are still executed
     *
     * <code>
     *  $binder = Binder::find(123);
     *  $binder->destroy();
     * </code>
     *
     * @return  boolean
     */
    public function destroy()
    {
        // All deletes are atomic
        $started = $this->connection->transactionStarted();
        if (!$started) { $this->connection->beginDbTransaction(); }

        try {
            $this->_beforeDestroy();
            $this->_destroy();
            $this->_afterDestroy();

            if (!$started) { $this->connection->commitDbTransaction(); }            
            return true;

        } catch (Exception $e) {
            $this->connection->rollbackDbTransaction(false);
            return false;
        }
    }

    /**
     * Replace bind variables in the sql string. 
     * 
     * @param   string  $sql
     * @param   array   $bindVars
     */
    public function sanitizeSql($sql, $bindVars)
    {
        preg_match_all("/(:\w+)/", $sql, $matches);
        if (!isset($matches[1])) return;

        foreach ($matches[1] as $replacement) {
            if (!isset($bindVars[$replacement])) {
                $msg = "missing value for $replacement in $sql";
                throw new Mad_Model_Exception($msg);
            }
            $sql = str_replace(
                $replacement, 
                $this->_quoteValue($bindVars[$replacement]), 
                $sql
            );
        }
        return $sql;
    }

    /**
     * Reload values from the database
     */
    public function reload()
    {
        $model = $this->find($this->id);
        foreach ($model->getAttributes() as $name => $value) {
            $this->writeAttribute($name, $value);
        }

        // reset associations
        if (isset($this->_associations)) {
            foreach ($this->_associations as $assoc) {
                $assoc->setLoaded(false);
            }
        }
        
        return $this;
    }

    /**
     * Check if this is a record that hasn't been inserted yet
     *
     * @return  boolean
     */
    public function isNewRecord()
    {
        return $this->_newRecord;
    }

    /**
     * This flag allows us to set explicitly that the association has changed and needs
     * to be saved even if the object itself hasn't been changed
     *
     * @param   boolean $assocSaved
     */
    public function setIsAssocChanged($assocChanged=true)
    {
        $this->_assocChanged = $assocChanged;
    }

    /**
     * Check if the association has changed
     *
     * @return  boolean
     */
    public function isAssocChanged()
    {
        return $this->_assocChanged;
    }

    /**
     * Check if this object is frozen for modification
     *
     * @return  boolean
     */
    public function isDestroyed()
    {
        return $this->_frozen;
    }


    /*##########################################################################
    # Associations - These are set in _initialize() method of subclass
    ##########################################################################*/

    /**
     * This defines a one-to-one relationship with another model class. It declares
     * that the given class has a parent relationship to this model.
     *
     * The foreign key must be specified in the options of the declaration
     * using 'foreignKey'
     *
     * For Document model
     * <code>
     * <?php
     *  ...
     *  protected function _initialize()
     *  {
     *      // specify that the Document has a parent Folder
     *      $this->belongsTo('Folder', array('foreignKey' => 'parent_folderid'));
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * When we specify this relationship, special attributes and methods are dynamically
     * added to the Document model.
     *
     *
     * Access the parent folder. This performs a query to get the parent folder
     * object of the document.
     *
     * <code>
     *  <?php
     *  ...
     *  // the very verbose..
     *  $folderId = $document->parent_folderid;
     *  $parentFolder = Folder::find($folderId);
     *  $folderName = $parentFolder->folder_name;
     *
     *  // can now be simply written as
     *  $folderName = $document->folder->folder_name;
     *  ...
     *  ?>
     * </code>
     *
     * The parent class name is assumed to be the mixed-case singular form of the
     * class name. The association name however can be defined as any name you wish
     * by specifying 'className' option.
     *
     * For Document model
     * <code>
     * <?php
     *  ...
     *  protected function _initialize()
     *  {
     *      // specify that the Document has a parent Folder
     *      $this->belongsTo('Parent', array('foreignKey' => 'parent_folderid',
     *                                  array('className'  => 'Folder')));
     *  }
     *  ...
     *  // now we can access the property using the name 'parent'
     *  $parentFolder = $document->parent;
     *  ...
     *  ?>
     * </code>
     *
     * @param   string  $associationId
     * @param   array   $options
     */
    protected function belongsTo($associationId, $options=null)
    {
        $this->_addAssociation('belongsTo', $associationId, $options);
    }

    /**
     * This defines a one-to-one relationship with another model class. It declares
     * that a given class is a child of this model.
     *
     * The foreign key must be specified in the options of the declaration using
     * 'foreignKey'. This declaration defines the same set of methods in the model
     * object as belongsTo, So given the MdMetadata class example..
     *
     * Any given metadata can have a single icon associated with it
     *
     * For MdMetadata model
     * <code>
     * <?php
     *  ...
     *  protected function _initialize()
     *  {
     *      // specify that the Metadata has an associated metadata icon
     *      $this->hasOne('MdIcon');
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * Now we can refer to the new object through the association
     * <code>
     *  <?php
     *  ...
     *  // the very verbose..
     *  $metadataId = $metadata->metadataid;
     *  $mdIcon = MdIcon::find($metadataId);
     *  $altText = $mdIcon->alt_text;
     *
     *  // can now be simply written as
     *  $altText = $metadata->mdIcon->alt_text;
     *  ...
     *  ?>
     * </code>
     *
     * The child class name is assumed to be the mixed-case singular form of the
     * class name. The association name however can be defined as any name you wish
     * by specifying 'className' option similar to belongsTo().
     *
     * Another options available to hasOne is 'dependent'. You can define if the associated
     * object is dependent on this object existing. This can be one of two options,
     *  1. destroy (the default)
     *  2. nullify
     *
     * A metadata Icon can't exist without it's associated metadata. Because of this, we
     * can tell metadata to destroy all metadata icons before
     *
     * @see Mad_Model_Base::belongsTo()
     *
     * @param   string  $associationId
     * @param   array   $options
     */
    protected function hasOne($associationId, $options=null)
    {
        $this->_addAssociation('hasOne', $associationId, $options);
    }

    /**
     * This defines a one-to-many relationship with another model class.
     * Define an attribute that behaves like a collection of the child objects.
     *
     * The foreign key must be  specified in the options of the declaration using
     * 'foreignKey'. Ordering of children objects can also be specified using the
     * 'order' option.
     *
     * The child class name is assumed to be the mixed-case plural form of the
     * class name. The association name however can be defined as any name you wish
     * by specifying 'className' option similar to belongsTo()
     *
     * For Folder model with multiple documents
     * <code>
     * <?php
     *  ...
     *  protected function _initialize()
     *  {
     *      // specify that the Document has a parent Folder
     *     $this->hasMany('Documents', array('foreignKey' => 'parent_folderid',
     *                                        'order'      => 'document_path'));
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * Now we can refer to the new object through the association
     * <code>
     *  <?php
     *  ...
     *  // the very verbose..
     *  $folderId = $folder->folderid;
     *  $documents = Document::find('all',
     *                         array('conditions' => 'parent_folderid=:id'),
     *                         array(':id' => $folderId));
     *  foreach ($documents as $document) {
     *      print $document->document_name;
     *  }
     *
     *  // can now be simply written as
     *  foreach ($folder->documents as $document) {
     *      print $document->document_name;
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * @see Mad_Model_Base::belongsTo()
     * @param   string  $associationId
     * @param   array   $options
     */
    protected function hasMany($associationId, $options=null)
    {
        $this->_addAssociation('hasMany', $associationId, $options);
    }

    /**
     * This defines a many-to-many relationship with another model class. It acts
     * in many ways similar to hasMany(), but allows us to specify an association table
     * between the two associated classes.
     *
     * The join table must be specified using the 'joinTable' option. The foreign keys
     * in the join table will be assumed to be the same name as the primary key from
     * the two respective tables. If this is not the case, the foreign key columns can
     * be specified using the 'foreignKey' or 'associationForeignKey' options. Ordering
     * of children objects can also be specified using the 'order' option.
     *
     * The child class name is assumed to be the mixed-case plural form of the
     * class name. The association name however can be defined as any name you wish
     * by specifying 'className' option similar to belongsTo()
     *
     * For Folder model with multiple documents
     * <code>
     * <?php
     *  ...
     *  protected function _initialize()
     *  {
     *      // specify that a briefcase has many documents,
     *      // and also belongs to many documents
     *      $this->hasAndBelongsToMany('Documents',
     *                            array('joinTable' => 'briefcase_documents',
     *                                  'order' => 'briefcase_documents.ordering'));
     *
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * If the foreign key names didn't match our convention, we'd have to specify them
     * as follows:
     *
     * <code>
     * <?php
     *  ...
     *  protected function _initialize()
     *  {
     *      // specify that a briefcase has many documents,
     *      // and also belongs to many documents
     *      $this->hasAndBelongsToMany('Documents',
     *                            array('joinTable'  => 'briefcase_documents',
     *                                  'foreignKey' => 'briefcaseid',
     *                                  'associationForeignKey' => 'documentid',
     *                                  'order' => 'briefcase_documents.ordering'));
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * Now we can refer to the new object through the association
     * <code>
     *  <?php
     *  ...
    *  // the very verbose..
     *  $id = $binder->briefcaseid;
     *  $documents = Document::find('all',
     *                         array('select' => 'd.*',
     *                               'from'   => 'documents d, briefcase_documents bd',
     *                               'conditions' => 'd.documentid=bd.documentid
     *                                            AND bd.briefcaseid=:id'),
     *                         array(':id' => $id));
     *  foreach ($documents as $document) {
     *      print $document->document_name;
     *  }
     *
     *  // can now be simply written as
     *  foreach ($binder->documents as $document) {
     *      print $document->document_name;
     *  }
     *  ...
     *  ?>
     * </code>
     *
     * @see Mad_Model_Base::belongsTo()
     * @param   string  $associationId
     * @param   array   $options
     */
    protected function hasAndBelongsToMany($associationId, $options=null)
    {
        $this->_addAssociation('hasAndBelongsToMany', $associationId, $options);
    }


    /*##########################################################################
    # Validation - These are set in _initialize() method of subclass
    ##########################################################################*/

    /**
     * Check for errors, and throw exception if found
     * @throws  Mad_Model_Exception_Validation
     */
    protected function checkErrors()
    {
        if (!$this->errors->isEmpty()) {
            throw new Mad_Model_Exception_Validation($this->errors->fullMessages());
        }
    }

    /**
     * Check if the data for this method is valid. This will also
     * populate the errors property
     * @return  boolean
     */
    public function isValid()
    {
        return $this->_validateData();
    }

    /**
     * This method is invoked on every save() operation. Override
     * this in concrete subclasses to implement your own insert/update validation
     */
    protected function validate(){}

    /**
     * This method is invoked when a record is being inserted. Override
     * this in concrete subclasses to implement your own insert validation
     */
    protected function validateOnCreate(){}

    /**
     * This method is invoked when a record is bieng updated. Override
     * this in concrete subclasses to implement your own update validation.
     */
    protected function validateOnUpdate(){}


    /**
     * Validate the format of the data using ctype or regex.
     * Options:
     *  - on:      string  save, create, or update. Defaults to: save
     *  - with:    string  The ctype/regex to validate against
     *                     [alpha], [digit], [alnum], or /regex/
     *  - message: string  Custom error message (default is: "is invalid")
     *
     * <code>
     *  <?php
     *  ...
     *  // make sure parent_id attribute is a digit only on inserts
     *  $this->validatesFormatOf('parent_id', array('on' => 'insert', 'with' => '[digit]');
     *
     *  // make sure length attribute matches regexp
     *  $this->validatesFormatOf('length', array('with' => '/\d+(in|cm)/i');
     *  ...
     *  ?>
     * </code>
     *
     * @param   mixed   $attributes
     * @param   array   $options
     */
    protected function validatesFormatOf($attributes, $options=array())
    {
        $attributes = func_get_args();
        $last = end($attributes);
        $options = is_array($last) ? array_pop($attributes) : array();

        $this->_addValidation('format', $attributes, $options);
    }

    /**
     * Validate the length of the data.
     * Options:
     *  - on:          string save, create, or update. Defaults to: save
     *  - minimum:     int     Value may not be greater than this int
     *  - maximum:     int     Value may not be less than this int
     *  - is:          int     Value must be specific length
     *  - within:      array   The length of value must be in range: eg. array(3, 5)
     *  - allowNull:   bool    Allow null values through
     *
     *  - tooLong:     string Message when 'maximum' is violated
     *                        (default is: "%s is too long (maximum is %d characters)")
     *  - tooShort:    string Message when 'minimum' is violated
     *                        (default is: "%s is too short (minimum is %d characters)")
     *  - wrongLength: string Message when 'is' is invalid.
     *                        (default is: "%s is the wrong length (should be %d characters)")
     *  - message:     string Message to use for a 'minimum', 'maximum', or 'is violation.
     *                        An alias of the appropriate tooLong/tooShort/wrongLength msg
     *
     * <code>
     *  <?php
     *  ...
     *  // validate name is between 20 and 255 chars
     *  $this->validatesLengthOf('name', array('within' => '20..255');
     *
     *  // validate is_locked is 1 char
     *  $this->validatesLengthOf('is_locked', array('is' => 1);
     *
     *  // validate password is more than or equal to 8 chars
     *  $this->validatesLengthOf('password', array('minimum' => 8);
     *  ...
     *  ?>
     * </code>
     *
     * @param   mixed   $attributes
     * @param   int     $minLength
     * @param   int     $maxLength
     */
    protected function validatesLengthOf($attributes, $options=array())
    {
        $attributes = func_get_args();
        $last = end($attributes);
        $options = is_array($last) ? array_pop($attributes) : array();

        $this->_addValidation('length', $attributes, $options);
    }

    /**
     * Validate that the data is numeric. (Yes I'm aware numericality is not a real word)
     * Options:
     *  - on:          string  save, create, or update. Defaults to: save
     *  - onlyInteger: bool    Don't allow floats
     *  - allowNull:   bool    Are null values valid. Defaults to: false
     *  - message:     string  Defaults to: "%s is not a number."
     *
     * <code>
     *  <?php
     *  ...
     *  // validate that height is a number
     *  $this->validatesNumericalityOf('height');
     *  $this->validatesNumericalityOf('age', array('only_integer' => true));
     *  ...
     *  ?>
     * </code>
     *
     * @param   mixed   $attributes
     */
    protected function validatesNumericalityOf($attributes, $options=array())
    {
        $attributes = func_get_args();
        $last = end($attributes);
        $options = is_array($last) ? array_pop($attributes) : array();

        $this->_addValidation('numericality', $attributes, $options);
    }

    /**
     * Validate that the data isn't empty
     * Options:
     *  - on:      string  save, create, or update. Defaults to: save
     *  - message: string  Defaults to: "%s can't be empty."
     *
     * <code>
     *  <?php
     *  ...
     *  $this->validatesPresenceOf(array('name', 'description'));
     *  ...
     *  ?>
     * </code>
     *
     * @param   mixed   $attributes
     */
    protected function validatesPresenceOf($attributes, $options=array())
    {
        $attributes = func_get_args();
        $last = end($attributes);
        $options = is_array($last) ? array_pop($attributes) : array();

        $this->_addValidation('presence', $attributes, $options);
    }

    /**
     * Validate that the data is unique.
     * Options:
     *  - on:      string  save, create, or update. Defaults to: save
     *  - scope:   string  Limits the check to rows having the same value in the column
     *                     as the row being checked.
     *  - message: string  Defaults to: "The value for %s has already been taken."
     *
     * <code>
     *  <?php
     *  ...
     *  $this->validatesUniquenessOf('name', array('scope' => 'parent_id'));
     *  ...
     *  ?>
     * </code>
     *
     * @param   mixed   $attributes
     */
    protected function validatesUniquenessOf($attributes, $options=array())
    {
        $attributes = func_get_args();
        $last = end($attributes);
        $options = is_array($last) ? array_pop($attributes) : array();

        $this->_addValidation('uniqueness', $attributes, $options);
    }

    /**
     * Validates an item is included in the list.
     * Options:
     *  - on:         string        save, create, or update. Defaults to: save
     *  - in:         array|object  array or traversable object
     *  - allowNull:  bool          Are null values valid. Defaults to: false
     *  - strict:     bool          If true, use === comparison.  Defaults to: false (==).
     *  - message:    string        Defaults to: "%s is not included in the list."
     *
     * <code>
     *  <?php
     *  ...
     *  $this->validatesInclusionOf('name', array('in' => array('foo', 'bar')));
     *  ...
     *  ?>
     * </code>
     *
     * @param   mixed   $attributes
     */
    protected function validatesInclusionOf($attributes, $options = array())
    {
        $attributes = func_get_args();
        $last = end($attributes);
        $options = is_array($last) ? array_pop($attributes) : array();

        $this->_addValidation('inclusion', $attributes, $options);
    }

    /**
     * Validate that the email address is formatted correctly
     * Options: 
     *  - on:      string   
     *  - message: 
     * 
     *
     * <code>
     *  <?php
     *  ...
     *  $this->validatesEmailAddress('name', array('scope' => 'parent_id'));
     *  ...
     *  ?>
     * </code>
     */
    protected function validatesEmailAddress($attributes, $options=array())
    {
        $attributes = func_get_args();
        $last = end($attributes);
        $options = is_array($last) ? array_pop($attributes) : array();

        $with = "/^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|".
                "([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,3})$/i";
        $msg  = "must be a valid address";
        $options = array_merge(array('with' => $with, 'message' => $msg), $options);
        $this->_addValidation('format', $attributes, $options);
    }


    /*##########################################################################
    # Serialization
    ##########################################################################*/
    
    /**
     * Builds an XML document to represent the model. Some configuration is
     * available through <code>options</code>. However more complicated cases should
     * override <code>Mad_Model_Base#toXml</code>.
     *
     * By default the generated XML document will include the processing
     * instruction and all the object's attributes. For example:
     *
     *   <?xml version="1.0" encoding="UTF-8"?>
     *   <topic>
     *     <title>The First Topic</title>
     *     <author-name>David</author-name>
     *     <id type="integer">1</id>
     *     <approved type="boolean">false</approved>
     *     <replies-count type="integer">0</replies-count>
     *     <bonus-time type="datetime">2000-01-01T08:28:00+12:00</bonus-time>
     *     <written-on type="datetime">2003-07-16T09:28:00+1200</written-on>
     *     <content>Have a nice day</content>
     *     <author-email-address>david@loudthinking.com</author-email-address>
     *     <parent-id></parent-id>
     *     <last-read type="date">2004-04-15</last-read>
     *   </topic>
     *
     * This behavior can be controlled with <code>only</code>, <code>except</code>,
     * <code>skip_instruct</code>, <code>skip_types</code> and <code>dasherize</code>.
     * The <code>only</code> and <code>except</code> options are the same as for the
     * <code>attributes</code> method. The default is to dasherize all column names, but you
     * can disable this setting <code>dasherize</code> to <code>false</code>. To not have the
     * column type included in the XML output set <code>:skip_types</code> to <code>true</code>.
     *
     * For instance:
     *
     *   $topic->toXml(array('skip_instruct' => true, 
     *                       'except' => array('id', 'bonus_time', 'written_on', 'replies_count'));
     *
     *   <topic>
     *     <title>The First Topic</title>
     *     <author-name>David</author-name>
     *     <approved type="boolean">false</approved>
     *     <content>Have a nice day</content>
     *     <author-email-address>david@loudthinking.com</author-email-address>
     *     <parent-id></parent-id>
     *     <last-read type="date">2004-04-15</last-read>
     *   </topic>
     *
     * To include first level associations use <code>include</code>:
     *
     *   $firm->toXml(array('include' => array('Account', 'Clients')));
     *
     *   <?xml version="1.0" encoding="UTF-8"?>
     *   <firm>
     *     <id type="integer">1</id>
     *     <rating type="integer">1</rating>
     *     <name>37signals</name>
     *     <clients type="array">
     *       <client>
     *         <rating type="integer">1</rating>
     *         <name>Summit</name>
     *       </client>
     *       <client>
     *         <rating type="integer">1</rating>
     *         <name>Microsoft</name>
     *       </client>
     *     </clients>
     *     <account>
     *       <id type="integer">1</id>
     *       <credit-limit type="integer">50</credit-limit>
     *     </account>
     *   </firm>
     *
     * To include deeper levels of associations pass a hash like this:
     *
     *   $firm->toXml(array('include' => array('Account' => array(), 
     *                                         'Clients' => array('include' => 'Address'))));
     *
     *   <?xml version="1.0" encoding="UTF-8"?>
     *   <firm>
     *     <id type="integer">1</id>
     *     <rating type="integer">1</rating>
     *     <name>37signals</name>
     *     <clients type="array">
     *       <client>
     *         <rating type="integer">1</rating>
     *         <name>Summit</name>
     *         <address>
     *           ...
     *         </address>
     *       </client>
     *       <client>
     *         <rating type="integer">1</rating>
     *         <name>Microsoft</name>
     *         <address>
     *           ...
     *         </address>
     *       </client>
     *     </clients>
     *     <account>
     *       <id type="integer">1</id>
     *       <credit-limit type="integer">50</credit-limit>
     *     </account>
     *   </firm>
     *
     * To include any methods on the model being called use <code>methods</code>:
     *
     *   $firm->toXml(array('methods' => array('calculated_earnings', 'real_earnings')));
     *
     *   <firm>
     *     # ... normal attributes as shown above ...
     *     <calculated-earnings>100000000000000000</calculated-earnings>
     *     <real-earnings>5</real-earnings>
     *   </firm>
     *
     * As noted above, you may override <code>toXml()</code> in your <code>Mad_Model_Base</code>
     * subclasses to have complete control about what's generated. The general
     * form of doing this is:
     *
     *   class IHaveMyOwnXML extends Mad_Model_Base
     *   {
     *      public function toXml($options = array)
     *      {
     *          // ...
     *      }
     *   }
     */
    public function toXml($options = array())
    {
        $serializer = new Mad_Model_Serializer_Xml($this, $options);
        return $serializer->serialize();
    }

    /** 
     * Convert XML to an Mad_Model record
     * 
     * @see     Mad_Model_Base::toXml()
     * @param   string  $xml
     * @return  Mad_Model_Base
     */    
    public function fromXml($xml)
    {
        $converted  = Mad_Support_ArrayObject::fromXml($xml);
        $values     = array_values($converted);
        $attributes = $values[0];

        $this->setAttributes($attributes); 
        return $this;
    }

    public function getXmlClassName()
    {
        return Mad_Support_Inflector::underscore($this->_className);
    }

    /** 
     * Returns a JSON string representing the model. Some configuration is
     * available through <code>$options</code>.
     *
     * Without any <code>$options</code>, the returned JSON string will include all
     * the model's attributes. For example:
     *
     *   $konata = User::find(1);
     *   $konata->toJson();
     *   # => {"id": 1, "name": "Konata Izumi", "age": 16,
     *         "created_at": "2006/08/01", "awesome": true}
     *
     * The <code>only</code> and <code>except</code> options can be used to limit 
     * the attributes included, and work similar to the <code>attributes</code> 
     * method. For example:
     *
     *   $konata->toJson(array('only' => array('id', 'name')));
     *   # => {"id": 1, "name": "Konata Izumi"}
     *
     *   $konata->toJson(array('except' => array('id', 'created_at', 'age')));
     *   # => {"name": "Konata Izumi", "awesome": true}
     *
     * To include any methods on the model, use <code>:methods</code>.
     *
     *   $konata->toJson(array('methods' => 'permalink'));
     *   # => {"id": 1, "name": "Konata Izumi", "age": 16,
     *         "created_at": "2006/08/01", "awesome": true,
     *         "permalink": "1-konata-izumi"}
     *
     * To include associations, use <code>:include</code>.
     *
     *   $konata->toJson(array('include' => 'Posts'));
     *   # => {"id": 1, "name": "Konata Izumi", "age": 16,
     *         "created_at": "2006/08/01", "awesome": true,
     *         "posts": [{"id": 1, "author_id": 1, "title": "Welcome to the weblog"},
     *                   {"id": 2, author_id: 1, "title": "So I was thinking"}]}
     *
     * 2nd level and higher order associations work as well:
     *
     *   $konata->toJson(array('include' => array('Posts' => array(
     *                                              'include' => array('Comments' => array(
    *                                                                    'only' => 'body')),
     *                                              'only'    => 'title'))));
     *   # => {"id": 1, "name": "Konata Izumi", "age": 16,
     *         "created_at": "2006/08/01", "awesome": true,
     *         "posts": [{"comments": [{"body": "1st post!"}, {"body": "Second!"}],
     *                    "title": "Welcome to the weblog"},
     *                   {"comments": [{"body": "Don't think too hard"}],
     *                    "title": "So I was thinking"}]}
     * 
     * @param   array   $options
     * @return  string
     */
    public function toJson($options = array())
    {
        $serializer = new Mad_Model_Serializer_Json($this, $options);
        $serialized = $serializer->serialize();
        
        if (self::$includeRootInJson) {
            $jsonName = $this->getJsonClassName(); 
            return "{ $jsonName: $serialized }";
        } else {
            return $serialized;
        }
    }
    
    /** 
     * Convert Json notation to an Mad_Model record
     * 
     * @see     Mad_Model_Base::toJson()
     * @param   string  $json
     * @return  Mad_Model_Base
     */
    public function fromJson($json)
    {
        if (! function_exists('json_decode')) { 
            throw new Mad_Model_Exception('json_decode() function required');
        }        

        $attributes = (array)json_decode($json);
        $this->setAttributes($attributes);
        return $this;
    }

    public function getJsonClassName()
    {
        return '"'.Mad_Support_Inflector::underscore($this->_className).'"';
    }


    /*##########################################################################
    # Private methods
    ##########################################################################*/

    /**
     * @return  string
     */
    protected function _quotedId()
    {
        return $this->_quoteValue(
            $this->id, $this->columnForAttribute($this->primaryKey())
        );
    }

    /**
     * @param   object  $quoter
     * @param   array   $hash
     * @return  array
     */
    protected function _quotedCommaPairList(Mad_Model_ConnectionAdapter_Abstract $quoter, $hash)
    {
        return $this->_commaPairList($this->_quoteColumns($quoter, $hash));
    }

    /**
     * Returns a comma-separated pair list, like "key1 = val1, key2 = val2".
     * @todo finish
     * @return  string
     */
    protected function _commaPairList($hash) 
    {
        $pairs = array();
        foreach ($hash as $key => $value) {
            $pairs[] = "#{pair.first} = #{pair.last}";
        }
        return join($pairs, ', ');
    }

    /**
     * @param   array   $attributes
     */
    protected function _quotedColumnNames()
    {
        $attributes = $this->_attributesWithQuotes();
        $quotedCols = array();
        foreach (array_keys($attributes) as $columnName) {
            $quotedCols[] = $this->connection->quoteColumnName($columnName);
        }
        return $quotedCols;
    }

    /**
     * @param   object  $quoter
     * @param   array   $hash
     * @return  array
     */
    protected function _quoteColumns(Mad_Model_ConnectionAdapter_Abstract $quoter, $hash)
    {
        $quoted = array();
        foreach ($hash as $name => $value) {
            $quoted[$quoter->quoteColumnName($name)] = $value;
        }
        return $quoted;
    }

    
    /**
     * Returns copy of the attributes hash where all the values have been 
     * safely quoted for use in a SQL statement.
     * 
     * @param   string  $includePrimaryKey
     * @return  array
     */
    protected function _attributesWithQuotes($includePrimaryKey=true)
    {
        $quoted = array();
        foreach ($attributes as $name => $value) {
            if ($column = $this->columnForAttribute($name)) {
                if (!$includePrimaryKey && $column->isPrimary()) { continue; }
                $quoted[$name] = $this->_quoteValue($value, $column);
            }
        }
        return $quoted;
    }
    
    /**
     * Quote strings appropriately for SQL statements.
     */
    protected function _quoteValue($value, $column=null)
    {
        return $this->connection->quote($value, $column);
    }

    /**
     * Initializes the attributes array with keys matching the columns
     * from the linked table and the values matching the corresponding
     * default value of that column, so that a new instance, or one 
     * populated from a passed-in Hash, still has all the attributes
     * that instances loaded from the database would.
     * 
     * @todo finish
     */
    protected function _attributesFromColumnDefinition()
    {
        $attributes = array();
        foreach ($this->columns() as $col) {
            $attributes[$col->getName()] = null;
            if ($col->getName() != $this->primaryKey()) {
                $attributes[$col->getName()] = $col->getDefault();
            }
        }
        return $attributes;
    }


    /*##########################################################################
    # Find Private methods
    ##########################################################################*/

    /**
     * Check if a record exists.
     *
     * @see     Mad_Model_Base::exists()
     * @param   array|int   $ids
     * @return  boolean
     */
    protected function _exists($ids)
    {
        try {
            $this->_findFromIds($ids);
            return true;
        } catch (Mad_Model_Exception_RecordNotFound $e) {
            return false;
        }
    }

    /**
     * Where the actual work is done for find() method
     *
     * @see     Mad_Model_Base::find()
     * @param   mixed   $type    (pk or array of pks)
     * @param   array   $options
     * @param   array   $bindVars
     * @throws  Mad_Model_Exception_RecordNotFound
     */
    protected function _find($type, $options, $bindVars)
    {
        $bindVars = !empty($bindVars) ? $bindVars : array();

        // find the first record that match the options
        if ($type == 'first') {
            return $this->_findInitial($options, $bindVars);

        // find all records that match the options
        } elseif ($type == 'all') {
            return $this->_findEvery($options, $bindVars);

        // type must match one of the above options
        } else {
            return $this->_findFromIds($type, $options, $bindVars);
        }
    }


    /**
     * Find by primary key values. Will either find by a single or multiple pks.
     * Single id returns a single Mad_Model_Base subclass
     * Multple ids return a Mad_Model_Collection of Mad_Model_Base subclasses
     *
     * @see     Mad_Model_Base::find()
     * @param   array|int   $ids
     * @param   array       $options
     * @param   array       $bindVars
     *
     * @return  Mad_Model_Collection|Mad_Model_Base
     * @throws  Mad_Model_Exception_RecordNotFound
     */
    protected function _findFromIds($ids, $options=array(), $bindVars=array())
    {
        $expectsArray = is_array($ids);
        $ids = (array)$ids;
        foreach ($ids as &$id) {
            if (!is_int($id)) $id = trim($id);
        }
        $selectStr = $this->getColumnStr();

        if (count($ids) == 0 || !isset($ids[0])) {
            $msg = "Couldn't find ".get_class($this)." without an ID";
            throw new Mad_Model_Exception_RecordNotFound($msg);

        } elseif (count($ids) == 1) {
            $result = $this->_findOne($ids[0], $options, $bindVars);
            return $expectsArray ? new Mad_Model_Collection($this, array($result)) : $result;

        } else {
            return $this->_findSome($ids, $options, $bindVars);
        }
    }

    /**
     * Find using a single pk
     *
     * @param   int     $id
     * @param   array   $options
     * @param   array   $bindVars
     * @return  Mad_Model_Base
     * @throws  Mad_Model_Exception_RecordNotFound
     */
    protected function _findOne($id, $options, $bindVars)
    {
        $conditions = null;
        if (isset($options['conditions'])) {
            $conditions = " AND (".$options['conditions'].")";
        }
        $options['conditions'] = "$this->_tableName.$this->_primaryKey = :pkId".
                                 " $conditions";
        $bindVars[':pkId'] = $id;

        if ($result = $this->_findInitial($options, $bindVars)) {
            return $result;
        } else {
            $msg = "The record for id=$id was not found";
            throw new Mad_Model_Exception_RecordNotFound($msg);
        }

    }

    /**
     * Find using mutiple pks
     *
     * @param   int     $id
     * @param   array   $options
     * @return  Mad_Model_Collection
     * @throws  Mad_Model_Exception_RecordNotFound
     */
    protected function _findSome($ids, $options, $bindVars)
    {
        // build list of ids/binds
        $size = count($ids);
        for ($i = 0; $i < $size; $i++) $inStr[] = ":id{$i}";
        for ($i = 0; $i < $size; $i++) $bindVars[":id{$i}"] = (int) $ids[$i];

        $conditions = null;
        if (isset($options['conditions'])) {
            $conditions = " AND (".$options['conditions'].")";
        }
        $options['conditions'] = "$this->_tableName.$this->_primaryKey IN (".
                                  join(', ', $inStr).") $conditions";
        $result = $this->_findEvery($options, $bindVars);

        // we should always get back the same number of rows as ids
        if ($result->count() == $size) {
            return $result;
        } else {
            $msg = 'A record id IN ('.join(', ', $ids).') was not found';
            throw new Mad_Model_Exception_RecordNotFound($msg);
        }
    }

    /**
     * Find the first record matching the given options
     *
     * @see     Mad_Model_Base::find()
     * @param   mixed   $options
     * @param   array   $bindVars
     * @return  Mad_Model_Base
     */
    protected function _findInitial($options, $bindVars)
    {
        $result = $this->_findEvery($options, $bindVars);
        return !empty($result[0]) ? $result[0] : null;
    }

    /**
     * Find all records matching the given options
     *
     * @see     Mad_Model_Base::find()
     * @param   array   $options
     * @param   array   $bindVars
     * @return  array   {@link Mad_Model_Base}s
     */
    protected function _findEvery($options, $bindVars)
    {
        // use eager loading associations
        if (isset($options['include'])) {
            return $this->_findWithAssociations($options, $bindVars);

        // no eager loading
        } else {
            return $this->_findEveryBySql($this->_constructFinderSql($options), $bindVars);
        }
    }

    /**
     * Count how many records match the given options
     *
     * @see     Mad_Model_Base::find()
     * @param   mixed   $options
     * @param   array   $bindVars
     * @return  int
     */
    protected function _count($options, $bindVars)
    {
        // if $options is a string, default it to be the conditions
        if (is_string($options)) {
            $options = array('conditions' => $options);
        }
        if (!isset($options['select'])) $options['select'] = 'COUNT(1)';

        // use eager loading associations
        if (isset($options['include'])) {
            $options['select'] = 'COUNT(DISTINCT('.$this->tableName().'.'.
                                                   $this->primaryKey().'))';
            return $this->_countWithAssociations($options, $bindVars);

        // no eager loading
        } else {
            $sql = $this->_constructFinderSql($options);
            $sql = $this->sanitizeSql($sql, $bindVars);
            return $this->connection->selectValue($sql, "$this->_className Count");
        }
    }


    /*##########################################################################
    # FindBySql Private methods
    ##########################################################################*/

    /**
     * Where the actual work is done for findBySql() calls
     *
     * @see     Mad_Model_Base::findBySql()
     * @param   string  $type
     * @param   string  $sql
     * @param   array   $bindVars
     */
    protected function _findBySql($type, $sql, $bindVars)
    {
        $bindVars = !empty($bindVars) ? $bindVars : array();

        // find all records that match the options
        if ($type == 'all') {
            return $this->_findEveryBySql($sql, $bindVars);

        // find the first record that match the options
        } elseif ($type == 'first') {
            return $this->_findInitialBySql($sql, $bindVars);
        }
    }

    /**
     * Find all records that are retrieved by the given sql
     *
     * @see     Mad_Model_Base::findBySql()
     * @param   string  $sql
     * @param   array   $bindVars
     */
    protected function _findEveryBySql($sql, $bindVars)
    {
        $sql = $this->sanitizeSql($sql, $bindVars);

        $result = $this->connection->selectAll($sql, "$this->_className Load");
        return new Mad_Model_Collection($this, $result);
    }

    /**
     * Find the first record that is retrieved by the given sql
     *
     * @see     Mad_Model_Base::findBySql()
     * @param   string  $sql
     * @param   array   $bindVars
     */
    protected function _findInitialBySql($sql, $bindVars)
    {
        $sql = $this->sanitizeSql($sql, $bindVars);
        $sql = $this->connection->addLimitOffset($sql, array('limit'  => 1, 
                                                             'offset' => 0));

        if ($row = $this->connection->selectOne($sql, "$this->_className Load")) {
            return $this->instantiate($row);
        } else {
            return null;
        }
    }

    /**
     * Count how many records are retrieved by the given sql
     *
     * @see     Mad_Model_Base::findBySql()
     * @param   string  $sql
     * @param   array   $bindVars
     */
    protected function _countBySql($sql, $bindVars)
    {
        // execute query
        $sql = $this->sanitizeSql($sql, $bindVars);
        return $this->connection->selectValue($sql, "$this->_className Count");
    }

    /**
     * Paginate is a proxy to find, but determines offset/limit based on 
     * 
     * @see     Mad_Model_Base::paginate()
     * @param   array   $options
     * @param   array   $bindVars
     * @return  Mad_Model_Collection
     */
    protected function _paginate($options=null, $bindVars=null)
    {
        // determine offset/limit based on page/perPage
        $page    = isset($options['page'])    ? $options['page']    : 1;
        $perPage = isset($options['perPage']) ? $options['perPage'] : 15;
        unset($options['page']);
        unset($options['perPage']);

        // count records
        $countOptions = $options;
        unset($countOptions['select']);
        $total = $this->_count($countOptions, $bindVars);
        if ($total == 0) { $page = 0; }

        // find records
        if ($total) {
            $options['offset'] = $page * $perPage - $perPage;
            $options['limit']  = $perPage;

            // default to page 1 if out of range
            if ($options['offset'] > $total) {
                $page = 1;
                $options['offset'] = 0;
            }

            $results = $this->_find('all', $options, $bindVars);
        } else {
            $results = new Mad_Model_Collection($this, array());
        }

        // paginated collection
        return new Mad_Model_PaginatedCollection($results, $page, $perPage, $total);
    }


    /*##########################################################################
    # Finder SQL Construction
    ##########################################################################*/

    /**
     * Find model objects with eager loaded associations
     * @param   array   $options
     * @param   array   $bindVars
     */
    protected function _findWithAssociations($options, $bindVars)
    {
        $joinDependency = new Mad_Model_Join_Dependency($this, $options['include']);
        $sql = $this->_constructFinderSqlWithAssoc($options, $joinDependency, $bindVars);

        $sql = $this->sanitizeSql($sql, $bindVars);
        $rows = $this->connection->selectAll($sql, "$this->_className Load");

        return new Mad_Model_Collection($this, $joinDependency->instantiate($rows));
    }
    
    /**
     * Count model objects with eager loaded associations
     * @param   array   $options
     * @param   array   $bindVars
     */
    protected function _countWithAssociations($options, $bindVars) 
    {
        $joinDependency = new Mad_Model_Join_Dependency($this, $options['include']);
        $sql = $this->_constructFinderSqlWithAssoc($options, $joinDependency, $bindVars);

        $sql = $this->sanitizeSql($sql, $bindVars);
        return $this->connection->selectValue($sql, "$this->_className Count");
    }

    /**
     * Construct the sql to retrieve all models w/eager associations
     * @param   array   $options
     * @param   object  $joinDependency
     * @param   array   $bindVars
     * @return  string
     */
    protected function _constructFinderSqlWithAssoc($options, $joinDependency, $bindVars)
    {
        $valid = array('select', 'from', 'conditions', 'include',
                       'order', 'group', 'limit', 'offset');
        $options = Mad_Support_Base::assertValidKeys($options, $valid);

        // get columns from dependency
        foreach ($joinDependency->joins() as $join) {
            foreach ($join->columnNamesWithAliasForSelect() as $colAlias) {
                $cols[] = $colAlias[0].' AS '.$colAlias[1];
            }
        }
        $selectStr = isset($options['select']) ? $options['select'] : join(', ', $cols);

        $sql = "SELECT ".$selectStr;
        $sql .= " FROM ". ($options['from'] ? $options['from'] : $this->tableName());
        $sql .= $this->_constructAssociationJoinSql($joinDependency);
        $sql = $this->_addConditions($sql, $options['conditions']);

        // certain association outer joins will truncate results using 'limit'
        if (isset($options['limit']) && !$this->_usingLimitableReflections($joinDependency->reflections())) {
            $sql = $this->_addLimitedIdsCondition($sql, $options, $joinDependency, $bindVars);
        }

        if ($options['order']) $sql .= ' ORDER BY '.$options['order'];
        if ($this->_usingLimitableReflections($joinDependency->reflections())) {
            $sql = $this->connection->addLimitOffset($sql, $options);
        }
        return $sql;
    }

    /**
     * Add condition to limit our query by a specific set of ids
     * @param   string  $sql
     * @param   array   $options
     * @param   object  $joinDependency
     * @param   array   $bindVars
     * @return  string
     */
    protected function _addLimitedIdsCondition($sql, $options, $joinDependency, $bindVars)
    {
        $idList = $this->_selectLimitedIdsList($options, $joinDependency, $bindVars);
        if (empty($idList)) { throw new Mad_Model_Exception('Invalid Query'); }

        $conditionWord = stristr($sql, 'where') ? ' AND ' : 'WHERE ';
        $sql .= "$conditionWord ".$this->tableName().'.'.
                 $this->primaryKey()." IN ($idList)";
        return $sql;
    }
    
    /**
     * @param   array   $options
     * @param   object  $joinDependency
     * @param   array   $bindVars
     * @return  string  
     */
    protected function _selectLimitedIdsList($options, $joinDependency, $bindVars)
    {
        $result = $this->connection->selectAll(
            $this->_constructFinderSqlForAssocLimiting($options, $joinDependency, $bindVars), 
            "$this->_className Load IDs For Limited Eager Loading");
        $ids = array();
        foreach ($result as $row) {
            $ids[] = $this->connection->quote($row[$this->primaryKey()]);
        }
        return join(', ', $ids);
    }

    /**
     * @param   array   $options
     * @param   object  $joinDependency
     * @param   array   $bindVars
     * @return  string
     */
    protected function _constructFinderSqlForAssocLimiting($options, $joinDependency, $bindVars)
    {
        $isDistinct = $this->_includeEagerConditions($options) ||  
                      $this->_includeEagerOrder($options);
        $sql = "SELECT ";
        if ($isDistinct) {
            $sql .= $this->connection->distinct($this->tableName().'.'.$this->primaryKey());
        } else {
            $sql .= $this->primaryKey();
        }
        $sql .= ' FROM '.$this->tableName().' ';

        // add join tables/conditions/ordering
        if ($isDistinct) { 
            $sql .= $this->_constructAssociationJoinSql($joinDependency); 
        }

        $sql = $this->_addConditions($sql, $options['conditions']);
        if (!empty($options['order'])) {
            if ($isDistinct) {
                $sql = $this->connection->addOrderByForAssocLimiting($sql, $options);
            } else {
                $sql .= "ORDER BY ".$options['order'];
            }
        }
        $sql = $this->connection->addLimitOffset($sql, $options);
        return $this->sanitizeSql($sql, $bindVars);
    }

    /**
     * Checks if the conditions reference a table other than the 
     * current model table
     * 
     * @param   array   $options
     * @return  boolean
     */
    protected function _includeEagerConditions($options)
    {
        if (!$conditions = $options['conditions']) { return false; }

        preg_match_all("/([\.\w]+)\.\w+/", $conditions, $matches);
        foreach ($matches[1] as $conditionTableName) {
            if ($conditionTableName != $this->tableName()) { return true; }
        }
        return false;
    }

    /**
     * Checks if the query order references a table other than the 
     * current model's table.
     * 
     * @param   array   $options
     * @return  boolean
     */
    protected function _includeEagerOrder($options)
    {
        if (!$order = $options['order']) { return false; }

        preg_match_all("/([\.\w]+)\.\w+/", $order, $matches);
        foreach ($matches[1] as $orderTableName) {
            if ($orderTableName != $this->tableName()) { return true; }
        }
        return false;
    }

    /**
     * Cannot use LIMIT/OFFSET on certain associations
     * 
     * @param   array   $reflections
     * @return  boolean
     */
    protected function _usingLimitableReflections($reflections)
    {
        foreach ($reflections as $r) {
            $macro = $r->getMacro();
            if ($macro != 'belongsTo' || $macro != 'hasOne') { return false; }
        }
        return true;
    }

    /**
     * Construct 'OUTER JOIN' sql fragments from associations
     * 
     * @param   object  $joinDependency
     */
    protected function _constructAssociationJoinSql($joinDependency) 
    {
        // get joins from dependency
        $joins = array();
        foreach ($joinDependency->joinAssociations() as $joinAssoc) {
            $joins[] = $joinAssoc->associationJoin();
        }
        return join('', $joins);
    }

    /**
     * Construct the sql used to do a find() method
     *
     * @param   array   $options
     * @return  string  the SQL
     */
    protected function _constructFinderSql($options)
    {
        $valid = array('select', 'from', 'conditions', 'include',
                       'order', 'group', 'limit', 'offset');
        $options = Mad_Support_Base::assertValidKeys($options, $valid);

        $sql = "SELECT ".($options['select'] ? $options['select'] : $this->getColumnStr());
        $sql .= " FROM ". ($options['from']  ? $options['from']   : $this->tableName());

        $sql = $this->_addConditions($sql, $options['conditions']);

        if ($options['group']) $sql .= ' GROUP BY '.$options['group'];
        if ($options['order']) $sql .= ' ORDER BY '.$options['order'];

        return $this->connection->addLimitOffset($sql, $options);
    }

    /**
     * Add 'where' conditions to the sql
     *
     * @param   string  $sql
     * @param   array   $options
     */
    private function _addConditions($sql, $conditions)
    {
        $segments = array();
        if (!empty($conditions)) $segments[] = $conditions;

        if (!empty($segments)) $sql .= ' WHERE ('.join(') AND (', $segments).')';

        return $sql;
    }

    /*##########################################################################
    # Create/Update/Delete Private methods
    ##########################################################################*/

    /**
     * Perform save operation. Only save if model data has changed. 
     * This method will perform all callback hooks for the save/update/create
     * operation. 
     */
    protected function _createOrUpdate()
    {
        // before save callback
        $this->_beforeSave();

        if ($this->isNewRecord()) {
            $this->_beforeCreate();
            $this->_saveCreate();
            $this->_afterCreate(); 

        } else {
            $this->_beforeUpdate();
            $this->_saveUpdate();
            $this->_afterUpdate(); 
        }
        // after save callback
        $this->_afterSave(); 
    }

    /**
     * Create object during save
     * 
     * @throws Mad_Model_Exception_Validation
     */
    protected function _saveCreate()
    {
        $this->_recordTimestamps();

        // build & execute SQL
        $sql = "INSERT INTO $this->_tableName (".
               "    ".$this->getColumnStr().
               ") VALUES (".
               "    ".$this->getInsertValuesStr().
               ")";
        $insertId = $this->connection->insert($sql, "$this->_className Insert");

        // only set the pk if it's not already set
        if ($this->primaryKey() && $this->{$this->primaryKey()} == null) {
            $this->_attributes[$this->primaryKey()] = $insertId;
        }
        return $insertId;
    }

    /**
     * Update object during save
     * 
     * @throws Mad_Model_Exception_Validation
     */
    protected function _saveUpdate()
    {
        $this->_recordTimestamps();

        foreach ($this->_attributes as $name => $value) {
            $column = strtolower($name);

            if ($column != $this->primaryKey()) {
                $sets[] = $this->connection->quoteColumnName($column)." = ".
                          $this->_quoteValue($value);

            } elseif ($column == $this->primaryKey()) {
                $pkVal = $this->_quoteValue($value);
            }
        }

        $sql = "UPDATE $this->_tableName ".
               "   SET ".join(', ', $sets).
               " WHERE $this->_primaryKey = $pkVal";
        return $this->connection->update($sql, "$this->_className Update");
    }

    /**
     * Automatic timestamps for magic columns
     */
    protected function _recordTimestamps()
    {
        $date = date("Y-m-d");
        $time = date("Y-m-d H:i:s");
        $attr = $this->getAttributes();

        // new records
        if (array_key_exists('created_at', $attr) && 
            (empty($this->created_at) || $this->created_at == '0000-00-00 00:00:00')) {
            $this->writeAttribute('created_at', $time);
        }
        if (array_key_exists('created_on', $attr) && 
            (empty($this->created_on) || $this->created_on == '0000-00-00')) {
            $this->writeAttribute('created_on', $date);
        }

        // all saves
        if (array_key_exists('updated_at', $attr)) {
            $this->writeAttribute('updated_at', $time);
        }
        if (array_key_exists('updated_on', $attr)) {
            $this->writeAttribute('updated_on', $date);
        }
    }

    /**
     * Create a new record
     *
     * @see     Mad_Model_Base::findBySql()
     * @param   array   $attributes
     * @return  mixed   single model object OR array of model objects
     */
    protected function _create($attributes)
    {
        $this->_newRecord = true;

        // MULTIPLE
        if (isset($attributes[0])) {
            $attributeList = $attributes;
            foreach ($attributeList as $attributes) {
                $obj = new $this->_className($attributes);
                $objs[] = $obj->save();
            }
            return $objs;

        // SINGLE
        } else {
            $obj = new $this->_className($attributes);
            return $obj->save();
        }
    }

    /**
     * Update a record
     *
     * @see     Mad_Model_Base::update()
     * @param   int     $id
     * @param   array   $attributes
     * @return  void
     */
    protected function _update($id, $attributes)
    {
        // MULTIPLE
        if (is_array($id)) {
            $ids = $id;
            foreach ($ids as $id) {
                $model = $this->find($id);
                $model->updateAttributes($attributes);
                $objs[] = $model;
            }
            return new Mad_Model_Collection($model, $objs);

        // SINGLE
        } else {
            $model = $this->find($id);
            return $model->updateAttributes($attributes);
        }
    }

    /**
     * Update multiple records matching the given criteria.
     *
     * @todo    replacements for bindvars
     * 
     * @see     Mad_Model_Base::updateAll()
     * @param   string  $set
     * @param   string  $conditions
     * @param   array   $bindVars
     * @return  void
     */
    protected function _updateAll($set, $conditions=null, $bindVars=null)
    {
        $setStr       = $this->sanitizeSql($set, $bindVars);
        $conditionStr = $this->sanitizeSql($conditions, $bindVars);
        $conditionStr = !empty($conditions) ? "WHERE $conditionStr " : null;

        $sql = "UPDATE $this->_tableName ".
               "   SET $setStr ".
               $conditionStr;
        return $this->connection->update($sql, "$this->_className Update");
    }

    /**
     * Perform destroy operation
     */
    protected function _destroy()
    {
        // only delete if not already deleted
        $sql = "DELETE FROM $this->_tableName ".
               " WHERE $this->_primaryKey = ".$this->_quotedId();
        return $this->connection->delete($sql, "$this->_className Delete");
    }

    /**
     * Delete a given record
     *
     * @see     Mad_Model_Base::delete()
     * @param   mixed   $id (int or array of ints)
     * @return  boolean
     */
    protected function _delete($id)
    {
        // MULTIPLE
        if (is_array($id)) {
            $ids = $id;
            foreach ($ids as $id) {
                $obj = new $this->_className();
                $obj->id = $id;
                $obj->destroy();
            }

        // SINGLE
        } else {
            $obj = new $this->_className();
            $obj->id = $id;
            $result = $obj->destroy();
            if (!$result) return false;
        }
        return true;
    }

    /**
     * Delete multiple records by the given conditions
     *
     * @todo    replacements for bindvars
     * 
     * @see     Mad_Model_Base::deleteAll()
     * @param   string  $conditions
     * @param   array   $bindVars
     */
    protected function _deleteAll($conditions=null, $bindVars=null)
    {
        $conditionStr = $this->sanitizeSql($conditions, $bindVars);
        $conditionStr = !empty($conditions) ? "WHERE $conditionStr " : null;

        $sql = "DELETE FROM $this->_tableName $conditionStr";
        return $this->connection->delete($sql, "$this->_className Delete");
    }


    /*##########################################################################
    # Callback Methods
    ##########################################################################*/

    /**
     * Execute this callback before records are inserted
     */
    protected function _beforeValidation()
    {
        // Execute callback if it exists
        if (method_exists($this, 'beforeValidation')) {
            $this->beforeValidation();
        }
    }

    /**
     * Execute this callback after records are inserted
     */
    protected function _afterValidation()
    {
        // Execute callback if it exists
        if (method_exists($this, 'afterValidation')) {
            $this->afterValidation();
        }
    }

    /**
     * Execute this callback before records are saved
     */
    protected function _beforeSave()
    {
        $this->checkErrors();

        // Execute callback if it exists
        if (method_exists($this, 'beforeSave')) {
            $result = $this->beforeSave();
            if ($result === false) { $this->checkErrors(); }
        }
    }

    /**
     * Execute this callback before records are inserted
     */
    protected function _beforeCreate()
    {
        // Execute callback if it exists
        if (method_exists($this, 'beforeCreate')) {
            $result = $this->beforeCreate();
            if ($result === false) { $this->checkErrors(); }
        }
    }

    /**
     * Execute this callback before records are updated
     */
    protected function _beforeUpdate()
    {
        // Execute callback if it exists
        if (method_exists($this, 'beforeUpdate')) {
            $result = $this->beforeUpdate();
            if ($result === false) { $this->checkErrors(); }
        }
    }

    /**
     * Execute this callback after records are saved
     */
    protected function _afterSave()
    {
        // Execute callback if it exists
        if (method_exists($this, 'afterSave')) {
            $this->afterSave();
        }
    }

    /**
     * Execute this callback after records are inserted
     */
    protected function _afterCreate()
    {
        // Execute callback if it exists
        if (method_exists($this, 'afterCreate')) {
            $this->afterCreate();
        }
    }

    /**
     * Execute this callback after records are updated
     */
    protected function _afterUpdate()
    {
        // Execute callback if it exists
        if (method_exists($this, 'afterUpdate')) {
            $this->afterUpdate();
        }
    }

    /**
     * Execute this callback before records are destroyed
     */
    protected function _beforeDestroy()
    {
        $this->_initAssociations();
        if (isset($this->_associations)) {
            foreach ($this->_associations as $association) {
                $association->destroyDependent();
            }
        }

        // reset error stack
        $this->errors->clear();

        // Execute callback if it exists
        if (method_exists($this, 'beforeDestroy')) {
            $result = $this->beforeDestroy();
            if ($result === false) { $this->checkErrors(); }
        }
    }

    /**
     * Execute this callback after records are destroyed
     */
    protected function _afterDestroy()
    {
        // Execute callback if it exists
        if (method_exists($this, 'afterDestroy')) {
            $this->afterDestroy();
        }
        $this->_frozen = true;
    }


    /*##########################################################################
    # Validation methods
    ##########################################################################*/

    /**
     * Add a validation rule to this controller
     *
     * @param   string       $type
     * @param   string|array $attributes
     * @param   array        $options
     */
    protected function _addValidation($type, $attributes, $options)
    {
        foreach ((array)$attributes as $attribute) {
            $this->_validations[] = Mad_Model_Validation_Base::factory($type, $attribute, $options);
        }
    }

    /**
     * Validate data that we are about to save
     * @return  boolean     true for valid, false for invalid
     */
    protected function _validateData()
    {
        // reset error stack
        $this->errors->clear();
        $this->_beforeValidation();

        // validate all
        $this->validate();
        foreach ($this->_validations as $validation) {
            $validation->validate('save', $this);
        }
        // validate create
        if ($this->isNewRecord()) {
            $this->validateOnCreate();
            foreach ($this->_validations as $validation) {
                $validation->validate('create', $this);
            }
        // validate update
        } else {
            $this->validateOnUpdate();
            foreach ($this->_validations as $validation) {
                $validation->validate('update', $this);
            }
        }
        $this->_afterValidation();

        return $this->errors->isEmpty();
    }

    /*##########################################################################
    # Association methods
    ##########################################################################*/

    /**
     * Associations are lazy initialized as needed. This function is called when needed
     * to check if we need an association method
     */
    protected function _initAssociations()
    {
        // only initialize if we haven't already
        if (!isset($this->_associationMethods) && isset($this->_associationList)) {
            // loop thru each define association
            foreach ($this->_associationList as $associationId => $info) {
                list($type, $options) = $info;
                $association = Mad_Model_Association_Base::factory($type, $associationId, $options, $this);
                $this->_associations[$associationId] = $association;

                // add list of dynamic methods this association adds
                foreach ($association->getMethods() as $methodName => $methodCall) {
                    $this->_associationMethods[$methodName] = $association;
                }
            }
        }
    }

    /**
     * Force a reload of all associations. 
     */
    protected function _resetAssociations()
    {
        if (isset($this->_associationMethods)) {
            $this->_associationMethods = null;
            $this->_associations       = null;
        }
    }

    /**
     * Add an association to this model. This creates the appropriate Mad_Model_Association_Base
     * object and adds the object to the stack of associations for this model.
     * it also adds a list of dynamic methods that are added to this object by the
     * association.
     *
     * @param   string  $type
     * @param   string  $associationId
     * @param   array   $options
     */
    protected function _addAssociation($type, $associationId, $options)
    {
        $options = !empty($options) ? $options : array();
        $this->_associationList[$associationId] = array($type, $options);
    }

    /**
     * Save association model data for this model
     *
     * @param   string  $type (before|after)
     */
    protected function _saveAssociations($type)
    {
        if (!isset($this->_associations)) return;

        // save belongsTo before, and all others after
        foreach ($this->_associations as $association) {
            if ($association instanceof Mad_Model_Association_BelongsTo && $type == 'before') {
                $association->save();
            } elseif (!$association instanceof Mad_Model_Association_BelongsTo && $type == 'after') {
                $association->save();
            }
        }
    }

}
