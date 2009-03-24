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
class Mad_Model_Validation_PresenceTest extends Mad_Test_Unit
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
            $validation = Mad_Model_Validation_Base::factory('presence', 'string_value', $options);
            $e->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertRegExp('/unknown key/i', $e->getMessage());
        }
    }


    // validate presence
    public function testPresenceTrue()
    {
        $options    = array();
        $validation = Mad_Model_Validation_Base::factory('presence', 'string_value', $options);

        $this->model->string_value = 'test';
        $validation->validate('save', $this->model);
        $this->assertEquals(array(), $this->model->errors->on('string_value'));
    }

    // validate presence
    public function testPresenceEmptyString()
    {
        $options    = array();
        $validation = Mad_Model_Validation_Base::factory('presence', 'string_value', $options);

        $this->model->string_value = '';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('can\'t be empty'), $this->model->errors->on('string_value'));
    }

    // validate presence
    public function testPresenceNull()
    {
        $options    = array();
        $validation = Mad_Model_Validation_Base::factory('presence', 'string_value', $options);

        $this->model->string_value = null;
        $validation->validate('save', $this->model);
        $this->assertEquals(array('can\'t be empty'), $this->model->errors->on('string_value'));
    }

    // validate presence
    public function testPresenceZero()
    {
        $options    = array();
        $validation = Mad_Model_Validation_Base::factory('presence', 'string_value', $options);

        $this->model->string_value = 0;
        $validation->validate('save', $this->model);
        $this->assertEquals(array('can\'t be empty'), $this->model->errors->on('string_value'));
    }

    // validate presence
    public function testPresenceZeroStr()
    {
        $options    = array();
        $validation = Mad_Model_Validation_Base::factory('presence', 'string_value', $options);

        $this->model->string_value = '0';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('can\'t be empty'), $this->model->errors->on('string_value'));
    }

    // custom 'message'
    public function testPresenceCustomMessage()
    {
        $options    = array('message' => 'is empty foo!');
        $validation = Mad_Model_Validation_Base::factory('presence', 'string_value', $options);

        $this->model->string_value = '';
        $validation->validate('save', $this->model);
        $this->assertEquals(array('is empty foo!'), $this->model->errors->on('string_value'));
    }


    /*##########################################################################
    ##########################################################################*/
}