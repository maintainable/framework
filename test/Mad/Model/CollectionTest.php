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
    require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config/environment.php';
}

/**
 * @group      model
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    http://maintainable.com/framework-license.txt
 */
class Mad_Model_CollectionTest extends Mad_Test_Unit
{
    // set up new db by inserting dummy data into the db
    public function setUp()
    {
        $this->fixtures('unit_tests', 'users');

        // collection by sql results
        $results = $this->_conn->selectAll("SELECT * FROM unit_tests ORDER BY id");
        $this->_results = new Mad_Model_Collection(new UnitTest, $results);

        // collection by array
        $models = array(
            new UnitTest(array('id' => 1)), new UnitTest(array('id' => 2)), 
            new UnitTest(array('id' => 3)), new UnitTest(array('id' => 4)), 
            new UnitTest(array('id' => 5)), new UnitTest(array('id' => 6))
        ); 
        $this->_models = new Mad_Model_Collection(new UnitTest, $models);
    }

    // test instantiation
    public function testConstructResults()
    {
        $this->assertTrue($this->_results instanceof Mad_Model_Collection);
    }

    // test instantiation
    public function testConstructModels()
    {
        $this->assertTrue($this->_models instanceof Mad_Model_Collection);
    }

    // test converting to string
    public function testToStringResults()
    {
        ob_start();
        print $this->_results;
        $result = ob_get_clean();

        $expected = "UnitTest Collection\n\n".
                    "  UnitTest: 1\n  UnitTest: 2\n".
                    "  UnitTest: 3\n  UnitTest: 4\n".
                    "  UnitTest: 5\n  UnitTest: 6";
        $this->assertEquals($expected, $result);
    }

    // test converting to string
    public function testToStringModels()
    {
        ob_start();
        print $this->_models;
        $result = ob_get_clean();

        $expected = "UnitTest Collection\n\n".
                    "  UnitTest: 1\n  UnitTest: 2\n".
                    "  UnitTest: 3\n  UnitTest: 4\n".
                    "  UnitTest: 5\n  UnitTest: 6";
        $this->assertEquals($expected, $result);
    }


    /*##########################################################################
    # Access
    ##########################################################################*/
    
    public function testShouldGetCollection()
    {
        $array = array(
            new UnitTest(array('id' => 1)), new UnitTest(array('id' => 2)), 
            new UnitTest(array('id' => 3)), new UnitTest(array('id' => 4)), 
            new UnitTest(array('id' => 5)), new UnitTest(array('id' => 6))
        ); 
        $models = new Mad_Model_Collection(new UnitTest, $array);

        $this->assertEquals($array, $models->getCollection());
    }


    /*##########################################################################
    # Countable Interface
    ##########################################################################*/

    // test counting the values
    public function testCountOopResults()
    {
        $this->assertEquals(6, $this->_results->count());
    }

    // test counting the values
    public function testCountOopModels()
    {
        $this->assertEquals(6, $this->_models->count());
    }

    // test counting the values using overloaded count()
    public function testCountProceduralResults()
    {
        $this->assertEquals(6, count($this->_results));
    }

    // test counting the values using overloaded count()
    public function testCountProceduralModels()
    {
        $this->assertEquals(6, count($this->_models));
    }


    /*##########################################################################
    # Iterator Interface
    ##########################################################################*/

    // test getting current value
    public function testCurrentResults()
    {
        $model = $this->_results->current();
        $this->assertType('UnitTest', $model);
        $this->assertEquals('1', $model->id);
    }

    // test getting current value
    public function testCurrentModels()
    {
        $model = $this->_models->current();
        $this->assertType('UnitTest', $model);
        $this->assertEquals('1', $model->id);
    }

    // test getting current value
    public function testKeyResults()
    {
        $key = $this->_results->key();
        $this->assertEquals(0, $key);
    }
    // test getting current value
    public function testKeyModels()
    {
        $key = $this->_models->key();
        $this->assertEquals(0, $key);
    }

