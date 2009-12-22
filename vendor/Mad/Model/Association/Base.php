<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Association
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * An association between model objects
 * 
 * belongsTo: (Article belongsTo User) 
 *   $id = articles.id 
 *   SELECT * FROM users WHERE id = $id
 * 
 *   foreignKey => articles.user_id    $assocModel->tableName() . '_id'
 *   primaryKey => users.id            $assocModel->primaryKeyName()
 * 
 * 
 * hasOne: (User hasOne Avatar) 
 *   $id = users.users_id
 *   SELECT * FROM avatars WHERE users_id = $id
 * 
 *   foreignKey => avatars.user_id     $model->tableName() + _id
 *   primaryKey => users.id            $model->primaryKeyName()
 * 
 * 
 * hasMany: (User hasMany Articles) 
 *   $id = users.id
 *   SELECT * FROM articles WHERE user_id = $id
 * 
 *   foreignKey => articles.user_id    $model->tableName() . '_id'
 *   primaryKey => users.id            $model->primaryKeyName()
 * 
 * 
 * hasMany (through): (Article hasMany Tags 'through' => Taggings)
 *   $id = articles.id
 *   SELECT * 
 *     FROM taggings ts, tags t
 *    WHERE ts.article_id = $id
 *      AND ts.tag_id = t.id
 * 
 *   foreignKey  => article_id   $model->tableName() . '_id'
 *   primaryKey  => articles.id  $model->primaryKeyName()
 *   joinTable   => new $options['through']->tableName()
 * 
 *
 * hasAndBelongsToMany: (Category hasAndBelongsToMany Articles) 
 *   $id = categories.id
 *   SELECT * 
 *     FROM articles_categories ac, articles a
 *    WHERE ac.category_id = $id
 *      AND ac.article_id = a.id
 * 
 *   foreignKey      => category_id    $model->tableName() . '_id'
 *   primaryKey      => categories.id  $model->primaryKeyName()
 *   assocForeignKey => article_id     $assocModel->tableName() . '_id'
 *   assocPrimaryKey => articles.id    $assocModel->primaryKeyName()
 * 
 * 
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Association
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
abstract class Mad_Model_Association_Base
{
    /**
     * The name given to the association when declared
     * @var string
     */
    protected $_assocName = null;

    /**
     * The method used to create the association.
     * eg. (hasOne|belongsTo|hasMany|hasAndBelongsToMany)
     * Lazy loaded
     *
     * @var string
     */
    protected $_macro;

    /**
     * The method used to create the association.
     * Lazy loaded
     *
     * @var string
     */
    protected $_assocModel;

    /**
     * The name of the table for the associated object
     * Lazy loaded
     *
     * @var string
     */
    protected $_assocTable;

    /**
     * The name of the class for the associated object
     * Lazy loaded
     *
     * @var string
     */
    protected $_assocClass;

    /**
     * The model's primary key needed to link the associated object
     * Lazy loaded
     *
     * @var string
     */
    protected $_pkName;

    /**
     * The model's foreign key needed to link the associated object
     * Lazy loaded
     *
     * @var string
     */
    protected $_fkName;

    /**
     * The primary key needed to link to the associated object through a join table
     * Lazy loaded
     *
     * @var string
     */
    protected $_assocPkName;

    /**
     * The foreign key needed to link to the associated object through a join table
     * Lazy loaded
     *
     * @var string
     */
    protected $_assocFkName;


    /**
     * The Mad_Model_Base object this association is for
     * @param   object
     */
    protected $_model = null;

    /**
     * The list of association options
     */
    protected $_options = array();

    /**
     * The list of dynamic methods added to the model through this association
     * @var array
     */
    protected $_methods = array();

    /**
     * The list of data that has already been loaded
     * @var array
     */
    protected $_loaded = array();

    /**
     * Has the association changed in any way
     * @var boolean
     */
    protected $_changed = false;

    /**
     * Instance of the db connection
     * @var object
     */
    protected $_conn = null;


    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * Mad_Model_Association_Base classes are done through a simple factory pattern.
     * @see Mad_Model_Association_Base::factory
     * 
     * @param   string  $assocName
     * @param   array   $options
     */
    abstract protected function __construct($assocName, $options, $model);

