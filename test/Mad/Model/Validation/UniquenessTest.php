<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD 
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
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Validation_UniquenessTest extends Mad_Test_Unit
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
            $validation = Mad_Model_Validation_Base::factory('uniqueness', 'string_value', $options);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertRegExp('/unknown key/i', $e->getMessage());
        }
    }

    public function testUniqueValid()
    {
        $this->fixtures('unit_tests');

        $options    = array();
        $validation = Mad_Model_Validation_Base::factory('uniqueness', 'string_value', $options);

        $this->model->string_value = 'unique name';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    public function testUniqueInvalid()
    {
        $this->fixtures('unit_tests');

        $options    = array();
        $validation = Mad_Model_Validation_Base::factory('uniqueness', 'string_value', $options);

        $this->model->string_value = 'name a';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('has already been taken'), $this->model->errors->on('string_value'));
    }

    public function testUniqueScopeValid()
    {
        $this->fixtures('unit_tests');

        $options    = array('scope' => 'text_value');
        $validation = Mad_Model_Validation_Base::factory('uniqueness', 'string_value', $options);

        $this->model->text_value   = 'string a';
        $this->model->string_value = 'name b';

        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    public function testUniqueScopeInvalid()
    {
        $this->fixtures('unit_tests');

        $options    = array('scope' => 'text_value');
        $validation = Mad_Model_Validation_Base::factory('uniqueness', 'string_value', $options);

        $this->model->text_value   = 'string a';
        $this->model->string_value = 'name a';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('has already been taken'), $this->model->errors->on('string_value'));
    }

    public function testUniqueScopesArrayValid()
    {
        $this->fixtures('unit_tests');

        $options    = array('scope' => array('text_value', 'email_value'));
        $validation = Mad_Model_Validation_Base::factory('uniqueness', 'string_value', $options);

        $this->model->text_value   = 'string a';
        $this->model->string_value = 'name a';
        $this->model->email_value  = 'bar@example.com';

        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    public function testUniqueScopeArrayInvalid()
    {
        $this->fixtures('unit_tests');

        $options    = array('scope' => array('text_value', 'email_value'));
        $validation = Mad_Model_Validation_Base::factory('uniqueness', 'string_value', $options);

        $this->model->text_value   = 'string a';
        $this->model->string_value = 'name a';
        $this->model->email_value  = 'foo@example.com';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('has already been taken'), $this->model->errors->on('string_value'));
    }

    public function testUniqueUpdateValid()
    {
        $this->fixtures('unit_tests');
        $this->model = UnitTest::find(1);

        $options    = array();
        $validation = Mad_Model_Validation_Base::factory('uniqueness', 'string_value', $options);

        $this->model->string_value   = 'name a';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    public function testUniqueUpdateInvalid()
    {
        $this->fixtures('unit_tests');
        $this->model = UnitTest::find(1);

        $options    = array();
        $validation = Mad_Model_Validation_Base::factory('uniqueness', 'string_value', $options);

        $this->model->string_value   = 'name b';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('has already been taken'), $this->model->errors->on('string_value'));
    }

    public function testUniqueCustomMessage()
    {
        $this->fixtures('unit_tests');

        $options    = array('message' => 'already exists. Choose another');
        $validation = Mad_Model_Validation_Base::factory('uniqueness', 'string_value', $options);

        $this->model->string_value = 'name a';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('already exists. Choose another'), $this->model->errors->on('string_value'));
    }


    /*##########################################################################
    ##########################################################################*/
}