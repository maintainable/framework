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
class Mad_Model_Validation_InclusionTest extends Mad_Test_Unit
{
    public function setUp()
    {
        $this->model = new UnitTest();
    }

    // validate invalid options
    public function testConstructorRejectsInvalidOptions()
    {
        try {
            $options = array('invalid_option' => 'test');
            Mad_Model_Validation_Base::factory('inclusion', 'string_value', $options);
            $this->fail();    
        } catch (InvalidArgumentException $e) {
            $this->assertRegExp('/invalid_option/', $e->getMessage());
        }
    }

    public function testConstructorRejectsInclusionListIfNotArrayOrIterable()
    {
        try {
            $options = array('in' => 'foo');
            Mad_Model_Validation_Base::factory('inclusion', 'string_value', $options);
            $this->fail();    
        } catch (InvalidArgumentException $e) {
            $this->assertRegExp('/array or traversable/', $e->getMessage());
        }
    }

    public function testValidatePassesNullAttributeWithAllowNullOptionOn()
    {
        $options = array('in' => array('foo'), 'allowNull' => true);
        $validator = Mad_Model_Validation_Base::factory('inclusion', 'string_value', $options);

        $this->model->string_value = null;
        $validator->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }
    
    public function testValidateFailsNullAttributeWithAllowNullOptionOff()
    {
        $options = array('in' => array('foo'), 'allowNull' => false);
        $validator = Mad_Model_Validation_Base::factory('inclusion', 'string_value', $options);

        $this->model->string_value = null;
        $validator->validate('save', $this->model);
        $this->assertEquals(array('is not included in the list'), $this->model->errors->on('string_value'));
    }

    public function testValidatePassesNonStrictComparisonWhenItemIsInArrayList()
    {
        $options = array('in' => array(false));
        $validator = Mad_Model_Validation_Base::factory('inclusion', 'string_value', $options);

        $this->model->string_value = '0';
        $validator->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    public function testValidatePassesNonStrictComparisonWhenItemIsInTraversableList()
    {
        $options = array('in' => new ArrayObject(array(false)));
        $validator = Mad_Model_Validation_Base::factory('inclusion', 'string_value', $options);

        $this->model->string_value = '0';
        $validator->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    public function testValidateFailsNonStrictComparisonWhenItemIsInArrayList()
    {
        $options = array('in' => array(1));
        $validator = Mad_Model_Validation_Base::factory('inclusion', 'string_value', $options);

        $this->model->string_value = false;
        $validator->validate('save', $this->model);
        $this->assertEquals(array('is not included in the list'), $this->model->errors->on('string_value'));
    }

    public function testValidateFailsNonStrictComparisonWhenItemIsInTraversableList()
    {
        $options = array('in' => new ArrayObject(array(1)));
        $validator = Mad_Model_Validation_Base::factory('inclusion', 'string_value', $options);
        $this->model->string_value = false;

        $validator->validate('save', $this->model);
        $this->assertEquals(array('is not included in the list'), $this->model->errors->on('string_value'));
    }

    public function testValidatePassesStrictComparisonWhenItemIsInArrayList()
    {
        $options = array('in' => array('0'), 'strict' => true);
        $validator = Mad_Model_Validation_Base::factory('inclusion', 'string_value', $options);
        $this->model->string_value = '0';

        $validator->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    public function testValidatePassesStrictComparisonWhenItemIsInTraversableList()
    {
        $options = array('in' => new ArrayObject(array('0')), 'strict' => true);
        $validator = Mad_Model_Validation_Base::factory('inclusion', 'string_value', $options);
        $this->model->string_value = '0';

        $validator->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }
    
    public function testValidateFailsStrictComparisonWhenItemIsNotInArrayList()
    {
        $options = array('in' => array(false), 'strict' => true);
        $validator = Mad_Model_Validation_Base::factory('inclusion', 'string_value', $options);
        $this->model->string_value = '0';

        $validator->validate('save', $this->model);
        $this->assertEquals(array('is not included in the list'), $this->model->errors->on('string_value'));
    }

    public function testValidateFailsStrictComparisonWhenItemIsNotInTraversableList()
    {
        $options = array('in' => new ArrayObject(array(false)), 'strict' => true);
        $validator = Mad_Model_Validation_Base::factory('inclusion', 'string_value', $options);
        $this->model->string_value = '0';

        $validator->validate('save', $this->model);
        $this->assertEquals(array('is not included in the list'), $this->model->errors->on('string_value'));
    }

    public function testValidateDisplaysDefaultMessage()
    {
        $options = array('in' => array('bar'));
        $validator = Mad_Model_Validation_Base::factory('inclusion', 'string_value', $options);
        $this->model->string_value = 'foo';

        $validator->validate('save', $this->model);
        $this->assertEquals(array('is not included in the list'), $this->model->errors->on('string_value'));
    }
    
    public function testValidateCanDisplayCustomMessage()
    {
        $options = array('in'      => array('bar'),
                         'message' => 'custom');
        $validator = Mad_Model_Validation_Base::factory('inclusion', 'string_value', $options);
        $this->model->string_value = 'foo';

        $validator->validate('save', $this->model);
        $this->assertEquals(array('custom'), $this->model->errors->on('string_value'));

    }
}
