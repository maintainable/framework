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
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config/environment.php';
}

/**
 * @group      model
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007-2008 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Migration_MigratorTest extends Mad_Test_Unit
{
    public function setUp()
    {
        Mad_Model_Migration_Base::$verbose = false;
    }

    public function tearDown()
    {
        $this->_conn->initializeSchemaInformation();
        $this->_conn->update("UPDATE schema_info SET version = 0");

        // drop tables
        foreach (array('reminders', 'users_reminders', 'testings', 'octopuses', 
                       'octopi', 'binary_testings', 'big_numbers') as $table) {
            try {
                $this->_conn->dropTable($table);
            } catch (Exception $e) {}
        }

        // drop cols
        foreach (array('first_name', 'middle_name', 'last_name', 'key', 'male', 
                       'bio', 'age', 'height', 'wealth', 'birthday', 'group', 
                       'favorite_day', 'moment_of_truth', 'administrator', 
                       'exgirlfriend', 'contributor', 'nick_name', 
                       'intelligence_quotient') as $col) {
            try {
                $this->_conn->removeColumn('users', $col);
            } catch (Exception $e) {}
        }
        $this->_conn->addColumn('users', 'first_name', 'string', array('limit' => 40));
        $this->_conn->changeColumn('users', 'approved', 'boolean', array('default' => true));
    }

    public function testMigrator()
    {
        $user = new User;
        $columns = $user->columnNames();

        $this->assertFalse(in_array('last_name', $columns));

        $e = null;
        try {
            $this->_conn->selectValues("SELECT * FROM reminders");
        } catch (Exception $e) {}
        $this->assertType('Horde_Db_Exception', $e);

        $dir = dirname(dirname(dirname(dirname(__FILE__)))).'/fixtures/migrations/';
        Mad_Model_Migration_Migrator::up($dir);
        $this->assertEquals(3, Mad_Model_Migration_Migrator::getCurrentVersion());

        $user->resetColumnInformation();
        $columns = $user->columnNames();
        $this->assertTrue(in_array('last_name', $columns));

        $result = Reminder::create(array('content'   => 'hello world', 
                                         'remind_at' => '2005-01-01 02:22:23'));
        $reminder = Reminder::find('first');
        $this->assertEquals('hello world', $reminder->content);

        $dir = dirname(dirname(dirname(dirname(__FILE__)))).'/fixtures/migrations/';
        Mad_Model_Migration_Migrator::down($dir);
        $this->assertEquals(0, Mad_Model_Migration_Migrator::getCurrentVersion());

        $user->resetColumnInformation();
        $columns = $user->columnNames();
        $this->assertFalse(in_array('last_name', $columns));

        $e = null;
        try {
            $this->_conn->selectValues("SELECT * FROM reminders");
        } catch (Exception $e) {}
        $this->assertType('Horde_Db_Exception', $e);
    }

    public function testOneUp()
    {
        $e = null;
        try {
            $this->_conn->selectValues("SELECT * FROM reminders");
        } catch (Exception $e) {}
        $this->assertType('Horde_Db_Exception', $e);

        $dir = dirname(dirname(dirname(dirname(__FILE__)))).'/fixtures/migrations/';
        Mad_Model_Migration_Migrator::up($dir, 1);
        $this->assertEquals(1, Mad_Model_Migration_Migrator::getCurrentVersion());

        $user = new User;
        $columns = $user->columnNames();
        $this->assertTrue(in_array('last_name', $columns));

        $e = null;
        try {
            $this->_conn->selectValues("SELECT * FROM reminders");
        } catch (Exception $e) {}
        $this->assertType('Horde_Db_Exception', $e);

        Mad_Model_Migration_Migrator::up($dir, 2);
        $this->assertEquals(2, Mad_Model_Migration_Migrator::getCurrentVersion());

        $result = Reminder::create(array('content'   => 'hello world', 
                                         'remind_at' => '2005-01-01 02:22:23'));
        $reminder = Reminder::find('first');
        $this->assertEquals('hello world', $reminder->content);
    }

    public function testOneDown()
    {
        $dir = dirname(dirname(dirname(dirname(__FILE__)))).'/fixtures/migrations/';

        Mad_Model_Migration_Migrator::up($dir);
        Mad_Model_Migration_Migrator::down($dir, 1);

        $user = new User;
        $columns = $user->columnNames();
        $this->assertTrue(in_array('last_name', $columns));
    }

    public function testOneUpOneDown()
    {
        $dir = dirname(dirname(dirname(dirname(__FILE__)))).'/fixtures/migrations/';

        Mad_Model_Migration_Migrator::up($dir, 1);
        Mad_Model_Migration_Migrator::down($dir, 0);

        $user = new User;
        $columns = $user->columnNames();
        $this->assertFalse(in_array('last_name', $columns));
    }

    public function testMigratorGoingDownDueToVersionTarget()
    {
        $dir = dirname(dirname(dirname(dirname(__FILE__)))).'/fixtures/migrations/';

        Mad_Model_Migration_Migrator::up($dir, 1);
        Mad_Model_Migration_Migrator::down($dir, 0);

        $user = new User;
        $columns = $user->columnNames();
        $this->assertFalse(in_array('last_name', $columns));
    
        $e = null;
        try {
            $this->_conn->selectValues("SELECT * FROM reminders");
        } catch (Exception $e) {}
        $this->assertType('Horde_Db_Exception', $e);


        Mad_Model_Migration_Migrator::up($dir);

        $user->resetColumnInformation();
        $columns = $user->columnNames();
        $this->assertTrue(in_array('last_name', $columns));

        $result = Reminder::create(array('content'   => 'hello world', 
                                         'remind_at' => '2005-01-01 02:22:23'));
        $reminder = Reminder::find('first');
        $this->assertEquals('hello world', $reminder->content);
    }

    public function testWithDuplicates()
    {
        try {
            $dir = dirname(dirname(dirname(dirname(__FILE__)))).'/fixtures/migrations_with_duplicate/';
            Mad_Model_Migration_Migrator::up($dir);
        } catch (Exception $e) { return; }
        $this->fail('Expected exception wasn\'t raised');
    }

    public function testWithMissingVersionNumbers()
    {
        $dir = dirname(dirname(dirname(dirname(__FILE__)))).'/fixtures/migrations_with_missing_versions/';
        Mad_Model_Migration_Migrator::migrate($dir, 500);
        $this->assertEquals(4, Mad_Model_Migration_Migrator::getCurrentVersion());

        Mad_Model_Migration_Migrator::migrate($dir, 2);
        $this->assertEquals(2, Mad_Model_Migration_Migrator::getCurrentVersion());

        $e = null;
        try {
            $this->_conn->selectValues("SELECT * FROM reminders");
        } catch (Exception $e) {}
        $this->assertType('Horde_Db_Exception', $e);

        $user = new User;
        $columns = $user->columnNames();
        $this->assertTrue(in_array('last_name', $columns));
    }
}