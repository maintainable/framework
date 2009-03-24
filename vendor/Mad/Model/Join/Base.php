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
class Mad_Model_Join_Base
{
    /**
     * The model for this join
     * @var Mad_Model_Base
     */
    protected $_model = null;

    /**
     * The column names with alias column. Select columns will incorporate
     * TO_CHAR() functions on date columns.
     *
     * @var array
     */
    protected $_columnNamesWithAliasForSelect = null;


    /**
     * The column names with alias column. extract columns are in all caps
     * so that we refer to data correctly in result set rows
     *
     * @var array
     */
    protected $_columnNamesWithAliasForExtract = null;


    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * Construct base join
     * @param   object  $model
     */
    public function __construct(Mad_Model_Base $model)
    {
        $this->_model = $model;
    }

    /**
     * Stringified version of the object
     * @return  string
     */
    public function __toString()
    {
        return 'Base: '.get_class($this->_model).' '.$this->aliasedPrefix();
    }


    /*##########################################################################
    # Public
    ##########################################################################*/

    /**
     * Mad_Model_Base associated with this join
     * @return  Mad_Model_Base
     */
    public function model()
    {
        return $this->_model;
    }

    /**
     * Get the list of associations classes available for the model
     * @param   string
     * @return  array
     */
    public function reflections($name=null)
    {
        return $this->_model->reflectOnAssociation($name);
    }

    /**
     * Base join alias is always first
     * @return  string
     */
    public function aliasedPrefix()
    {
        return "T0";
    }

    /**
     * Primary key is always first col
     * @return  string
     */
    public function aliasedPrimaryKey()
    {
        return $this->aliasedPrefix()."_R0";
    }

    /**
     * Get the table name for the model
     * @return  string
     */
    public function tableName()
    {
        return $this->_model->tableName();
    }

    /**
     * Base join alias is direct name of the table
     * @return  string
     */
    public function aliasedTableName()
    {
        return $this->_model->tableName();
    }

    /**
     * Get the list of columns with associated aliases for the select statement
     * @return  array
     */
    public function columnNamesWithAliasForSelect()
    {
        if (empty($this->_columnNamesWithAliasForSelect)) {
            $cols = $this->_model->getColumns($this->aliasedTableName(), false, false);
            foreach ($cols as $i => $col) {
                $this->_columnNamesWithAliasForSelect[] =
                    array($col, $this->aliasedPrefix().'_R'.$i);
            }
        }
        return $this->_columnNamesWithAliasForSelect;
    }

    /**
     * Get the list of columns with associated aliases
     */
    public function columnNamesWithAliasForExtract()
    {
        if (empty($this->_columnNamesWithAliasForExtract)) {
            foreach ($this->_model->getColumns() as $i => $col) {
                $this->_columnNamesWithAliasForExtract[] =
                    array($col, $this->aliasedPrefix().'_R'.$i);
            }
        }
        return $this->_columnNamesWithAliasForExtract;
    }

    /**
     * Extract the model record hash from the database result set
     * @param   array   $row
     * @return  array
     */
    public function extractRecord($row)
    {
        foreach ($this->columnNamesWithAliasForExtract() as $colAlias) {
            list($col, $alias) = $colAlias;
            $record[$col] = isset($row[$alias]) ? $row[$alias] : null;
        }
        return isset($record) ? $record : array();
    }

    /**
     * Get id of the record based on pk value
     * @param   array   $row
     * @return  int
     */
    public function recordId($row)
    {
        return $row[$this->aliasedPrimaryKey()];
    }

    /**
     * Instantiate the model with the attributes
     * @param   array   $row
     * @return  object
     */
    public function instantiate($row)
    {
        if (!isset($this->_cachedRecord[$this->recordId($row)])) {
            $model = $this->_model->instantiate($this->extractRecord($row));
            $this->_cachedRecord[$this->recordId($row)] = $model;
        }
        return $this->_cachedRecord[$this->recordId($row)];
    }
}