    // test getting current value
    public function testNextResults()
    {
        $key = $this->_results->key();
        $this->assertEquals(0, $key);

        $model = $this->_results->next();

        $this->assertType('UnitTest', $model);
        $this->assertEquals('2', $model->id);
    }
    // test getting current value
    public function testNextModels()
    {
        $key = $this->_models->key();
        $this->assertEquals(0, $key);

        $model = $this->_models->next();

        $this->assertType('UnitTest', $model);
        $this->assertEquals('2', $model->id);
    }

    // test getting current value
    public function testRewindResults()
    {
        $model = $this->_results->next();
        $this->assertType('UnitTest', $model);
        $this->assertEquals('2', $model->id);

        $model = $this->_results->rewind();
        $this->assertType('UnitTest', $model);
        $this->assertEquals('1', $model->id);
    }
    // test getting current value
    public function testRewindModels()
    {
        $model = $this->_models->next();
        $this->assertType('UnitTest', $model);
        $this->assertEquals('2', $model->id);

        $model = $this->_models->rewind();
        $this->assertType('UnitTest', $model);
        $this->assertEquals('1', $model->id);
    }


    /*##########################################################################
    # IteratorAggregate Interface
    ##########################################################################*/

    // test element iteration
    public function testElementIterationResults()
    {
        $i = 0;
        foreach ($this->_results as $model) {
            $this->assertType('UnitTest', $model);
            $i++;
        }
        $this->assertEquals(6, $i);
    }

    // test element iteration
    public function testElementIterationModels()
    {
        $i = 0;
        foreach ($this->_models as $model) {
            $this->assertType('UnitTest', $model);
            $i++;
        }
        $this->assertEquals(6, $i);
    }


    // test unsetting an element of the object
    public function testDoubleIterationResults()
    {
        $i = 0;
        foreach ($this->_results as $model) {
            $this->assertType('UnitTest', $model);
            $i++;
        }
        $this->assertEquals(6, $i);

        $j = 0;
        foreach ($this->_results as $model) {
            $this->assertType('UnitTest', $model);
            $j++;
        }
        $this->assertEquals(6, $j);
    }

    // test unsetting an element of the object
    public function testDoubleIterationModels()
    {
        $i = 0;
        foreach ($this->_models as $model) {
            $this->assertType('UnitTest', $model);
            $i++;
        }
        $this->assertEquals(6, $i);

        $j = 0;
        foreach ($this->_models as $model) {
            $this->assertType('UnitTest', $model);
            $j++;
        }
        $this->assertEquals(6, $j);
    }

    /*##########################################################################
    # ArrayAccess Interface
    ##########################################################################*/

    // test accessing elements of the array
    public function testAccessFirstElementResults()
    {
        $this->assertType('UnitTest', $this->_results[0]);
    }
    // test accessing elements of the array
    public function testAccessFirstElementModels()
    {
        $this->assertType('UnitTest', $this->_models[0]);
    }

    // test accessing elements of the array
    public function testAccessNonexistentElementResults()
    {
        $this->assertNull($this->_results[99]);
    }
    // test accessing elements of the array
    public function testAccessNonexistentElementModels()
    {
        $this->assertNull($this->_models[99]);
    }

    // test checking if an element exists
    public function testElementIssetTrueResults()
    {
        $this->assertTrue(isset($this->_results[0]));
    }
    // test checking if an element exists
    public function testElementIssetTrueModels()
    {
        $this->assertTrue(isset($this->_models[0]));
    }

    // test checking if an element exists
    public function testElementIssetFalseResults()
    {
        $this->assertFalse(isset($this->_results[99]));
    }
    // test checking if an element exists
    public function testElementIssetFalseModels()
    {
        $this->assertFalse(isset($this->_models[99]));
    }

    public function testElementSetResults()
    {
        $this->assertEquals(6, count($this->_results));

        $this->_results[] = new UnitTest(array('id' => 99));
        $this->assertEquals(7, count($this->_results));
    }
    public function testElementSetModels()
    {
        $this->assertEquals(6, count($this->_models));

        $this->_models[] = new UnitTest(array('id' => 99));
        $this->assertEquals(7, count($this->_models));
    }

