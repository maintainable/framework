<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD 
 */

/**
 * Set environment
 */
if (!defined('MAD_ENV')) define('MAD_ENV', 'test');
if (!defined('MAD_ROOT')) {
    require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config/environment.php';
}

/**
 * @todo Tests for sanitizeSql()
 * 
 * @group      model
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_BaseTest extends Mad_Test_Unit
{
    // set up new db by inserting dummy data into the db
    public function setUp()
    {
        $this->fixtures('unit_tests');
    }

    /*##########################################################################
    # Establish connection
    ##########################################################################*/

    // connect with no opts
    public function testEstablishConnection()
    {
        $conn = Mad_Model_Base::establishConnection();
    }

    // connect with string
    public function testEstablishConnectionString()
    {
        $conn = Mad_Model_Base::establishConnection('development');
    }

    // connect with array
    public function testEstablishConnectionArray()
    {
        $conn = Mad_Model_Base::establishConnection(array());
    }


    /*##########################################################################
    # Serialization
    ##########################################################################*/
    
    public function testSleep()
    {
        $test = UnitTest::find($this->unit_tests('unit_test_1')->id);

        $serialized = serialize($test);
        $test2 = unserialize($serialized);
        
        $this->assertEquals($test->getAttributes(), $test2->getAttributes());
    }

    public function testToParamWithEmptyPrimaryKeyReturnsNull()
    {
        $model = new UnitTest();
        $this->assertNull($model->id);
        $this->assertNull($model->toParam());
    }

    public function testToParamReturnsStringCoercedPrimaryKey()
    {
        $model = UnitTest::find($this->unit_tests('unit_test_1')->id);
        $expected = (string)$model->id;
        $actual   = $model->toParam();
        $this->assertSame($expected, $actual);
    }


    /*##########################################################################
    # Namespaces
    ##########################################################################*/

    public function testModelNamespaces()
    {
        $this->setFixtureClass(array('fax_jobs'       => 'Fax_Job', 
                                     'fax_recipients' => 'Fax_Recipient'));
        $this->fixtures('fax_jobs', 'fax_recipients');

        $job = Fax_Job::find($this->fax_jobs('fax_job_1')->id);
        $this->assertType("Fax_Job", $job);
        $this->assertEquals('fax_jobs', $job->tableName());
    }


    /*##########################################################################
    # DB Table column/keys
    ##########################################################################*/

    // test getting table
    public function testTableName()
    {
        $test = new UnitTest();
        $this->assertEquals('unit_tests', $test->tableName());
    }
    
    // test getting table
    public function testTableNameForStiModels()
    {
        $client = new Client();
        $this->assertEquals('users', $client->tableName());
    }

    public function testSetTableName()
    {
        $test = new UnitTest();
        $test->setTableName('hey');
        $this->assertEquals('hey', $test->tableName());
    }

    public function testResetTableName()
    {
        $test = new UnitTest();

        $test->setTableName('hey');
        $this->assertEquals('hey', $test->tableName());

        $test->resetTableName();
        $this->assertEquals('unit_tests', $test->tableName());
    }

    // test getting pk
    public function testPrimaryKey()
    {
        $test = new UnitTest();
        $this->assertEquals('id', $test->primaryKey());
    }

    public function testSetPrimaryKey()
    {
        $test = new UnitTest();
        $this->assertEquals('id', $test->primaryKey());

        $test->setPrimaryKey('asdf');
        $this->assertEquals('asdf', $test->primaryKey());
    }

    public function testResetPrimaryKey()
    {
        $test = new UnitTest();
        $this->assertEquals('id', $test->primaryKey());

        $test->setPrimaryKey('asdf');
        $this->assertEquals('asdf', $test->primaryKey());

        $test->resetPrimaryKey();
        $this->assertEquals('id', $test->primaryKey());
    }


    // test getting inheritance column
    public function testInheritanceColumn()
    {
        $test = new UnitTest();
        $this->assertEquals($test->inheritanceColumn(), 'type');
    }

    public function testSetInheritanceColumn()
    {
        $test = new UnitTest();
        $test->setInheritanceColumn('foofoo');
        $this->assertEquals($test->inheritanceColumn(), 'foofoo');
    }


    public function testColumns()
    {
        $test = new UnitTest();
        $columns = $test->columns();
        $this->assertEquals(13, count($columns));
        
        foreach ($columns as $col) {
            $this->assertType('Mad_Model_ConnectionAdapter_Mysql_Column', $col);
        }
    }

    public function testColumnsHash()
    {
        $test = new UnitTest();
        $columns = $test->columnsHash();
        $this->assertEquals(13, count($columns));
        $this->assertType('Mad_Model_ConnectionAdapter_Mysql_Column', $columns['id']);
    }

    public function testColumnNames()
    {
        $test = new UnitTest();
        $columns = $test->columnNames();
        $this->assertEquals(13, count($columns));

        $expected = array('id', 'integer_value', 'string_value', 'text_value', 
                          'float_value', 'decimal_value', 'datetime_value', 
                          'date_value', 'time_value', 'blob_value', 
                          'boolean_value', 'enum_value', 'email_value');
        $this->assertEquals($expected, $columns);
    }


    /*##########################################################################
    # Attribute Accessors
    ##########################################################################*/

    public function testReadAttribute()
    {
        $test = UnitTest::find($this->unit_tests('unit_test_1')->id);
        $this->assertEquals($this->unit_tests('unit_test_1')->id, $test->readAttribute('id'));
    }

    public function testWriteAttribute()
    {
        $test = UnitTest::find($this->unit_tests('unit_test_1')->id);
        $test->writeAttribute('string_value', 'asdf');
        $this->assertEquals('asdf', $test->string_value);
    }

    public function testAttrReaderWriter()
    {
        $test = new UnitTest;

        $test->foo_value = "hey, there";
        $this->assertEquals("|hey there|", $test->foo_value);

        $test->bar_value = "hey, there";
        $this->assertEquals("hey, there", $test->bar_value);
    }

    public function testAttrReaderWriterCamel()
    {
        $test = new UnitTest;

        $test->fooValue = "hey, there";
        $this->assertEquals("|hey there|", $test->fooValue);

        $test->barValue = "hey, there";
        $this->assertEquals("hey, there", $test->barValue);
    }

    public function testAttrAccessor()
    {
        $test = new UnitTest;

        $test->baz_value = "hey, there";
        $this->assertEquals("hey, there", $test->baz_value);

        $test->fuzz_value = "hey, there";
        $this->assertEquals("hey, there", $test->fuzz_value);
    }

    public function testAttrAccessorCamel()
    {
        $test = new UnitTest;

        $test->bazValue = "hey, there";
        $this->assertEquals("hey, there", $test->bazValue);

        $test->fuzzValue = "hey, there";
        $this->assertEquals("hey, there", $test->fuzzValue);
    }

    /*##########################################################################
    # Attribute Accessors
    ##########################################################################*/

    public function testGetHumanAttributeName()
    {
        $test = new UnitTest;
        $this->assertEquals('String value', $test->humanAttributeName('string_value'));
    }

    // test getting fields
    public function testGetAttributes()
    {
        $test = new UnitTest();
        $attributes = $test->getAttributes();

        $this->assertTrue(count($attributes) > 0);
        foreach ($attributes as $key => $value) {
            $this->assertTrue(is_string($key));
        }
    }

    public function testInstantiateRecord()
    {
        $test = new UnitTest();
        $model = $test->instantiate(array('id' => 'asdf'));
        
        $this->assertType('UnitTest', $model);
        $this->assertEquals('asdf', $model->id);
    }

    public function testInstantiateStiRecord()
    {
        $test = new User();
        $model = $test->instantiate(array('id' => '123', 'type' => 'Client'));

        $this->assertType('Client', $model);
        $this->assertEquals('123', $model->id);
    }

    public function testGetAttributeNames()
    {
        $user = new User;
        $expected = array('approved', 'company_id', 'created_at', 'created_on', 
                          'first_name', 'id', 'name', 'type', 'updated_at', 'updated_on');
        $this->assertEquals($expected, $user->attributeNames());
    }

    public function testGetColumnForAttribute()
    {
        $user = new User;
        $col = $user->columnForAttribute('name');
        $this->assertType('Mad_Model_ConnectionAdapter_Mysql_Column', $col);
        
        $this->assertEquals('string', $col->getType());
    }

    public function testSetValuesByRowColumn()
    {
        $test = new UnitTest();
        $test->setValuesByRow(array('id' => 'asdf'));

        $this->assertEquals('asdf', $test->id);
    }

    public function testSetValuesByRowWriter()
    {
        $test = new UnitTest();
        $test->setValuesByRow(array('foo_value' => 'derek, mike'));

        $this->assertEquals('|derek mike|', $test->foo_value);
    }


    /*##########################################################################
    # Construction
    ##########################################################################*/

    // test initiating the object by pk
    public function testInitByPk()
    {
        $test = new UnitTest(1);
        $this->assertEquals('1', $test->id);
    }

    // test initiating the object by pk
    public function testInitByStringPk()
    {
        $test = new UnitTest('1');
        $this->assertEquals('1', $test->id);
    }


    /*##########################################################################
    # Deprecated column accessors
    ##########################################################################*/

    // get array of columns
    public function testGetColumns()
    {
        $test = new UnitTest();
        $expected = array('id', 'integer_value', 'string_value', 'text_value', 
                          'float_value', 'decimal_value', 'datetime_value', 
                          'date_value', 'time_value', 'blob_value', 
                          'boolean_value', 'enum_value', 'email_value');
        $this->assertEquals($expected, $test->getColumns());
    }

    // test getting colStr
    public function testGetColumnStr()
    {
        $test = new UnitTest();
        $expected = '`id`, `integer_value`, `string_value`, `text_value`, `float_value`, '.
                    '`decimal_value`, `datetime_value`, `date_value`, `time_value`, '.
                    '`blob_value`, `boolean_value`, `enum_value`, `email_value`';
        $this->assertEquals($expected, $test->getColumnStr());
    }

    // test getting colStr with table alias
    public function testGetColumnStrAlias()
    {
        $test = new UnitTest();
        $expected = '`t`.`id`, `t`.`integer_value`, `t`.`string_value`, `t`.`text_value`, `t`.`float_value`, '.
                    '`t`.`decimal_value`, `t`.`datetime_value`, `t`.`date_value`, `t`.`time_value`, '.
                    '`t`.`blob_value`, `t`.`boolean_value`, `t`.`enum_value`, `t`.`email_value`';
        $this->assertEquals($expected, $test->getColumnStr('t'));
    }

    // test getting value string/bind
    public function testGetValuesStr()
    {
        $test = new UnitTest(array('id'            => 100,
                                   'integer_value' => 1000,
                                   'string_value'  => 'My Name'));
        
        $values = "'100', '1000', 'My Name', NULL, '0.0', '0.0', '0000-00-00 00:00:00', '0000-00-00', '00:00:00', NULL, '0', 'a', ''";
        $this->assertEquals($values, $test->getInsertValuesStr());
    }

    // test getting value string/bind
    public function testGetQuotedValuesStr()
    {
        $test = new UnitTest(array('id'            => 100,
                                   'integer_value' => 1000,
                                   'string_value'  => "Derek's Name"));

        $values = "'100', '1000', 'Derek\'s Name', NULL, '0.0', '0.0', '0000-00-00 00:00:00', '0000-00-00', '00:00:00', NULL, '0', 'a', ''";
        $this->assertEquals($values, $test->getInsertValuesStr());
    }


    /*##########################################################################
    # Test getting/setting attributes
    ##########################################################################*/

    // test __isset() of attributes
    public function testGetIssetAttribute()
    {
        $test = UnitTest::find(2);

        $this->assertTrue(!empty($test->id));
        $this->assertTrue(empty($test->boolean_value));

        $this->assertTrue(isset($test->id));
        $this->assertTrue(!isset($test->fake_col));
    }

    // test __isset() of association based attributes
    public function testGetIssetHasManyAssociationAttribute()
    {
        $this->fixtures('users', 'articles');
        $user = User::find(2);
        $this->assertFalse(empty($user->articles));
            
        // remove all associated objects & recheck
        Article::deleteAll();
        $user = User::find(2);
        $this->assertTrue(empty($user->articles));
    }

    // test __isset() of association based attributes
    public function testGetIssetBelongsToAssociationAttribute()
    {
        $this->fixtures('users', 'articles');
        $article = Article::find(1);
        $this->assertFalse(empty($article->user));

        // remove all associated objects & recheck
        User::deleteAll();
        $article = Article::find(1);
        $this->assertTrue(empty($article->user));
    }

    // test get nonexistent attributes
    public function testGetInvalidAttribute()
    {
        $test = UnitTest::find(1);

        try {
            $randomAttribute = $test->random_attribute;
            $this->fail();
        } catch (Mad_Model_Exception $e) {}

        $this->assertEquals("Unrecognized attribute 'random_attribute'", $e->getMessage());
    }

    // test get nonexistent attributes
    public function testSetInvalidAttribute()
    {
        $test = UnitTest::find(1);

        try {
            $test->random_attribute = 'test';
            $this->fail();
        } catch (Mad_Model_Exception $e) {}

        $this->assertEquals("Unrecognized attribute 'random_attribute'", $e->getMessage());
    }

    // test getting special pk attribute
    public function testGetPk()
    {
        $test = UnitTest::find(1);
        $this->assertEquals('1', $test->id);
    }

    // test setting special pk attribute for empty object
    public function testSetPkEmptyA()
    {
        $test = new UnitTest();
        $test->id = 12345;
        $this->assertEquals('12345', $test->id);
    }

    // test setting special pk attribute for empty object
    public function testSetPkEmptyB()
    {
        $test = new UnitTest();
        $test->id = 12345;
        $this->assertEquals('12345', $test->id);

        $test->id = 67890;
        $this->assertEquals('67890', $test->id);
    }

    // test setting special pk attribute when it already exists
    public function testSetPkExists()
    {
        $test = UnitTest::find(1);

        $test->id = 12345;
        
        $this->assertEquals(1, $test->id);
    }


    /*##########################################################################
    # Test If data has changed OR new
    ##########################################################################*/

    // test if new record
    public function testIsNewRecordA()
    {
        $unitTest = UnitTest::find(1);
        $this->assertFalse($unitTest->isNewRecord());
    }

    // test if new record
    public function testIsNewRecordB()
    {
        $unitTest = new UnitTest();
        $this->assertTrue($unitTest->isNewRecord());
    }

    // saving flags it as not a new record anymore
    public function testIsNewRecordC()
    {
        $test = new UnitTest(array('id' => '10',
                                   'integer_value' => 7,
                                   'string_value'  => 'name f', 
                                   'email_value'   => 'foo@example.com'));
        $this->assertTrue($test->isNewRecord());

        $test->save();
        $this->assertFalse($test->isNewRecord());
    }

    //
    public function testSetIsAssocChanged()
    {
        $test = UnitTest::find(1);
        $this->assertFalse($test->isAssocChanged());

        $test->setIsAssocChanged(true);
        $this->assertTrue($test->isAssocChanged());
    }



    /*##########################################################################
    # Test SELECT using find() by pk(s)
    ##########################################################################*/

    // test finding all diff types of data
    public function testFindAllTypes()
    {
        $unitTest = UnitTest::find(4);
        $this->assertEquals('4',                   $unitTest->id);
        $this->assertEquals('4',                   $unitTest->integer_value);
        $this->assertEquals('name d',              $unitTest->string_value);
        $this->assertEquals('string b',            $unitTest->text_value);
        $this->assertEquals('1.2',                 $unitTest->float_value);
        $this->assertEquals('1.2',                 $unitTest->decimal_value);
        $this->assertEquals('2005-12-23 12:34:23', $unitTest->datetime_value);
        $this->assertEquals('2005-12-23',          $unitTest->date_value);
        $this->assertEquals('12:34:23',            $unitTest->time_value);
        $this->assertEquals('some blob data',      $unitTest->blob_value);
        $this->assertEquals('0',                   $unitTest->boolean_value);
        $this->assertEquals('b',                   $unitTest->enum_value);
    }

    // test find by pk
    public function testFindBlob()
    {
        $test = UnitTest::find(6);
        $this->assertEquals(strlen($this->unit_tests('unit_test_6')->blob_value), strlen($test->blob_value));
    }

    // test find by pk not found
    public function testFindEmpty()
    {
        try {
            $test = UnitTest::find(null);
            $this->fail();
        } catch (Mad_Model_Exception_RecordNotFound $e) {}

        $msg = "Couldn't find UnitTest without an ID";
        $this->assertEquals($msg, $e->getMessage());
    }

    // test find by pk
    public function testFindPk()
    {
        $test = UnitTest::find(1);
        $this->assertEquals('name a', $test->string_value);
    }

    // test find by pk
    public function testFindPkString()
    {
        $test = UnitTest::find('1');
        $this->assertEquals('name a', $test->string_value);
    }

    // test find by pk with space
    public function testFindPkSpace()
    {
        $test = UnitTest::find('1   ');
        $this->assertEquals('name a', $test->string_value);
    }

    // test find by multiple pks
    public function testFindPks()
    {
        $tests = UnitTest::find(array(1, 5));

        $this->assertEquals('name a', $tests[0]->string_value);
        $this->assertEquals('name e', $tests[1]->string_value);
    }

    // test find by multiple pks
    public function testFindPksOneElement()
    {
        $tests = UnitTest::find(array(1));

        $this->assertEquals('name a', $tests[0]->string_value);
    }

    // test find by pk not found
    public function testFindPkNotFound()
    {
        try {
            $test = UnitTest::find(123);
            $this->fail();
        } catch (Mad_Model_Exception_RecordNotFound $e) {}

        $msg = "The record for id=123 was not found";
        $this->assertEquals($msg, $e->getMessage());
    }

    // test find by pk not found
    public function testFindPksNotFound()
    {
        try {
            $test = UnitTest::find(array(1, 123));
            $this->fail();
        } catch (Mad_Model_Exception_RecordNotFound $e) {}

        $msg = "A record id IN (1, 123) was not found";
        $this->assertEquals($msg, $e->getMessage());
    }

    // test if pk exists
    public function testExistsPkTrue()
    {
        $this->assertTrue(UnitTest::exists(1));
    }

    // test if pk exists
    public function testExistsPkFalse()
    {
        $this->assertFalse(UnitTest::exists(123));
    }


    /*##########################################################################
    # Test SELECT using find() 'all'
    ##########################################################################*/

    // test finding all records
    public function testFindAll()
    {
        $tests = UnitTest::find('all');
        $this->assertEquals(6, $tests->count());
    }

    // test finding data using WHERE condition
    public function testFindAllConditions()
    {
        $tests = UnitTest::find('all', array('conditions' => "text_value='string a'"));
        $this->assertEquals(2, $tests->count());
    }

    // test finding data using WHERE condition with BIND variables
    public function testFindAllConditionsBind()
    {
        $tests = UnitTest::find('all', array('conditions' => "text_value=:text"),
                                       array(':text' => 'string a'));
        $this->assertEquals('1', $tests[0]->id);
    }

    // test finding data using WHERE condition with AND
    public function testFindAllConditionsAnd()
    {
        $tests = UnitTest::find('all', array('conditions' => "text_value=:text AND
                                                              string_value=:str"),
                                       array(':text' => 'string a',
                                             ':str'  => 'name c'));
        $this->assertEquals('3', $tests[0]->integer_value);
    }

    // test finding data using WHERE condition with OR
    public function testFindAllConditionsOr()
    {
        $tests = UnitTest::find('all', array('conditions' => "text_value=:text OR
                                                              string_value=:str"),
                                       array(':text' => 'string a',
                                             ':str'  => 'name c'));
        $this->assertEquals(2, $tests->count());
    }

    // test finding data using 'select'
    public function testFindAllSelect()
    {
        // id value is null since we didn't select it
        $tests = UnitTest::find('all', array('select' => 'id'));
        $this->assertEquals('0', $tests[0]->integer_value);
    }

    // test finding data using ORDER BY
    public function testFindAllOrder()
    {
        // Retrieve using arbitrary conditions and ORDERING
        $tests = UnitTest::find('all', array('order' => 'integer_value DESC'));
        $this->assertEquals('6', $tests[0]->id);
        $this->assertEquals('5', $tests[1]->id);
        $this->assertEquals('4', $tests[2]->id);
        $this->assertEquals('3', $tests[3]->id);
        $this->assertEquals('2', $tests[4]->id);
        $this->assertEquals('1', $tests[5]->id);
    }

    // test finding data using LIMIT
    public function testFindAllLimit()
    {
        $tests = UnitTest::find('all', array('limit' => 2));
        $this->assertEquals(2, $tests->count());
    }

    // test finding data using only OFFSET with no LIMIT (wont' do anything)
    public function testFindAllOffset()
    {
        $tests = UnitTest::find('all', array('offset' => 2));
        $this->assertEquals(6, $tests->count());
    }

    // test finding data using LIMIT and OFFSET
    public function testFindAllLimitOffset()
    {
        $tests = UnitTest::find('all', array('offset' => 2,
                                             'limit'  => 2));
        $this->assertEquals(2, $tests->count());
    }

    // test finding data using WHERE conditions and ORDER BY
    public function testFindAllConditionsFrom()
    {
        // Retrieve using arbitrary conditions and ORDERING
        $tests = UnitTest::find('all', array('select'     => 'a.id',
                                             'from'       => 'unit_tests a, unit_tests b',
                                             'conditions' => 'a.id = b.id'));
        $this->assertEquals(6, $tests->count());
    }

    // test finding data using WHERE conditions and ORDER BY
    public function testFindAllConditionsOrder()
    {
        // Retrieve using arbitrary conditions and ORDERING
        $tests = UnitTest::find('all', array('conditions' => 'boolean_value = :bool',
                                             'order'      => 'integer_value DESC'),
                                       array(':bool' => '1'));
        $this->assertEquals('5', $tests[0]->integer_value);
        $this->assertEquals('3', $tests[1]->integer_value);
        $this->assertEquals('1', $tests[2]->integer_value);
    }

    // test finding data using WHERE conditions and LIMIT
    public function testFindAllConditionsLimit()
    {
        $tests = UnitTest::find('all', array('conditions' => 'boolean_value = :bool',
                                             'limit'      => 2),
                                       array(':bool' => '1'));
        $this->assertEquals(2, $tests->count());
    }

    // test finding data using WHERE conditions and LIMIT
    public function testFindAllConditionsOrderLimit()
    {
        $tests = UnitTest::find('all', array('conditions' => 'boolean_value = :bool',
                                             'order'      => 'integer_value DESC',
                                             'limit'      => 2),
                                       array(':bool' => '1'));
        $this->assertEquals(2, $tests->count());
        $this->assertEquals('5', $tests[0]->integer_value);
        $this->assertEquals('3', $tests[1]->integer_value);
    }

    // test finding data using LIMIT
    public function testFindAllConditionsLimitOffset()
    {
        $tests = UnitTest::find('all', array('conditions' => 'text_value = :text',
                                             'offset'     => 2,
                                             'limit'      => 1),
                                       array(':text' => 'string b'));
        $this->assertEquals(1, $tests->count());

    }

    // test finding data using LIMIT
    public function testFindAllConditionsOrderLimitOffset()
    {
        $tests = UnitTest::find('all', array('conditions' => 'text_value = :text',
                                             'order'      => 'integer_value DESC',
                                             'offset'     => 2,
                                             'limit'      => 2),
                                       array(':text' => 'string b'));
        $this->assertEquals(2, $tests->count());
        $this->assertEquals('4', $tests[0]->integer_value);
        $this->assertEquals('2', $tests[1]->integer_value);
    }


    /*##########################################################################
    # Test SELECT using find() first/count
    ##########################################################################*/

    // test finding all records
    public function testFindFirst()
    {
        $test = UnitTest::find('first');
        $this->assertType('UnitTest', $test);
    }

    // test finding first record
    public function testFindFirstConditions()
    {
        $test = UnitTest::find('first', array('conditions' => "boolean_value = '0'"));
        $this->assertType('UnitTest', $test);
        $this->assertTrue($test->integer_value == 2 || $test->integer_value == 4);
    }

    // test finding first record
    public function testFindFirstConditionsBind()
    {
        $test = UnitTest::find('first', array('conditions' => "string_value=:str"),
                                        array(':str' => 'name a'));
        $this->assertType('UnitTest', $test);
        $this->assertEquals('1', $test->integer_value);
    }

    // test finding data using 'select'
    public function testFindFirstSelect()
    {
        // id value is null since we didn't select it
        $test = UnitTest::find('first', array('select' => 'id'));
        $this->assertEquals('0', $test->integer_value);
    }

    // test finding first record using ORDER BY
    public function testFindFirstOrder()
    {
        $test = UnitTest::find('first', array('order' => 'integer_value DESC'));
        $this->assertType('UnitTest', $test);
        $this->assertEquals('6', $test->id);
    }

    // test finding first record
    public function testCount()
    {
        $testCnt = UnitTest::count();
        $this->assertEquals('6', $testCnt);
    }

    // test finding sum()
    public function testCountSum()
    {
        $testSum = UnitTest::count(array('select' => "SUM(integer_value)"));
        $this->assertEquals('21', $testSum);
    }

    // test finding first record
    public function testCountConditions()
    {
        $testCnt = UnitTest::count(array('conditions' => "boolean_value = '1'"));
        $this->assertEquals('3', $testCnt);
    }

    // test finding first record
    public function testCountConditionsString()
    {
        $testCnt = UnitTest::count("boolean_value = '1'");
        $this->assertEquals('3', $testCnt);
    }

    /*##########################################################################
    # Test SELECT using findBySql()
    ##########################################################################*/

    // test find by sql
    public function testFindAllBySql()
    {
        $sql = "SELECT id, integer_value FROM unit_tests WHERE string_value=:str";
        $tests = UnitTest::findBySql('all', $sql, array(':str' => 'name a'));
        $this->assertEquals(1,   count($tests));
        $this->assertEquals('1', $tests[0]->integer_value);
    }

    // test find first record sql
    public function testFindFirstBySql()
    {
        $sql = "SELECT id, integer_value FROM unit_tests
                 WHERE text_value=:text ORDER BY integer_value";
        $test = UnitTest::findBySql('first', $sql, array(':text' => 'string a'));
        $this->assertEquals('1', $test->integer_value);
    }

    // test count records by sql
    public function testFindCountBySql()
    {
        $sql = "SELECT count(1) FROM unit_tests WHERE text_value=:text";
        $testCnt = UnitTest::countBySql($sql, array(':text' => 'string a'));
        $this->assertEquals('2', $testCnt);
    }


    /*##########################################################################
    # Test Pagination
    ##########################################################################*/

    public function testPaginationFirstPage()
    {
        $this->fixtures('unit_tests_more');
        $this->assertEquals('11', UnitTest::count());

        // get 1st page
        $results = UnitTest::paginate(array('page' => 1, 'perPage' => 5));
        $this->assertEquals(5, count($results));

        $this->assertEquals(11, $results->totalEntries);
        $this->assertEquals(1,  $results->currentPage);
        $this->assertEquals(5,  $results->perPage);
        $this->assertEquals(3,  $results->pageCount);
        $this->assertEquals('1 - 5', $results->range);
    }

    public function testPaginationThirdPage()
    {
        $this->fixtures('unit_tests_more');
        $this->assertEquals('11', UnitTest::count());

        // get 3rd page
        $results = UnitTest::paginate(array('page' => 3, 'perPage' => 5));
        $this->assertEquals(1, count($results));

        $this->assertEquals(11, $results->totalEntries);
        $this->assertEquals(3,  $results->currentPage);
        $this->assertEquals(5,  $results->perPage);
        $this->assertEquals(3,  $results->pageCount);
        $this->assertEquals('11 - 11', $results->range);
    }

    public function testPaginationNoResults()
    {
        UnitTest::deleteAll();

        // get 3rd page
        $results = UnitTest::paginate(array('page' => 1, 'perPage' => 5));
        $this->assertEquals(0, count($results));

        $this->assertEquals(0, $results->totalEntries);
        $this->assertEquals(0,  $results->currentPage);
        $this->assertEquals(5,  $results->perPage);
        $this->assertEquals(0,  $results->pageCount);
        $this->assertEquals('0', $results->range);
    }


    /*##########################################################################
    # Test reloading attributes
    ##########################################################################*/

    public function testReload()
    {
        // get an object
        $test = UnitTest::find(1);
        $this->assertEquals('name a', $test->string_value);

        // update that object
        UnitTest::update(1, array('string_value' => 'name z'));

        // update isn't reflected
        $this->assertEquals('name a', $test->string_value);

        // reload values from db
        $test->reload();
        $this->assertEquals('name z', $test->string_value);
    }

    public function testReloadReturnsTheObjectHandleForChainingConvenience()
    {
        $test = UnitTest::find(1);
        $this->assertType('UnitTest', $test);
        $this->assertSame($test, $test->reload());
    }

    /*##########################################################################
    # Test INSERT using create() staticly
    ##########################################################################*/

    // test creating new records
    public function testCreate()
    {
        // create object
        $test = UnitTest::create(array('integer_value' => 7, 
                                       'string_value'  => 'name g', 
                                       'email_value'   => 'foo@example.com'));
        $pk = $test->id;

        // select to check if inserted
        $test2 = UnitTest::find('first', array('conditions' => "string_value = 'name g'"));
        $this->assertEquals('7', $test2->integer_value);
        $this->assertEquals($pk, $test2->id);
    }

    // test inserting blob value
    public function testCreateBlob()
    {
        $blobStr = $this->unit_tests('unit_test_6')->blob_value;
        $test = UnitTest::create(array('id' => 7, 
                                       'string_value'  => 'tester', 
                                       'email_value'   => 'foo@example.com',
                                       'integer_value' => 100, 
                                       'blob_value'    => $blobStr));

        $test2 = UnitTest::find(7);
        $this->assertEquals($blobStr, $test2->blob_value);
    }

    // test passing in of multiple records at once
    public function testCreateMultiple()
    {
        // create object
        $tests = UnitTest::create(array(array('integer_value' => 7, 
                                              'string_value'  => 'name g', 
                                              'email_value'   => 'foo@example.com'),
                                        array('integer_value' => 8, 
                                              'string_value'  => 'name h', 
                                              'email_value'   => 'foo@example.com')));

        // check 1st record
        $pk = $tests[0]->id;
        $test = UnitTest::find('first', array('conditions' => "string_value = 'name g'"));
        $this->assertEquals('7', $test->integer_value);
        $this->assertEquals($pk, $test->id);

        // check 2nd record
        $pk = $tests[1]->id;
        $test = UnitTest::find('first', array('conditions' => "string_value = 'name h'"));
        $this->assertEquals('8', $test->integer_value);
        $this->assertEquals($pk, $test->id);
    }

    // test creating new records
    public function testCreateAttributeNotExists()
    {
        try {
            $test = UnitTest::create(array('nonexistent_attribute' => 6));
            $this->fail();
        } catch (Mad_Model_Exception $e) {}

        $this->assertEquals("Unrecognized attribute 'nonexistent_attribute'", $e->getMessage());
    }


    /*##########################################################################
    # Test INSERT/UPDATE using save()
    ##########################################################################*/

    // test creating new records
    public function testSaveInsertConstructAttributesForce()
    {
        $test = new UnitTest(array('id'            => '10', 
                                   'integer_value' => 7, 
                                   'string_value'  => 'name g', 
                                   'email_value'   => 'foo@example.com'));
        $test->save();

        // check for record
        $test = UnitTest::find(10);
        $this->assertEquals('7', $test->integer_value);
        $this->assertEquals('10', $test->id);
    }

    // test creating new records
    public function testSaveInsertConstructAttributes()
    {
        $test = new UnitTest(array('integer_value' => 7, 
                                   'string_value'  => 'name g', 
                                   'email_value'   => 'foo@example.com'));
        $test->save();
        $pk = $test->id;

        // check for record
        $test = UnitTest::find('first', array('conditions' => "string_value = 'name g'"));
        $this->assertEquals('7', $test->integer_value);
        $this->assertEquals($pk, $test->id);
    }

    // test creating new records
    public function testSaveInsertSetAttributes()
    {
        $test = new UnitTest();
        $test->integer_value = 7;
        $test->string_value  = 'name g';
        $test->email_value   = 'foo@example.com'; 
        $test->save();
        $pk = $test->id;

        // check for record
        $test = UnitTest::find('first', array('conditions' => "string_value = 'name g'"));
        $this->assertEquals('7', $test->integer_value);
        $this->assertEquals($pk, $test->id);
    }

    // test saving blob value
    public function testSaveBlob()
    {
        $blobStr = $this->unit_tests('unit_test_6')->blob_value;
        $test = UnitTest::find(1);
        $test->blob_value = $blobStr;
        $test->save();

        $test2 = UnitTest::find(1);
        $this->assertEquals($blobStr, $test2->blob_value);
    }

    // force saving data that has an existing primary key
    public function testSaveInsertForcePk()
    {
        $test = new UnitTest();
        $test->id = 20;
        $test->string_value  = 'asdf';
        $test->integer_value = 123;
        $test->email_value   = 'foo@example.com';

        $test->save();
        $this->assertTrue(UnitTest::exists(20));
    }

    // Test setting zero as the primary key (should fail)
    public function testSaveInsertZeroOnPrimaryKey()
    {
        $test = UnitTest::create(array('id'            => 0, 
                                       'integer_value' => 123,
                                       'string_value'  => 'asdf', 
                                       'email_value'   => 'foo@example.com'));
        $this->assertFalse(UnitTest::exists(0));

        $test = UnitTest::find('first', array('conditions' => "string_value = 'asdf'"));
        $this->assertType('UnitTest', $test);
    }

    // test updating records
    public function testSaveAttributes()
    {
        $test = UnitTest::find('first', array('conditions' => "string_value = 'name a'"));
        $pk = $test->id;
        $test->string_value = 'name zzzz';
        $test->integer_value   = 100;

        $test->save();

        $test2 = UnitTest::find($pk);
        $this->assertEquals('100',       $test2->integer_value);
        $this->assertEquals('name zzzz', $test2->string_value);
    }

    // test the auto-columns
    public function testAutomaticColumnsInsert()
    {
        $user = new User;

        // all magic columns are null
        $this->assertEquals('0000-00-00 00:00:00', $user->created_at);
        $this->assertEquals('0000-00-00',          $user->created_on);
        $this->assertEquals('0000-00-00 00:00:00', $user->updated_at);
        $this->assertEquals('0000-00-00',          $user->updated_on);

        $user->save();

        // all magic columns are updated
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->created_on);
        $this->assertNotNull($user->updated_at);
        $this->assertNotNull($user->updated_on);
    }

    // test the auto-columns
    public function testAutomaticColumnsUpdate()
    {
        $this->fixtures('users');

        // updating record changes updated stamps, but not 
        $user = User::find($this->users('derek')->id);
        $createdAt = $user->created_at;
        $createdOn = $user->created_on;
        $updatedAt = $user->updated_at;
        $updatedOn = $user->updated_on;
        $user->save();

        // updating record changes updated_* stamps, but not created_*
        $user = User::find($this->users('derek')->id);
        $this->assertEquals($createdAt,    $user->created_at);
        $this->assertEquals($createdOn,    $user->created_on);
        $this->assertNotEquals($updatedAt, $user->updated_at);
        $this->assertNotEquals($updatedOn, $user->updated_on);
    }

    /*##########################################################################
    # Test UPDATEs
    ##########################################################################*/
    
    // @todo - fix saving of belongs to associated objects
    // Saving a change to a column of an associated belongs to object doesn't 
    // work when we include the object in the find. This has to do with saving
    // the associated object before we save the base object
    public function testUpdateAttributesWithAssociations()
    {
        $this->markTestSkipped();

        $this->fixtures('users', 'articles');

        $article = Article::find($this->articles('xml_rpc')->id, array('include' => 'User'));
        $this->assertEquals($this->users('mike')->id, $article->user_id);

        $article->updateAttributes(array('user_id' => $this->users('derek')->id));
        $article->save();

        $article = Article::find($this->articles('xml_rpc')->id);
        $this->assertEquals($this->users('derek')->id, $article->user_id);
    }

    // test updating one attribute
    public function testUpdateAttribute()
    {
        $test = UnitTest::find('first', array('conditions' => "string_value = 'name a'"));
        $test->updateAttribute('integer_value', 1000);

        $test2 = UnitTest::find($test->id);
        $this->assertEquals('1000', $test2->integer_value);
    }

    // test updating multiple attribute with array
    public function testUpdateAttributesWithArray()
    {
        $test = UnitTest::find('first', array('conditions' => "string_value = 'name a'"));
        $test->updateAttributes(array('integer_value'   => 1000,
                                      'string_value' => 'name zzzz'));

        $test2 = UnitTest::find($test->id);
        $this->assertEquals('1000',      $test2->integer_value);
        $this->assertEquals('name zzzz', $test2->string_value);
    }

    // test updating multiple attribute with traversable object
    public function testUpdateAttributesWithTraversable()
    {
        $test = UnitTest::find('first', array('conditions' => "string_value = 'name a'"));
        $test->updateAttributes(new ArrayObject(array('integer_value'   => 1000,
                                                      'string_value' => 'name zzzz')));

        $test2 = UnitTest::find($test->id);
        $this->assertEquals('1000',      $test2->integer_value);
        $this->assertEquals('name zzzz', $test2->string_value);
    }

    public function testUpdateAttributesWithoutArrayReturnsFalse()
    {
        $test = new UnitTest();
        $this->assertFalse($test->updateAttributes('should-be-array'));
    }

    // test updating record using update statically
    public function testUpdatePk()
    {
        $test = UnitTest::update(1, array('string_value' => 'derek name test'));
        $this->assertType('UnitTest', $test);

        // check if name was updated
        $test2 = UnitTest::find(1);
        $this->assertEquals('derek name test', $test2->string_value);
        $this->assertEquals('1',               $test2->integer_value);

    }

    // test updating record using update statically
    public function testUpdatePks()
    {
        $pks = array(1, 2, 3);
        $tests = UnitTest::update($pks, array('string_value' => 'derek name test'));
        $this->assertEquals(3, $tests->count());

        // check if names were updated
        $test = UnitTest::find(1);
        $this->assertEquals('derek name test', $test->string_value);
        $this->assertEquals('1',               $test->integer_value);

        $test = UnitTest::find(2);
        $this->assertEquals('derek name test', $test->string_value);
        $this->assertEquals('2',               $test->integer_value);

        $test = UnitTest::find(3);
        $this->assertEquals('derek name test', $test->string_value);
        $this->assertEquals('3',               $test->integer_value);
    }

    // test updating all records
    public function testUpdateAll()
    {
        $result = UnitTest::updateAll("string_value = 'new name'");

        // check if names were updated
        $test = UnitTest::find(2);
        $this->assertEquals('new name', $test->string_value);
        $this->assertEquals('2',        $test->integer_value);

        $test = UnitTest::find(3);
        $this->assertEquals('new name', $test->string_value);
        $this->assertEquals('3',        $test->integer_value);
    }

    // test updating all records using a condition statement
    public function testUpdateAllCondition()
    {
        $result = UnitTest::updateAll("string_value = 'new name'", "integer_value = 2");

        // check if name was updated
        $test = UnitTest::find(2);
        $this->assertEquals('new name', $test->string_value);
        $this->assertEquals('2',        $test->integer_value);

        // make sure only 1 was updated
        $test = UnitTest::find(1);
        $this->assertEquals('name a', $test->string_value);
        $this->assertEquals('1',      $test->integer_value);
    }

    // test updating all records using a condition statement & bind
    public function testUpdateAllConditionBind()
    {
        $result = UnitTest::updateAll("string_value = 'new name'", "integer_value = :id",
                                       array(':id' =>'2'));

        // check if name was updated
        $test = UnitTest::find(2);
        $this->assertEquals('new name', $test->string_value);
        $this->assertEquals('2',        $test->integer_value);

        // make sure only 1 was updated
        $test = UnitTest::find(1);
        $this->assertEquals('name a', $test->string_value);
        $this->assertEquals('1',      $test->integer_value);
    }


    /*##########################################################################
    # Test DELETES
    ##########################################################################*/

    // test destroy
    public function testDestroy()
    {
        $test = UnitTest::find(2);
        $test->destroy();

        $this->assertTrue($test->isDestroyed());

        // make sure the record doesn't exist anymore
        try {
            $test = UnitTest::find(2);
            $this->fail();
        } catch (Mad_Model_Exception_RecordNotFound $e) {
            $msg = 'The record for id=2 was not found';
            $this->assertEquals($msg, $e->getMessage());
        }
    }

    // test getting a attribute from a destroyed object
    public function testDestroyGet()
    {
        $test = UnitTest::find(2);
        $test->destroy();

        // try to get a attribute
        $this->assertEquals('2', $test->id);
    }

    // test setting a attribute for a destroyed object
    public function testDestroySet()
    {
        $test = UnitTest::find(2);
        $test->destroy();

        // try to set a attribute
        try {
            $test->integer_value = '345';
            $this->fail();
        } catch (Mad_Model_Exception $e) {
            $msg = "You cannot set attributes of a destroyed object";
            $this->assertEquals($msg, $e->getMessage());
        }
    }

    // test deleting by pk
    public function testDeletePk()
    {
        UnitTest::delete(3);

        try {
            $test = UnitTest::find(3);
            $this->fail();
        } catch (Mad_Model_Exception_RecordNotFound $e) {
            $msg = 'The record for id=3 was not found';
            $this->assertEquals($msg, $e->getMessage());
        }
    }

    // test deleting by multiple pk
    public function testDeletePks()
    {
        UnitTest::delete(array(4, 5));

        try {
            $test = UnitTest::find(4);
            $this->fail();
        } catch (Mad_Model_Exception_RecordNotFound $e) {
            $msg = 'The record for id=4 was not found';
            $this->assertEquals($msg, $e->getMessage());
        }

        try {
            $test = UnitTest::find(5);
            $this->fail();
        } catch (Mad_Model_Exception_RecordNotFound $e) {
            $msg = 'The record for id=5 was not found';
            $this->assertEquals($msg, $e->getMessage());
        }
    }

    // test deleting all records that match a given condition
    public function testDeleteAll()
    {
        UnitTest::deleteAll();
        $this->assertEquals('0', UnitTest::count());
    }

    // test deleting all records that match a given condition
    public function testDeleteAllConditions()
    {
        UnitTest::deleteAll("boolean_value = '1'");

        // make sure Y record is gone
        try {
            $test = UnitTest::find(3);
            $this->fail();
        } catch (Mad_Model_Exception_RecordNotFound $e) {
            $msg = 'The record for id=3 was not found';
            $this->assertEquals($msg, $e->getMessage());
        }

        // count the records
        $this->assertEquals('3', UnitTest::count());
    }

    // test deleting all records that match a given condition
    public function testDeleteAllConditionsBind()
    {
        UnitTest::deleteAll("boolean_value = :bool", array(':bool' => '1'));

        // make sure Y record is gone
        try {
            $test = UnitTest::find(3);
            $this->fail();
        } catch (Mad_Model_Exception_RecordNotFound $e) {
            $msg = 'The record for id=3 was not found';
            $this->assertEquals($msg, $e->getMessage());
        }

        // count the records
        $this->assertEquals('3', UnitTest::count());
    }


    /*##########################################################################
    # Test Validation without Exceptions
    ##########################################################################*/

    // test validation of all saves
    public function testValidationSaveInsert()
    {
        // integer_value is required, string_value can't be 9999
        $test = new UnitTest(array('integer_value' => '0',
                                   'string_value'  => '9999', 
                                   'email_value'   => 'foo@example.com'));

        $this->assertFalse($test->save());
        $this->assertEquals(2, count($test->errors));
        $this->assertTrue($test->errors->isInvalid('integer_value'));
        $this->assertTrue($test->errors->isInvalid('string_value'));
    }

    // test validation of all saves
    public function testValidationSaveUpdate()
    {
        // integer_value is required, string_value can't be 9999
        $test = UnitTest::find(1);
        $test->updateAttributes(array('integer_value' => '0',
                                      'string_value'  => '9999', 
                                      'email_value'   => 'foo@example.com'));

        $this->assertFalse($test->save());
        $this->assertEquals(2, count($test->errors));
        $this->assertTrue($test->errors->isInvalid('integer_value'));
        $this->assertTrue($test->errors->isInvalid('string_value'));
    }

    // test validation on create
    public function testValidationOnCreate()
    {
        $test = new UnitTest(array('integer_value' => '123',
                                   'text_value'    => 'text test', 
                                   'email_value'   => 'foo@example.com'));

        $this->assertFalse($test->save());
        $this->assertEquals(1, count($test->errors));
        $this->assertTrue($test->errors->isInvalid('text_value'));
    }

    // test validation on update
    public function testValidationOnUpdate()
    {
        $test = UnitTest::find(1);
        $test->updateAttributes(array('integer_value' => '123',
                                      'string_value'  => 'string test'));

        $this->assertFalse($test->save());
        $this->assertEquals(1, count($test->errors));
        $this->assertTrue($test->errors->isInvalid('string_value'));
    }


    /*##########################################################################
    # Test Validation Using Exceptions
    ##########################################################################*/

    // test validation of all saves
    public function testValidationSaveInsertException()
    {
        try {
            // integer_value is required, string_value can't be 9999
            $test = new UnitTest(array('integer_value' => '0',
                                       'string_value'  => '9999', 
                                       'email_value'   => 'foo@example.com'));
            $test->saveEx();
            $this->fail();
        } catch (Mad_Model_Exception_Validation $e) {
            // validation errors
            $this->assertEquals(2, count($test->errors));
        }
    }

    // test validation of all saves
    public function testValidationSaveUpdateException()
    {
        try {
            // integer_value is required, string_value can't be 9999
            $test = UnitTest::find(1);
            $test->updateAttributes(array('integer_value' => '0',
                                      'string_value'  => '9999', 
                                      'email_value'   => 'foo@example.com'));
            $test->saveEx();
            $this->fail();
        } catch (Mad_Model_Exception_Validation $e) {
            // validation errors
            $this->assertEquals(2, count($test->errors));
        }
    }

    // test validation on create
    public function testValidationOnCreateException()
    {
        try {
            $test = new UnitTest(array('integer_value' => '123',
                                       'text_value'    => 'text test', 
                                       'email_value'   => 'foo@example.com'));
            $test->saveEx();
            $this->fail();
        } catch (Mad_Model_Exception_Validation $e) {
            // validation errors
            $this->assertEquals(1, count($test->errors));

            foreach ($test->errors->fullMessages() as $msg) {
                $this->assertEquals('Text value cannot be test', $msg);
            }
        }
    }

    // test validation on update
    public function testValidationOnUpdateException()
    {
        try {
            $test = UnitTest::find(1);
            $test->updateAttributes(array('integer_value' => '123',
                                          'string_value'  => 'string test'));
            $test->saveEx();
            $this->fail();
        } catch (Mad_Model_Exception_Validation $e) {
            // validation errors
            $this->assertEquals(1, count($test->errors));

            foreach ($test->errors->fullMessages() as $msg) {
                $this->assertEquals('String value cannot be test', $msg);
            }
        }
    }

    // method validation (throwing exceptions within custom methods)
    public function testMethodValidationSingleException()
    {
        $test = UnitTest::find(1);

        try {
            $test->testMethodValidationA();
            $this->fail();
        } catch (Mad_Model_Exception_Validation $e) {
            $this->assertEquals(1, count($test->errors));
        }
    }

    // multiple errors thrown
    public function testMethodValidationMultipleException()
    {
        $test = UnitTest::find(1);

        try {
            $test->testMethodValidationB();
            $this->fail();
        } catch (Mad_Model_Exception_Validation $e) {
            $this->assertEquals(2, count($test->errors));

            $errors = $test->errors->fullMessages();
            $this->assertEquals('test first error', $errors[0]);
        }
    }


    /*##########################################################################
    # Test Callback Methods without Exceptions
    ##########################################################################*/

    // test execution of callback before saving insert
    public function testBeforeSaveInsert()
    {
        $test = new UnitTest(array('integer_value' => '123',
                                   'string_value'  => 'before save test', 
                                   'email_value'   => 'foo@example.com'));

        $this->assertFalse($test->save());
        $this->assertTrue($test->errors->isInvalid('string_value'));
    }

    // test execution of callback before saving update
    public function testBeforeSaveUpdate()
    {
        $test = UnitTest::find(1);
        $test->updateAttributes(array('integer_value' => '123',
                                      'string_value'  => 'before save test'));

        $this->assertFalse($test->save());
        $this->assertTrue($test->errors->isInvalid('string_value'));
    }

    // test execution of callback before insert
    public function testBeforeCreate()
    {
        $test = new UnitTest(array('integer_value' => '123',
                                   'string_value'  => 'before create test', 
                                   'email_value'   => 'foo@example.com'));

        $this->assertFalse($test->save());
        $this->assertTrue($test->errors->isInvalid('string_value'));
    }

    // test execution of callback before update
    public function testBeforeUpdate()
    {
        $test = UnitTest::find(1);
        $test->updateAttributes(array('integer_value' => '123',
                                      'string_value'  => 'before update test'));

        $this->assertFalse($test->save());
        $this->assertTrue($test->errors->isInvalid('string_value'));
    }

    // test execution of callback before destroy
    public function testBeforeDestroy()
    {
        $test = new UnitTest(array('integer_value' => '123',
                                   'string_value'  => 'before destroy test'));

        $this->assertFalse($test->destroy());
        $this->assertTrue($test->errors->isInvalid('string_value'));
    }


    /*##########################################################################
    # Test Callback Methods using exceptions
    ##########################################################################*/

    // test execution of callback before saving insert
    public function testBeforeSaveInsertException()
    {
        try {
            $test = new UnitTest(array('integer_value' => '123',
                                       'string_value'  => 'before save test', 
                                       'email_value'   => 'foo@example.com'));
            $test->saveEx();
            $this->fail();

        } catch (Mad_Model_Exception_Validation $e) {
            // validation errors
            $msgs = $e->getMessages();
            $this->assertEquals(1, sizeof($msgs));

            foreach ($msgs as $msg) {
                $this->assertEquals('String value cannot be renamed to before save test', $msg);
            }
        }
    }

    // test execution of callback before saving update
    public function testBeforeSaveUpdateException()
    {
        try {
            $test = UnitTest::find(1);
            $test->updateAttributes(array('integer_value' => '123',
                                          'string_value'  => 'before save test'));
            $test->saveEx();
            $this->fail();
        } catch (Mad_Model_Exception_Validation $e) {
            // validation errors
            $this->assertEquals(1, count($test->errors));

            foreach ($test->errors->fullMessages() as $msg) {
                $this->assertEquals('String value cannot be renamed to before save test', $msg);
            }
        }
    }

    // test execution of callback before insert
    public function testBeforeCreateException()
    {
        try {
            $test = new UnitTest(array('integer_value' => '123',
                                       'string_value'  => 'before create test', 
                                       'email_value'   => 'foo@example.com'));
            $test->saveEx();
            $this->fail();
        } catch (Mad_Model_Exception_Validation $e) {
            // validation errors
            $this->assertEquals(1, count($test->errors));

            foreach ($test->errors->fullMessages() as $msg) {
                $this->assertEquals('String value cannot be renamed to before create test', $msg);
            }
        }
    }

    // test execution of callback before update
    public function testBeforeUpdateException()
    {
        try {
            $test = UnitTest::find(1);
            $test->updateAttributes(array('integer_value' => '123',
                                          'string_value'  => 'before update test'));
            $test->saveEx();
            $this->fail();
        } catch (Mad_Model_Exception_Validation $e) {
            // validation errors
            $this->assertEquals(1, count($test->errors));

            foreach ($test->errors->fullMessages() as $msg) {
                $this->assertEquals('String value cannot be renamed to before update test', $msg);
            }
        }
    }


    /*##########################################################################
    ##########################################################################*/
}