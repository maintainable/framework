<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt 
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
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_Validation_NumericalityTest extends Mad_Test_Unit
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
            $validation = Mad_Model_Validation_Base::factory('numericality', 'name_value', $options);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertRegExp('/unknown key/i', $e->getMessage());
        }
    }

    // validate numericality
    public function testNumericalityInvalid()
    {
        $options    = array();
        $validation = Mad_Model_Validation_Base::factory('numericality', 'integer_value', $options);

        $this->model->integer_value = 'abc';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is not a number'), $this->model->errors->on('integer_value'));
    }

    // validate numericality
    public function testNumericalityInteger()
    {
        $options    = array();
        $validation = Mad_Model_Validation_Base::factory('numericality', 'integer_value', $options);

        $this->model->integer_value = '123';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('integer_value'));
    }

    // validate numericality
    public function testNumericalityNegative()
    {
        $options    = array();
        $validation = Mad_Model_Validation_Base::factory('numericality', 'integer_value', $options);

        $this->model->integer_value = '-123';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('integer_value'));
    }

    // validate numericality
    public function testNumericalityFloat()
    {
        $options    = array('onlyInteger' => false);
        $validation = Mad_Model_Validation_Base::factory('numericality', 'integer_value', $options);

        $this->model->integer_value = '1.23';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('integer_value'));
    }


    // validate numericality
    public function testNumericalityOnlyIntegerTrue()
    {
        $options    = array('onlyInteger' => true);
        $validation = Mad_Model_Validation_Base::factory('numericality', 'integer_value', $options);

        $this->model->integer_value = '123';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('integer_value'));
    }

    // validate numericality
    public function testNumericalityOnlyIntegerFalse()
    {
        $options    = array('onlyInteger' => true);
        $validation = Mad_Model_Validation_Base::factory('numericality', 'integer_value', $options);

        $this->model->integer_value = '1.23';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is not a number'), $this->model->errors->on('integer_value'));
    }

    // validate numericality
    public function testNumericalityAllowNullFalse()
    {
        $options    = array('allowNull' => false);
        $validation = Mad_Model_Validation_Base::factory('numericality', 'integer_value', $options);

        $this->model->integer_value = null;
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is not a number'), $this->model->errors->on('integer_value'));
    }

    // validate numericality
    public function testNumericalityAllowNullTrue()
    {
        $options    = array('allowNull' => true);
        $validation = Mad_Model_Validation_Base::factory('numericality', 'integer_value', $options);

        $this->model->integer_value = null;
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('integer_value'));
    }

    // validate numericality
    public function testNumericalityAllowNullTrueEmptyString()
    {
        $options    = array('allowNull' => true);
        $validation = Mad_Model_Validation_Base::factory('numericality', 'integer_value', $options);

        $this->model->integer_value = '';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('integer_value'));
    }

    // custom error message
    public function testNumericalityCustomMessage()
    {
        $options    = array('message' => 'has to be un numero!');
        $validation = Mad_Model_Validation_Base::factory('numericality', 'integer_value', $options);

        $this->model->integer_value = 'abc';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('has to be un numero!'), $this->model->errors->on('integer_value'));
    }


    /*##########################################################################
    ##########################################################################*/
}