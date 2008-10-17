<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Validation
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */

/**
 * Format validation rule for attributes of models before save/insert/update
 *
 * Options:
 *  - on:      string  save, create, or update. Defaults to: save
 *  - scope:   string  Limits the check to rows having the same value in the column
 *                     as the row being checked.
 *  - message: string  Defaults to: "The value for %s has already been taken."
 *
 * @see     Mad_Model_Base::validatesUniquenessOf
 * 
 * @category   Mad
 * @package    Mad_Model
 * @subpackage Validation
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Validation_Uniqueness extends Mad_Model_Validation_Base
{
    /*##########################################################################
    # Construct/Destruct
    ##########################################################################*/

    /**
     * Construct Validation rule
     *
     * @param   array   $attribute
     * @param   array   $options
     */
    public function __construct($attribute, $options)
    {
        // verify options
        $valid = array('on' => 'save', 'scope',
                       'message' => Mad_Model_Errors::$defaultErrorMessages['taken']);
        $this->_options = Mad_Support_Base::assertValidKeys($options, $valid);
        $this->_attribute = $attribute;
    }


    /*##########################################################################
    # Validation
    ##########################################################################*/

    /**
     * Validate attribute on save
     * @param   string  $column
     * @param   string  $value
     */
    protected function _validate($column, $value)
    {
        $conditions = "$column = :val";
        $bindVars[':val'] = $value;

        // insert - filter by pk as well (don't match own record)
        if (!$this->_model->isNewRecord()) {
            $conditions .= ' AND '.$this->_model->primaryKey().' <> :pk';
            $bindVars[':pk'] = $this->_model->id;
        }

        // scoped
        if ($scope = $this->_options['scope']) {
            $conditions .= " AND $scope = :scopeVal";
            $bindVars[':scopeVal'] = $this->_model->readAttribute($scope);
        }

        $model = $this->_model->find('first', array('conditions' => $conditions), $bindVars);
        if ($model) {
            $this->_model->errors->add($column, $this->_options['message']);
        }
    }

}
