<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Join
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Join
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Join_Association extends Mad_Model_Join_Base
{
    protected $_reflection           = null;
    protected $_parent               = null;
    protected $_aliasedTableName     = null;
    protected $_aliasPrefix          = null;
    protected $_aliasedJoinTableName = null;
    protected $_parentTableName      = null;


    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * Construct new Join Association.
     *
     * @param   object  $reflection
     * @param   object  $joinDependency
     * @param   object  $parent
     */
    public function __construct(Mad_Model_Association_Base $reflection, 
                                Mad_Model_Join_Dependency $joinDependency,
                                Mad_Model_Join_Base $parent)
    {
        parent::__construct($reflection->getAssocModel());

        $this->_parent           = $parent;
        $this->_reflection       = $reflection;
        $this->_aliasedPrefix    = 'T'.sizeof($joinDependency->joins());
        $this->_aliasedTableName = $this->tableName(); // start with table name
        $this->_parentTableName  = $parent->model()->tableName();

        // if the table name has been used, then use an alias
        $alias = $this->_aliasedTableName;
        if ($joinDependency->tableAlias($this->_aliasedTableName)) {
            $alias = $reflection->getAssocTable()."_$this->_parentTableName";
            $this->_aliasedTableName = $this->_model->connection->tableAliasFor($alias);

            // make sure to get a unique name. careful of name restrictions
            $alias = $this->_aliasedTableName;
            $i = $joinDependency->tableAlias($alias);
            if ($i > 0) {
                $maxLen = $this->_model->connection->tableAliasLength() - 3;
                $this->_aliasedTableName = substr($this->_aliasedTableName, 0, $maxLen)."_".($i+1);
            }

        }
        $joinDependency->addTableAlias($alias);

        // create alias for join table (only should be executed for hasAndBelongsToMany|belongsTo
        if ($this->_aliasedJoinTableName = $this->_reflection->getJoinTable()) {
            if ($joinDependency->tableAlias($this->_aliasedJoinTableName)) {
                $alias = $reflection->getAssocTable().'_'.$this->_parentTableName.'_join';
                $this->_aliasedJoinTableName = $this->_model->connection->tableAliasFor($alias);

                // make sure to get a unique name. careful of oracle name restrictions
                $i = $joinDependency->tableAlias($this->_aliasedJoinTableName);
                if ($i > 0) {
                    $maxLen = $this->_model->connection->tableAliasLength() - 3;
                    $this->_aliasedJoinTableName = substr($this->_aliasedJoinTableName, 0, $maxLen)."_".($i+1);
                }
            }
            $joinDependency->addTableAlias($this->_aliasedJoinTableName);
        }
    }

    /**
     * Stringified version of the object
     * @return  string
     */
    public function __toString()
    {
        return get_class($this->_reflection->getModel()).' : '.
               get_class($this->_reflection).' : '.
               get_class($this->_model).' '.
               $this->aliasedPrefix();
    }


    /*##########################################################################
    # Public
    ##########################################################################*/

    /**
     * @return  Mad_Model_Association_Base
     */
    public function reflection()
    {
        return $this->_reflection;
    }

    /**
     * @return  Mad_Model_Join_Base
     */
    public function parent()
    {
        return $this->_parent;
    }

    /**
     * @return  string
     */
    public function aliasedTableName()
    {
        return $this->_aliasedTableName;
    }

    /**
     * @return  string
     */
    public function aliasedPrefix()
    {
        return $this->_aliasedPrefix;
    }

    /**
     * @return  string
     */
    public function aliasedJoinTableName()
    {
        return $this->_aliasedJoinTableName;
    }

    /**
     * @return  string
     */
    public function parentTableName()
    {
        return $this->_parentTableName;
    }


    /**
     * Get the association join
     */
    public function associationJoin()
    {
        $macro = $this->reflection()->getMacro();

        // HABTM
        if ($macro == 'hasAndBelongsToMany' || $macro == 'hasManyThrough') {
            // eg. Binders hasAndBelongsToMany Documents
            // JOIN binders_documents ON binders_documents.binder_id = binders.id
            // JOIN documents ON documents.id = binders_documents.document_id
            $join =
            sprintf(" LEFT OUTER JOIN %s ON %s.%s = %s.%s ",
                $this->_tableAliasFor($this->_reflection->getJoinTable(), $this->aliasedJoinTableName()),
                $this->aliasedJoinTableName(),
                $this->reflection()->getFkName(),
                $this->reflection()->tableName(),
                $this->reflection()->getPkName()).
            sprintf(" LEFT OUTER JOIN %s ON %s.%s = %s.%s ",
                $this->_tableNameAndAlias(),
                $this->aliasedTableName(),
                $this->reflection()->getAssocPkName(),
                $this->aliasedJoinTableName(),
                $this->reflection()->getAssocFkName());

        // hasMany/hasOne
        } elseif ($macro == 'hasMany' || $macro == 'hasOne') {
            // eg. Folders hasMany Documents
            // JOIN documents ON documents.folder_id = folders.id
            $join = sprintf(" LEFT OUTER JOIN %s ON %s.%s = %s.%s ",
                $this->_tableNameAndAlias(),
                $this->aliasedTableName(),
                $this->reflection()->getFkName(),
                $this->parent()->aliasedTableName(),
                $this->reflection()->getPkName());

        // belongsTo
        } elseif ($macro == 'belongsTo') {
            // use join table.
            if ($this->reflection()->getJoinTable()) {
                // eg. BinderCollection belongsTo VisibilityGroup
                // JOIN visibility_collections
                //  ON visibility_collections.collectionid = briefcase_collections.collectionid
                // JOIN user_visibility_groups
                //  ON user_visibility_groups.visibility_groupid = visibility_collections.visibility_groupid
                $join =
                sprintf(" LEFT OUTER JOIN %s ON %s.%s = %s.%s ",
                    $this->_tableAliasFor($this->_reflection->getJoinTable(), $this->aliasedJoinTableName()),
                    $this->aliasedJoinTableName(),
                    $this->reflection()->getFkName(),
                    $this->reflection()->tableName(),
                    $this->reflection()->getPkName()).
                sprintf(" LEFT OUTER JOIN %s ON %s.%s = %s.%s ",
                    $this->_tableNameAndAlias(),
                    $this->aliasedTableName(),
                    $this->reflection()->getAssocPkName(),
                    $this->aliasedJoinTableName(),
                    $this->reflection()->getAssocFkName());


            // no join table
            } else {
                // eg. Document belongsTo Folder
                // JOIN folders ON folders.id = documents.folder_id
                $join = sprintf(" LEFT OUTER JOIN %s ON %s.%s = %s.%s ",
                    $this->_tableNameAndAlias(),
                    $this->aliasedTableName(),
                    $this->reflection()->getPkName(),
                    $this->parent()->aliasedTableName(),
                    $this->reflection()->getFkName());
            }
        }

        return isset($join) ? $join : null;
    }

    /*##########################################################################
    # Private
    ##########################################################################*/

    /**
     * Concatenate table name/alias if needed
     * @param   string  $tableName
     * @param   string  $tableAlias
     * @return  string
     */
    private function _tableAliasFor($tableName, $tableAlias)
    {
        return $tableName . ($tableName != $tableAlias ? " $tableAlias" : null);
    }

    /**
     * Generate a string with tablename/tableAlias
     * @return  string
     */
    private function _tableNameAndAlias()
    {
        return $this->_tableAliasFor($this->tableName(), $this->_aliasedTableName);
    }
}