    /**
     * Stringified version
     * @return  string
     */
    public function __toString()
    {
        return $this->getAssocName().' ['.
               get_class($this->getModel()).' '.
               $this->getMacro().' '.
               $this->getAssocClass().']';
    }

    /**
     * Construct association object - simple factory
     * @param   string  $macro (belongsTo|hasOne|hasMany|hasAndBelongsToMany)
     * @param   string  $assocName
     * @param   array   $options
     * @param   object  $model
     */
    public static function factory($macro, $assocName, $options, Mad_Model_Base $model)
    {
        // through association
        $through = isset($options['through']) ? 'Through' : '';
        $className = 'Mad_Model_Association_'.Mad_Support_Inflector::classify($macro).$through;

        return new $className($assocName, $options, $model);
    }


    /*##########################################################################
    # Accessors
    ##########################################################################*/

    /**
     * Get the method used to declare the of this association
     *
     * Example:
     * <code>
     *  $this->hasMany('Documents');
     *  // returns 'hasMany'
     * </code>
     *
     * @return  string
     */
    public function getMacro()
    {
        if (!isset($this->_macro)) {
            $this->_macro = Mad_Support_Inflector::camelize(
                str_replace('Mad_Model_Association_', '', get_class($this)), 'lower'
            );
        }
        return $this->_macro;
    }

    /**
     * Get all options for this association
     *
     * Example:
     * <code>
     *  $this->hasMany('Documents', array('foreignKey' => 'documentid'));
     *  // returns array('foreignKey' => 'documentid')
     * </code>
     *
     * @return  array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Get the name given when the association is created.
     *
     * Example:
     * <code>
     *  $this->hasMany('Documents');
     *  // returns 'Documents'
     *
     *  $this->hasMany('ChildDocuments', array('className' => 'Document'));
     *  // returns 'ChildDocuments'
     * </code>
     *
     * @return  string
     */
    public function getAssocName()
    {
        return $this->_assocName;
    }

    /**
     * The class name is determined based on the class in which the
     *
     * Example:
     * in models/Folder.php
     * <code>
     *  $this->hasMany('Documents');
     *  // or
     *  $this->hasMany('ChildDocuments', array('className' => 'Document'));
     *  // returns 'Folder'
     * </code>
     *
     * @return  string
     */
    public function getClass()
    {
        return get_class($this->_model);
    }

    /**
     * Get the model in which the association was defined
     *
     * Example:
     * in models/Folder.php
     * <code>
     *  $this->hasMany('Documents');
     *  // returns a Folder object
     * </code>
     *
     * @return  Mad_Model_Base
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * Get the table for the object
     *
     * Example:
     * in models/Folder.php
     * <code>
     *  $this->hasMany('Documents');
     *  // or
     *  $this->hasMany('ChildDocuments', array('className' => 'Document'));
     *  // returns 'folders'
     * </code>
     *
     * @return  string
     */
    public function tableName()
    {
        return $this->_model->tableName();
    }

    /**
     * Get the value for the primaryKey
     *
     * @return  string
     */
    public function getPkValue()
    {
        // belongsTo association is reversed so we use the foreignKey
        if ($this->getMacro() == 'belongsTo') {
            $attribute = $this->getFkName();
        } else {
            $attribute = $this->getPkName();
        }
        return $this->getModel()->readAttribute($attribute);
    }

    /**
     * The class name is determined based on the string passed into the association
     *  declaration unless specified in the options
     *
     * Example:
     * <code>
     *  $this->hasMany('Documents');
     *  // or
     *  $this->hasMany('ChildDocuments', array('className' => 'Document'));
     *  // returns 'Document'
     * </code>
     *
     * @return  string
     */
    public function getAssocClass()
    {
        if (!isset($this->_assocClass)) {
            $className = $this->_options['className'];

            // name of the association and model
            if (empty($className)) {
                $plural = ($this->getMacro() == 'hasMany' || 
                           $this->getMacro() == 'hasManyThrough' || 
                           $this->getMacro() == 'hasAndBelongsToMany');
                $className = $plural ? Mad_Support_Inflector::singularize($this->_assocName) : $this->_assocName;
            }
            $this->_assocClass = $className;

            if (!class_exists($this->_assocClass)) {
                $msg = "Invalid association declaration. The class $this->_assocClass doesn't exist";
                throw new Mad_Model_Association_Exception($msg);
            }
        }
        return $this->_assocClass;
    }

