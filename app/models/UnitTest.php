<?php

class UnitTest extends Mad_Model_Base
{
    public $setter = null;

    // relationships and validation
    public function _initialize()
    {
        $this->validatesFormatOf('string_value', array('with' => '/^[a-z0-9 ]*$/i'));
        $this->validatesLengthOf('integer_value', array('within' => array(1, 5)));
        $this->validatesNumericalityOf('integer_value');
        $this->validatesPresenceOf('integer_value');
        $this->validatesUniquenessOf('integer_value');
        $this->validatesEmailAddress('email_value');

        // test accessors
        $this->attrReader('fooValue', 'barValue', 'foo_value', 'bar_value');
        $this->attrWriter('fooValue', 'barValue', 'foo_value', 'bar_value');
        $this->attrAccessor('bazValue', 'fuzzValue', 'baz_value', 'fuzz_value');
    }

    /*##########################################################################
    # Accessor Methods
    ##########################################################################*/

    /**
     * Test the setting of proxy writer method
     */
    public function setFooValue($value)
    {
        $this->_attrValues['foo'] = str_replace(",", "", $value);
    }

    /**
     * Test the setting of proxy reader method
     */
    public function getFooValue()
    {
        return "|".$this->_attrValues['foo']."|";
    }

    /**
     * Test throwing valid
     */
    public function testMethodValidationA()
    {
        $this->errors->add('base', 'test throwing single error');
        throw new Mad_Model_Exception_Validation($this->errors->fullMessages());
    }

    /**
     * Test throwing valid
     */
    public function testMethodValidationB()
    {
        $this->errors->add('base', 'test first error');
        $this->errors->add('base', 'test second error');
        throw new Mad_Model_Exception_Validation($this->errors->fullMessages());
    }


    /*##########################################################################
    # Validation Methods
    ##########################################################################*/

    /**
     * Validate data whenever saved
     */
    protected function validate()
    {
        if ($this->string_value == '9999') {
            $this->errors->add('string_value', 'cannot be "9999"');
        }
    }

    /**
     * Validate data whenever creates are performed
     */
    protected function validateOnCreate()
    {
        if ($this->text_value == 'text test') {
            $this->errors->add('text_value', 'cannot be test');
        }
    }

    /**
     * Validate data whenever updates are performed
     */
    protected function validateOnUpdate()
    {
        if ($this->string_value == 'string test') {
            $this->errors->add('string_value', 'cannot be test');
        }
    }

    /*##########################################################################
    # Callback Methods
    ##########################################################################*/

    /**
     * Execute method before save of data
     */
    protected function beforeSave()
    {
        if ($this->string_value == 'before save test') {
            $this->errors->add('string_value', 'cannot be renamed to before save test');
            return false;
        }
    }

    /**
     * Execute method before creation of data
     */
    protected function beforeCreate()
    {
        if ($this->string_value == 'before create test') {
            $this->errors->add('string_value', 'cannot be renamed to before create test');
            return false;
        }
    }

    /**
     * Execute method before update of data
     */
    protected function beforeUpdate()
    {
        if ($this->string_value == 'before update test') {
            $this->errors->add('string_value', 'cannot be renamed to before update test');
            return false;
        }
    }

    /**
     * Execute method before deletion of data
     */
    protected function beforeDestroy()
    {
        if ($this->string_value == 'before destroy test') {
            $this->errors->add('string_value', 'cannot be renamed to before destroy test');
            return false;
        }
    }
}

?>