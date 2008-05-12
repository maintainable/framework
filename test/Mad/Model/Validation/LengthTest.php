<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config/environment.php';
}

/**
 * @group      model
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential
 */
class Mad_Model_Validation_LengthTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->model = new UnitTest();
    }

    // validate invalid options
    public function testInvalidOptions()
    {
        try {
            $options = array('invalid_option' => 'test');
            $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertRegExp('/unknown key/i', $e->getMessage());
        }
    }

    public function testLengthValidMinimum()
    {
        $options    = array('minimum' => 5);
        $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

        $this->model->string_value = 'abcde';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    public function testLengthInvalidMinimum()
    {
        $options    = array('minimum' => 5);
        $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

        $this->model->string_value = 'abc';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is too short (minimum is 5 characters)'), $this->model->errors->on('string_value'));
    }

    public function testLengthValidMaximum()
    {
        $options    = array('maximum' => 5);
        $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

        $this->model->string_value = 'abc';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    public function testLengthInvalidMaximum()
    {
        $options    = array('maximum' => 5);
        $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

        $this->model->string_value = 'abcdef';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is too long (maximum is 5 characters)'), $this->model->errors->on('string_value'));
    }

    public function testLengthValidIsSpecificLen()
    {
        $options    = array('is' => 5);
        $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

        $this->model->string_value = 'abcde';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    public function testLengthInvalidIsSpecificLen()
    {
        $options    = array('is' => 5);
        $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

        $this->model->string_value = 'abcd';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is the wrong length (should be 5 characters)'), $this->model->errors->on('string_value'));
    }

    public function testLengthValidWithinRange()
    {
        $options    = array('within' => array(3, 5));
        $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

        $this->model->string_value = 'abcd';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    public function testLengthInvalidWithinRange()
    {
        try {
            $options    = array('within' => 5);
            $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

            $this->model->string_value = 'abcdef';
            $validation->validate('save', $this->model);

            $this->fail();
        } catch (InvalidArgumentException $e) {}
    }

    public function testLengthInvalidWithinRangeTooHigh()
    {
        $options    = array('within' => array(3, 5));
        $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

        $this->model->string_value = 'abcdef';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is too long (maximum is 5 characters)'), $this->model->errors->on('string_value'));
    }

    public function testLengthInvalidWithinRangeTooLow()
    {
        $options    = array('within' => array(3, 5));
        $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

        $this->model->string_value = 'ab';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is too short (minimum is 3 characters)'), $this->model->errors->on('string_value'));
    }

    public function testLengthAllowNullFalse()
    {
        $options    = array('minimum' => 5, 'allowNull' => false);
        $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

        $this->model->string_value = null;
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is too short (minimum is 5 characters)'), $this->model->errors->on('string_value'));
    }

    public function testLengthAllowNullTrue()
    {
        $options    = array('minimum' => 3, 'allowNull' => true);
        $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

        $this->model->string_value = null;
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    public function testLengthCustomTooShort()
    {
        $options    = array('minimum' => 5, 'tooShort' => 'is too short [make it > %d]');
        $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

        $this->model->string_value = 'abc';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is too short [make it > 5]'), $this->model->errors->on('string_value'));
    }

    public function testLengthCustomTooLong()
    {
        $options    = array('maximum' => 5, 'tooLong' => 'is too long [make it < %d]');
        $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

        $this->model->string_value = 'abcdef';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is too long [make it < 5]'), $this->model->errors->on('string_value'));
    }

    public function testLengthCustomWrongLength()
    {
        $options    = array('is' => 5, 'wrongLength' => 'is totally wrong. must be %d');
        $validation = Mad_Model_Validation_Base::factory('length', 'string_value', $options);

        $this->model->string_value = 'abcd';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is totally wrong. must be 5'), $this->model->errors->on('string_value'));
    }

    /*##########################################################################
    ##########################################################################*/
}