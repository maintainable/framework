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
 * @group      model
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_PaginatedCollectionTest extends Mad_Test_Unit
{
    // set up new db by inserting dummy data into the db
    public function setUp()
    {
        $this->fixtures('unit_tests', 'unit_tests_more');

        // paginated collection from array
        $files = array(new File(1, 'alpha'),  new File(2, 'beta'),     new File(3, 'gamma'), 
                       new File(4, 'delta'),  new File(5, 'epsilon'),  new File(6, 'zeta'), 
                       new File(7, 'eta'),    new File(8, 'theta'),    new File(9, 'iota'), 
                       new File(10, 'kappa'), new File(11, 'lamda'),   new File(12, 'mu'), 
                       new File(13, 'nu'),    new File(14, 'xi'),      new File(15, 'omikron'), 
                       new File(16, 'pi'),    new File(17, 'rho'),     new File(18, 'sigma'), 
                       new File(19, 'tau'),   new File(20, 'upsilon'), new File(21, 'phi'), 
                       new File(22, 'chi'),   new File(23, 'psi'),     new File(24, 'omega'));
        $this->_array = new Mad_Model_PaginatedCollection(
            array_slice($files, 10, 5), 3, 5, count($files)
        );

        // paginated collection from model collection
        $testCount = UnitTest::count();
        $tests     = UnitTest::find('all', array('offset' => '5', 'limit' => '5'));
        $this->_models = new Mad_Model_PaginatedCollection($tests, 2, 5, $testCount);
    }

    // test instantiation
    public function testConstructArray()
    {
        $this->assertType('Mad_Model_PaginatedCollection', $this->_array);
    }

    public function testConstructModel()
    {
        $this->assertType('Mad_Model_PaginatedCollection', $this->_models);
    }

    // test converting to string
    public function testToStringArray()
    {
        ob_start();
        print $this->_array;
        $result = ob_get_clean();

        $expected = "File Collection\n\n".
                    "  File: 11\n  File: 12\n".
                    "  File: 13\n  File: 14\n".
                    "  File: 15";
        $this->assertEquals($expected, $result);
    }

    // test converting to string
    public function testToStringModel()
    {
        ob_start();
        print $this->_models;
        $result = ob_get_clean();

        $expected = "UnitTest Collection\n\n".
                    "  UnitTest: 6\n  UnitTest: 7\n".
                    "  UnitTest: 8\n  UnitTest: 9\n".
                    "  UnitTest: 10";
        $this->assertEquals($expected, $result);
    }

    /*##########################################################################
    # Countable Interface
    ##########################################################################*/

    // test counting the values
    public function testCountOopArray()
    {
        $this->assertEquals(5, $this->_array->count());
    }

    // test counting the values
    public function testCountOopModel()
    {
        $this->assertEquals(5, $this->_models->count());
    }

    // test counting the values using overloaded count()
    public function testCountProceduralArray()
    {
        $this->assertEquals(5, count($this->_array));

    }

    // test counting the values using overloaded count()
    public function testCountProceduralModel()
    {
        $this->assertEquals(5, count($this->_models));
    }


    /*##########################################################################
    # Iterator Interface
    ##########################################################################*/

    // test getting current value
    public function testCurrentArray()
    {
        $model = $this->_array->current();
        $this->assertType('File', $model);
        $this->assertEquals('11', $model->id);
    }
    // test getting current value
    public function testCurrentModels()
    {
        $model = $this->_models->current();
        $this->assertType('UnitTest', $model);
        $this->assertEquals('6', $model->id);
    }

    // test getting current value
    public function testKeyArray()
    {
        $key = $this->_array->key();
        $this->assertEquals(0, $key);
    }
    // test getting current value
    public function testKeyModels()
    {
        $key = $this->_models->key();
        $this->assertEquals(0, $key);
    }

    // test getting current value
    public function testNextArray()
    {
        $key = $this->_array->key();
        $this->assertEquals(0, $key);

        $model = $this->_array->next();

        $this->assertType('File', $model);
        $this->assertEquals('12', $model->id);
    }
    // test getting current value
    public function testNextModels()
    {
        $key = $this->_models->key();
        $this->assertEquals(0, $key);

        $model = $this->_models->next();

        $this->assertType('UnitTest', $model);
        $this->assertEquals('7', $model->id);
    }

    // test getting current value
    public function testRewindArray()
    {
        $model = $this->_array->next();
        $this->assertType('File', $model);
        $this->assertEquals('12', $model->id);

        $model = $this->_array->rewind();
        $this->assertType('File', $model);
        $this->assertEquals('11', $model->id);
    }
    // test getting current value
    public function testRewindModels()
    {
        $model = $this->_models->next();
        $this->assertType('UnitTest', $model);
        $this->assertEquals('7', $model->id);

        $model = $this->_models->rewind();
        $this->assertType('UnitTest', $model);
        $this->assertEquals('6', $model->id);
    }


    /*##########################################################################
    # IteratorAggregate Interface
    ##########################################################################*/

    // test element iteration
    public function testElementIterationArray()
    {
        $i = 0;
        foreach ($this->_array as $item) {
            $this->assertType('File', $item);
            $i++;
        }
        $this->assertEquals(5, $i);
    }

    // test element iteration
    public function testElementIterationModel()
    {
        $i = 0;
        foreach ($this->_models as $item) {
            $this->assertType('UnitTest', $item);
            $i++;
        }
        $this->assertEquals(5, $i);
    }


    // test unsetting an element of the object
    public function testDoubleIterationArray()
    {
        $i = 0;
        foreach ($this->_array as $item) {
            $this->assertType('File', $item);
            $i++;
        }
        $this->assertEquals(5, $i);

        $j = 0;
        foreach ($this->_array as $item) {
            $this->assertType('File', $item);
            $j++;
        }
        $this->assertEquals(5, $j);
    }

    // test unsetting an element of the object
    public function testDoubleIterationModel()
    {
        $i = 0;
        foreach ($this->_models as $item) {
            $this->assertType('UnitTest', $item);
            $i++;
        }
        $this->assertEquals(5, $i);

        $j = 0;
        foreach ($this->_models as $item) {
            $this->assertType('UnitTest', $item);
            $j++;
        }
        $this->assertEquals(5, $j);
    }


    /*##########################################################################
    # ArrayAccess Interface
    ##########################################################################*/

    // test accessing elements of the array
    public function testAccessFirstElementArray()
    {
        $this->assertType('File', $this->_array[0]);
    }
    // test accessing elements of the array
    public function testAccessFirstElementModel()
    {
        $this->assertType('UnitTest', $this->_models[0]);
    }

    // test accessing elements of the array
    public function testAccessNonexistentElementArray()
    {
        $this->assertNull($this->_array[99]);
    }
    // test accessing elements of the array
    public function testAccessNonexistentElementModel()
    {
        $this->assertNull($this->_models[99]);
    }

    // test checking if an element exists
    public function testElementIssetTrueArray()
    {
        $this->assertTrue(isset($this->_array[0]));
    }
    // test checking if an element exists
    public function testElementIssetTrueModel()
    {
        $this->assertTrue(isset($this->_models[0]));
    }

    // test checking if an element exists
    public function testElementIssetFalseArray()
    {
        $this->assertFalse(isset($this->_array[99]));
    }
    // test checking if an element exists
    public function testElementIssetFalseModel()
    {
        $this->assertFalse(isset($this->_models[99]));
    }

    // test setting an element of the object
    public function testElementSetDoesNothingArray()
    {
        $this->_array[0] = 'test';
        $this->assertType('File', $this->_array[0]);
    }
    // test setting an element of the object
    public function testElementSetDoesNothingModel()
    {
        $this->_models[0] = 'test';
        $this->assertType('UnitTest', $this->_models[0]);
    }

    // test unsetting an element of the object
    public function testElementUnsetArray()
    {
        unset($this->_array[0]);
        $this->assertType('File', $this->_array[0]);
    }
    // test unsetting an element of the object
    public function testElementUnsetModel()
    {
        unset($this->_models[0]);
        $this->assertType('UnitTest', $this->_models[0]);
    }


    /*##########################################################################
    # Test Pagination
    ##########################################################################*/

    public function testCurrentPageArray()
    {
        $this->assertEquals('3', $this->_array->currentPage);
    }
    public function testCurrentPageModel()
    {
        $this->assertEquals('2', $this->_models->currentPage);
    }

    public function testPerPageArray()
    {
        $this->assertEquals('5', $this->_array->perPage);
    }
    public function testPerPageModel()
    {
        $this->assertEquals('5', $this->_models->perPage);
    }

    public function testTotalEntriesArray()
    {
        $this->assertEquals('24', $this->_array->totalEntries);
    }
    public function testTotalEntriesModel()
    {
        $this->assertEquals('11', $this->_models->totalEntries);
    }

    public function testTotalPagesArray()
    {
        $this->assertEquals('5', $this->_array->pageCount);
    }
    public function testTotalPagesModel()
    {
        $this->assertEquals('3', $this->_models->pageCount);
    }

    public function testRangeArray()
    {
        $this->assertEquals('11 - 15', $this->_array->range);
    }
    public function testRangeModel()
    {
        $this->assertEquals('6 - 10', $this->_models->range);
    }


    /*##########################################################################
    # XML
    ##########################################################################*/
    
    public function testToXml()
    {
        $this->fixtures('articles');

        $testCount = Article::count();
        $tests     = Article::find('all', array('offset' => '2', 'limit' => '2'));
        $models    = new Mad_Model_PaginatedCollection($tests, 2, 2, $testCount);

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<articles type="array">
  <article>
    <id type="integer">3</id>
    <title>Testing Javascript in Rails</title>
    <user-id type="integer">2</user-id>
  </article>
  <article>
    <id type="integer">4</id>
    <title>Creating Classes with Prototype</title>
    <user-id type="integer">2</user-id>
  </article>
</articles>

XML;
        $this->assertEquals($expected, $models->toXml());
    }

    public function testToXmlProxiesOptionsToCollection()
    {
        $this->fixtures('articles');

        $testCount = Article::count();
        $tests     = Article::find('all', array('offset' => '2', 'limit' => '2'));
        $models    = new Mad_Model_PaginatedCollection($tests, 2, 2, $testCount);

        $this->assertContains('<articles>', $models->toXml(array('skipTypes' => true)));
    }

    /*##########################################################################
    ##########################################################################*/
}