    /**
     * Get the model used for the association
     *
     * Example:
     * <code>
     *  $this->hasMany('Documents');
     *  // returns a Document object
     * </code>
     *
     * @return  Mad_Model_Base
     */
    public function getAssocModel()
    {
        if (!isset($this->_assocModel)) {
            $class = $this->getAssocClass();
            $this->_assocModel = new $class;
        }
        return $this->_assocModel;
    }


    /**
     * Get the table for the associated object
     *
     * Example:
     * <code>
     * class Folder
     * {
     *   ...
     *   $this->hasMany('Documents');
     *   // or
     *   $this->hasMany('ChildDocuments', array('className' => 'Document'));
     *   // returns 'documents'
     *   ...
     * }
     * </code>
     *
     * @return  string
     */
    public function getAssocTable()
    {
        if (!isset($this->_assocTable)) {
            $this->_assocTable = $this->getAssocModel()->tableName();
        }
        return $this->_assocTable;
    }

    /**
     * The primary key for the model table. This is determined
     * automatically by the data object if not given in options.
     * 
     * Example:
     * in Folder.php
     * <code>
     *  $this->hasMany('Documents');
     *  // returns 'folderid'
     *
     *  $this->hasMany('ChildDocuments', array('primaryKey' => 'parent_folderid'));
     *  // returns 'parent_folderid'
     * </code>
     *
     * @return  string
     */
    public function getPkName()
    {
        if (!isset($this->_pkName)) {
            $macro = $this->getMacro();

            // key was given in options
            if (!empty($this->_options['primaryKey'])) {
                $this->_pkName = $this->_options['primaryKey'];

            } elseif ($macro == 'belongsTo') {
                $this->_pkName = $this->getAssocModel()->primaryKey();

            } else {
                $this->_pkName = $this->getModel()->primaryKey();
            }
        }
        return $this->_pkName;
    }

    /**
     * The foreign key for the model table. This is determined
     * automatically by the data object if not given in options.
     *
     * Example:
     * in Folder.php
     * <code>
     *  $this->hasMany('Documents');
     *  // returns 'folderid'
     *
     *  $this->hasMany('ChildDocuments', array('foreignKey' => 'parent_folderid'));
     *  // returns 'parent_folderid'
     * </code>
     *
     * @return  string
     */
    public function getFkName()
    {
        if (!isset($this->_fkName)) {
            $macro = $this->getMacro();

            // key was given in options
            if (!empty($this->_options['foreignKey'])) {
                $this->_fkName = $this->_options['foreignKey'];

            } elseif ($macro == 'belongsTo') {
                $associationName = Mad_Support_Inflector::underscore($this->getAssocName());
                $this->_fkName = "{$associationName}_id";
            } else {
                $table = $this->getModel()->tableName();
                $singularTable = Mad_Support_Inflector::singularize($table);
                $this->_fkName = "{$singularTable}_id";
            }
        }
        return $this->_fkName;
    }

    /**
     * The primary key for the associated model table. This is determined
     * automatically by the data object if not given in options.
     *
     * Example:
     * <code>
     *  $this->hasMany('Documents');
     *  // returns 'documentid'
     *
     *  $this->hasMany('ChildDocuments', array('foreignKey' => 'parent_documentid'));
     *  // returns 'parent_documentid'
     * </code>
     *
     * @return  string
     */
    public function getAssocPkName()
    {
        if (!isset($this->_assocPkName)) {
            // key was given in options
            if (!empty($this->_options['associationPrimaryKey'])) {
                $this->_assocPkName = $this->_options['associationPrimaryKey'];
            } else {
                $this->_assocPkName = $this->getAssocModel()->primaryKey();
            }
        }
        return $this->_assocPkName;
    }

