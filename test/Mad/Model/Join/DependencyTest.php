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
class Mad_Model_Join_DependencyTest extends Mad_Test_Unit
{
    /*##########################################################################
    # Test construct
    ##########################################################################*/

    // constructing using belongs to association
    public function testConstructHasManyString()
    {
        $d = new Mad_Model_Join_Dependency(new User, 'Articles');
        $this->assertTrue($d instanceof Mad_Model_Join_Dependency);
    }

    // construct using has many association
    public function testConstructHasManyArray()
    {
        $d = new Mad_Model_Join_Dependency(new User, array('Articles'));
        $this->assertTrue($d instanceof Mad_Model_Join_Dependency);
    }

    // construct using has many association
    public function testConstructHasManyAssocArray()
    {
        $d = new Mad_Model_Join_Dependency(new User, array('Articles' => 'Comments'));
        $this->assertTrue($d instanceof Mad_Model_Join_Dependency);
    }


    /*##########################################################################
    # Test getters
    ##########################################################################*/

    public function testJoins()
    {
        $d = new Mad_Model_Join_Dependency(new User, array('Articles' => 'Comments'));

        $joins = $d->joins();
        $this->assertEquals(3, count($joins));
        $this->assertTrue($joins[0] instanceof Mad_Model_Join_Base);
        $this->assertTrue($joins[1] instanceof Mad_Model_Join_Association);
        $this->assertTrue($joins[2] instanceof Mad_Model_Join_Association);
    }

    // get base join object
    public function testJoinBase()
    {
        $d = new Mad_Model_Join_Dependency(new User, array('Articles' => 'Comments'));
        $this->assertTrue($d->joinBase() instanceof Mad_Model_Join_Base);
    }

    // gat association join objects
    public function testJoinAssociation()
    {
        $d = new Mad_Model_Join_Dependency(new User, array('Articles' => 'Comments'));

        $joins = $d->joinAssociations();
        $this->assertEquals(2, count($joins));
        $this->assertTrue($joins[0] instanceof Mad_Model_Join_Association);
        $this->assertTrue($joins[1] instanceof Mad_Model_Join_Association);
    }


    /*##########################################################################
    # Aliases
    ##########################################################################*/

    // test adding an alias
    public function testAddTableAlias()
    {
        $d = new Mad_Model_Join_Dependency(new User, array('Articles'));
        $expected = array('users' => 1, 'articles' => 1);
        $this->assertEquals($expected,  $d->tableAliases());

        // add aliases
        $d->addTableAlias('users');
        $expected = array('users' => 2, 'articles' => 1);
        $this->assertEquals($expected,  $d->tableAliases());

        $d->addTableAlias('comments');
        $expected = array('users' => 2, 'articles' => 1, 'comments' => 1);
        $this->assertEquals($expected,  $d->tableAliases());
    }

    // get the index of the given alias
    public function testTableAlias()
    {
        $d = new Mad_Model_Join_Dependency(new User, array('Articles'));
        $this->assertEquals(1,  $d->tableAlias('users'));
        $this->assertEquals(0,  $d->tableAlias('asdf'));
    }

    // test getting the list of aliases
    public function testTableAliases()
    {
        $d = new Mad_Model_Join_Dependency(new User, array('Articles' => 'Comments'));

        $expected = array('users' => 1, 'articles' => 1, 'comments' => 1);
        $this->assertEquals($expected, $d->tableAliases());
    }


    /*##########################################################################
    # Instantiation
    ##########################################################################*/

    // test instantiating objects based on the associations
    public function testInstantiateHasMany()
    {
        // this is 4 articles for 2 users
        $rows = array(array('T0_R0' => '1', 'T1_R0' => '1'),
                      array('T0_R0' => '1', 'T1_R0' => '2'),
                      array('T0_R0' => '2', 'T1_R0' => '3'),
                      array('T0_R0' => '2', 'T1_R0' => '4'));

        $d = new Mad_Model_Join_Dependency(new User, 'Articles');
        $users = $d->instantiate($rows);

        // make sure it made 2 folders
        $this->assertEquals(2, count($users));

        // each is a folder
        foreach ($users as $user) {
            $this->assertTrue($user instanceof User);

            $this->assertEquals(2, count($user->articles));
            foreach ($user->articles as $article) {
                $this->assertTrue($article instanceof Article);
            }
        }
    }


    // test instantiating objects based on the associations
    public function testInstantiateBelongsTo()
    {
        // this is 4 articles w/associated user
        $rows = array(array('T0_R0' => '1', 'T1_R0' => '9'),
                      array('T0_R0' => '2', 'T1_R0' => '8'),
                      array('T0_R0' => '3', 'T1_R0' => '7'),
                      array('T0_R0' => '4', 'T1_R0' => '6'));

        $d = new Mad_Model_Join_Dependency(new Article, 'User');
        $articles = $d->instantiate($rows);

        // make sure it made 4 docs
        $this->assertEquals(4, count($articles));

        $this->assertTrue($articles[0] instanceof Article);
        $this->assertTrue($articles[0]->user instanceof User);
    }

    // test instantiating objects based on the associations
    public function testInstantiateHasManyBelongsToHash()
    {
        // this is 4 docs in 2 folders
        $rows = array(array('T0_R0' => '1', 'T1_R0' => '1', 'T2_R0' => '9'),
                      array('T0_R0' => '1', 'T1_R0' => '2', 'T2_R0' => '9'),
                      array('T0_R0' => '2', 'T1_R0' => '3', 'T2_R0' => '9'),
                      array('T0_R0' => '2', 'T1_R0' => '4', 'T2_R0' => '9'));
        $d = new Mad_Model_Join_Dependency(new User, array('Articles' => 'Comments'));
        $users = $d->instantiate($rows);

        // make sure it made 2 folders
        $this->assertEquals(2, count($users));

        // each is a folder
        foreach ($users as $user) {
            $this->assertTrue($user instanceof User);

            $this->assertEquals(2, count($user->articles));
            foreach ($user->articles as $article) {
                $this->assertTrue($article instanceof Article);
                $this->assertTrue($article->comments[0] instanceof Comment);
            }
        }
    }


    /*##########################################################################
    ##########################################################################*/
}