    // test unsetting an element of the object
    public function testElementUnsetResults()
    {
        unset($this->_results[0]);
        $this->assertType('UnitTest', $this->_results[0]);
    }
    // test unsetting an element of the object
    public function testElementUnsetModels()
    {
        unset($this->_models[0]);
        $this->assertType('UnitTest', $this->_models[0]);
    }


    /*##########################################################################
    # XML
    ##########################################################################*/

    public function testToXml()
    {
        $array = array(
            new User(array('id' => 1, 'name' => 'Derek DeVries')), 
            new User(array('id' => 2, 'name' => 'Mike Naberezny'))
        ); 
        $collection = new Mad_Model_Collection(new User, $array);
        $xml = $collection->toXml();
        
        $this->assertContains('<users type="array">',        $xml);
        $this->assertContains('<user>',                      $xml);
        $this->assertContains('<name>Derek DeVries</name>',  $xml);
        $this->assertContains('<name>Mike Naberezny</name>', $xml);
    }

    public function testToXmlAssigningRoot()
    {
        $array = array(
            new User(array('id' => 1, 'name' => 'Derek DeVries')), 
            new User(array('id' => 2, 'name' => 'Mike Naberezny'))
        ); 
        $collection = new Mad_Model_Collection(new User, $array);

        $options = array('root' => 'people', 'indent' => false);
        $xml     = $collection->toXml($options);
        
        $this->assertContains('<people type="array">', $xml);
    }

    public function testToXmlSkipInstruct()
    {
        $array = array(
            new User(array('id' => 1, 'name' => 'Derek DeVries')), 
            new User(array('id' => 2, 'name' => 'Mike Naberezny'))
        ); 
        $collection = new Mad_Model_Collection(new User, $array);

        $options = array('skipInstruct' => true, 'indent' => false);
        $xml     = $collection->toXml($options);

        $this->assertNotContains('<?xml', $xml);
    }
    
    public function testToXmlNoSkipInstruct()
    {
        $array = array(
            new User(array('id' => 1, 'name' => 'Derek DeVries')), 
            new User(array('id' => 2, 'name' => 'Mike Naberezny'))
        ); 
        $collection = new Mad_Model_Collection(new User, $array);

        $options = array('skipInstruct' => false, 'indent' => false);
        $xml     = $collection->toXml($options);

        $this->assertContains('<?xml', $xml);
    }
    
    public function testToXmlSkipTypes()
    {
        $array = array(
            new User(array('id' => 1, 'name' => 'Derek DeVries')), 
            new User(array('id' => 2, 'name' => 'Mike Naberezny'))
        ); 
        $collection = new Mad_Model_Collection(new User, $array);

        $options = array('skipTypes' => true, 'indent' => false);
        $xml     = $collection->toXml($options);

        $this->assertContains('<users>', $xml);
        $this->assertContains('<id>',    $xml);
    }
    
    public function testToXmlDasherizeFalse()
    {
        $array = array(
            new User(array('id' => 1, 'name' => 'Derek DeVries')), 
            new User(array('id' => 2, 'name' => 'Mike Naberezny'))
        ); 
        $collection = new Mad_Model_Collection(new User, $array);

        $options = array('dasherize' => false, 'indent' => false);
        $xml     = $collection->toXml($options);

        $this->assertContains('<created_at', $xml);
    }
    
    public function testToXmlDasherizeTrue()
    {
        $array = array(
            new User(array('id' => 1, 'name' => 'Derek DeVries')), 
            new User(array('id' => 2, 'name' => 'Mike Naberezny'))
        ); 
        $collection = new Mad_Model_Collection(new User, $array);

        $options = array('dasherize' => true, 'indent' => false);
        $xml     = $collection->toXml($options);

        $this->assertContains('<created-at', $xml);
    }

    public function testToXmlEmpty()
    {
        $array = array();
        $collection = new Mad_Model_Collection(new User, $array);

        $options = array('skipInstruct' => true, 'indent' => false);
        $xml     = $collection->toXml($options);

        $expected = '<users type="array"></users>';
        $this->assertEquals($expected, $xml);
    }


    /*##########################################################################
    ##########################################################################*/
}