    /**
     * The foreign key name for the join table in an association. This is determined
     * automatically by the data object if not given in options.
     *
     * Example:
     * <code>
     *  $this->hasAndBelongsToMany('Documents', array('associationForeignKey' => 'documentid'));
     *  // returns 'documentid'
     * </code>
     *
     * @return  string
     */
    public function getAssocFkName()
    {
        if (!isset($this->_assocFkName)) {
            // key was given in options
            if (!empty($this->_options['associationForeignKey'])) {
                $this->_assocFkName = $this->_options['associationForeignKey'];
            } else {
                $table = $this->getAssocModel()->tableName();
                $singularTable = Mad_Support_Inflector::singularize($table);
                $this->_assocFkName = "{$singularTable}_id";
            }
        }
        return $this->_assocFkName;
    }

    /**
     * Get the join table for hasAndBelongsToMany associations. Default to
     * tablename1_tablename2 by alpha (briefcases_documents)
     *
     * Example:
     * <code>
     * class Binder
     * ...
     *  $this->hasAndBelongsToMany('Documents');
     * ...
     *  // returns 'briefcases_documents' (by default)
     * </code>
     *
     * @return  string
     */
    public function getJoinTable()
    {
        if (!isset($this->_joinTable)) {
            $macro = $this->getMacro();

            // join table was given in options
            if (!empty($this->_options['joinTable'])) {
                $this->_joinTable = $this->_options['joinTable'];

            // join table from through association
            } elseif (!empty($this->_options['through'])) {
                $class = Mad_Support_Inflector::classify($this->_options['through']);
                $model = new $class;
                $this->_joinTable = $model->tableName();

            // determine table name by convention from DO data
            } elseif ($macro == 'hasAndBelongsToMany') {
                $tbls = array($this->_model->tableName(), $this->getAssocTable());
                sort($tbls);
                $this->_joinTable = implode('_', $tbls);

            // no join table
            } else {
                $this->_joinTable = null;
            }
        }
        return $this->_joinTable;
    }

    /**
     * List of dynamic methods that are added when the association is created. It
     * shows the dynamic method's mapping to the association method
     *
     * Example:
     * <code>
     *  $this->belongsTo('Folder');
     *
     *  // returns array('folder'       => 'getObject',
     *  //               'folder='      => 'setObject',
     *  //               'buildFolder'  => 'buildObject',
     *  //               'createFolder' => 'createObject');
     * </code>
     *
     * @return  array
     */
    public function getMethods()
    {
        return $this->_methods;
    }

    /**
     * An object is considered loaded once the assocation model has been
     * populated from hitting the database, or explicitly flagged as loaded
     *
     * @return  boolean
     */
    abstract public function isLoaded();

    /**
     * Set that this association's object data as loaded
     *
     * @param   boolean $loaded
     */
    abstract public function setLoaded($loaded=true);

    /**
     * Check if the associated object has changed
     *
     * @return  boolean
     */
    abstract public function isChanged();

    /**
     * Flag the association as being changed
     *
     * @param   boolean $changed
     */
    public function setChanged($changed=true)
    {
        $this->_changed = $changed;
    }

    /**
     * Destroy all objects that are dependent on the base object based on their
     * dependency options. This only applies to hasOne/hasMany/HABTM associations.
     */
    public function destroyDependent()
    {
        // this is re-implemented in subclasses that need to use it
        // no code should be here
    }

    /**
     * Save changes to association objects. This will only save the object changes if it
     * has been loaded up from the database and was changed.
     */
    abstract public function save();


    /*##########################################################################
    # Magic methods call this to method to get results
    ##########################################################################*/

    /**
     * Dynamically call an association method by name/args
     * @param   string  $name
     * @param   array   $args
     * @return  mixed
     */
    public function callMethod($name, $args)
    {
        // get the local name of the method we need to call & call it
        $methodName = $this->_methods[$name];
        return $this->$methodName($args);
    }
}
