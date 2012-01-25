Model Testing
=============

Overview
--------

Model tests are stored in ``test/unit``. Once we have some fixtures written,
testing the model functionality is straightforward.  We specify what data to
load, and make sure that our model functionality returns the correct data when it
is executed.

Each unit test class is named after the model class with a "Test" suffix and extends
``Mad_Test_Unit``. Most of these details are already handled when the stubs are
generated for the model file. Tests classes have two special methods that are optional.
``setUp()`` will execute before each test, and ``tearDown()`` will
execute after each test finishes::

    class FolderTest extends Mad_Test_Unit
    {
        public function setUp()
        {
            // this code is run before each test
        }

        public function tearDown()
        {
            // this code is run after each test
        }
    }

Any method within the class that begins with the prefix ``test`` will be considered
a test and will be executed when PHPUnit runs. Most of the time you will write at
least one test per public method in your model. More often than not you will write
more::

    class FolderTest extends Mad_Test_Unit
    {
        // this is considered a test
        public function testGetParentFolders()
        {
        }

        // this is not a test (not prefixed with 'test')
        public function doSomething()
        {
        }
    }

Most tests will be performed with the use of PHPUnit's assertion methods. When the
test executes, PHPUnit ensures that each assertion performs correctly in order for
the test to pass. The three most common assertions are ``assertTrue``,
``assertFalse``, and ``assertEquals``::

    class FolderTest extends Mad_Test_Unit
    {
        public function testGetParentFolders()
        {
            $this->assertTrue($folder instanceof Folder);

            $this->assertFalse(empty($folder->name));

            $this->assertEquals('The Description', $folder->description)
        }
    }

Loading Fixtures
----------------

Loading fixture data in a unit test is simple.  It can be put in the setUp() method
which gets run before each test::

    public function setUp()
    {
        $this->fixtures('folders');

        $this->fixtures('documents', 'document_types');
    }

It can also be loaded individually per test::

    public function testSomeFunctionality()
    {
        $this->fixtures('folders');
    }

Data by Name
------------

When a fixture gets loaded, we also get access to the records in the fixture file
by name. This allows us to write tests that are more resilient to changes within the
fixture. Each name has an associative array of values from the fixture.

Fixture data in ``folders.yml``::

    public:
      id:        123
      parent_id: 0
      name:      1. Documents
      path:      ./1. Documents

Access the fixture record by name::

    public function testSomeFunctionality()
    {
        $this->fixtures('folders');

        // a Folder object is instantiated with the fixture data
        $folder = $this->folders('public');

        // assert the name is correct
        $this->assertEquals('1. Documents', $folder->name);
    }

Database Access
---------------

Most of the tests written to test Model functionality will not need to access the
database directly, but will instead verify data from a fixture file. If you need
to access the database directly you can do so by using ``$this->_conn``::

    public function testQuerySomeData()
    {
        $results = $this->_conn->select("SELECT * FROM folders");
        foreach ($results as $row) {
            print $row['name'];
        }
    }

Test Helpers
------------

If we have custom assertion or test helper methods, we can share them
using the ``MadTestHelper`` class. Adding public methods to
``test/MadTestHelper.php`` will make them accessible from all of
our unit and functional tests::

    class MadTestHelper
    {
        public function clearUploads()
        {
            Mad_Support_FileUtils::rm_rf(UPLOAD_DIR);
        }
    }

    class DocumentTest extends Mad_Test_Unit
    {
      public function setUp()
      {
          $this->clearUploads();
      }
    }

Assertions
----------

These assertions are added by Mad_Test_Unit to make testing
models more convenient.

AssertDifference
^^^^^^^^^^^^^^^^

This assertion uses a block style syntax to make sure that a
expression given yields different results after a block of code has
executed.  The first argument to this assertion is an expression.
The second is an integer that represents the difference between the
expression's result before and after the block is finished.
The block is anything that comes before we call the ``end()`` method::

    // assert count is +1 after create() finishes
    $diff = $this->assertDifference('User::count()', 1);
        User::create(array('username' => 'lebowski'));
    $diff->end();

The difference expected in this case is that ``User::count()`` will
increase by 1 after the creation is finished. This is an optional
argument, and will default to 1 when not specified. We can
alternately use a negative number if the net count would have
decreased during the block::

    // assert count is -1 after delete() finishes
    $diff = $this->assertDifference('User::count()', -1);
        User::delete(1);
    $diff->end();

AssertNoDifference
^^^^^^^^^^^^^^^^^^

This assertion works very similarly to its counterpart, but
asserts that no change takes place when the expression
is evaluated before and after the block executes::

    // make sure that the count is the same after create() finishes
    $diff = $this->assertNoDifference('User::count()');
        User::create(array('username' => ''));
    $diff->end();

