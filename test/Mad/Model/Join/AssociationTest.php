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
class Mad_Model_Join_AssociationTest extends Mad_Test_Unit
{
    /*##########################################################################
    # Construct
    ##########################################################################*/

    // construct thru dependency
    public function testConstruct()
    {
        $joinDep = new Mad_Model_Join_Dependency(new User, 'Articles');
        $associations = $joinDep->joinAssociations();
        $joinAssoc = array_shift($associations);

        $this->assertTrue($joinAssoc instanceof Mad_Model_Join_Association);
    }


    /*##########################################################################
    # Getters
    ##########################################################################*/

    // association object
    public function testGetReflection()
    {
        $joinDep = new Mad_Model_Join_Dependency(new User, 'Articles');
        $associations = $joinDep->joinAssociations();
        $joinAssoc = array_shift($associations);

        $this->assertTrue($joinAssoc->reflection() instanceof Mad_Model_Association_Base);
    }

    // parent join
    public function testGetParent()
    {
        $joinDep = new Mad_Model_Join_Dependency(new User, 'Articles');
        $associations = $joinDep->joinAssociations();
        $joinAssoc = array_shift($associations);

        $this->assertTrue($joinAssoc->parent() instanceof Mad_Model_Join_Base);
    }

    // aliased table
    public function testGetAliasedTableName()
    {
        $joinDep = new Mad_Model_Join_Dependency(new User, 'Articles');
        $associations = $joinDep->joinAssociations();
        $joinAssoc = array_shift($associations);

        $this->assertEquals('articles', $joinAssoc->aliasedTableName());
    }

    // aliased table
    public function testGetAliasedTableNameMultiple()
    {
        $joinDep = new Mad_Model_Join_Dependency(new User, array('Articles', 'Articles', 'Articles', 'Comments'));
        $joinAssocs = $joinDep->joinAssociations();

        $joinAssoc = array_shift($joinAssocs);
        $this->assertEquals('articles', $joinAssoc->aliasedTableName());

        $joinAssoc = array_shift($joinAssocs);
        $this->assertEquals('articles_users', $joinAssoc->aliasedTableName());

        $joinAssoc = array_shift($joinAssocs);
        $this->assertEquals('articles_users_2', $joinAssoc->aliasedTableName());

        $joinAssoc = array_shift($joinAssocs);
        $this->assertEquals('comments', $joinAssoc->aliasedTableName());
    }

    // aliased prefix
    public function testGetAliasedPrefix()
    {
        $joinDep = new Mad_Model_Join_Dependency(new User, 'Articles');
        $associations = $joinDep->joinAssociations();
        $joinAssoc = array_shift($associations);

        $this->assertEquals('T1', $joinAssoc->aliasedPrefix());
    }

    // aliased prefix
    public function testGetAliasedPrefixMultiple()
    {
        $joinDep = new Mad_Model_Join_Dependency(new User, array('Articles', 'Comments'));
        $joinAssocs = $joinDep->joinAssociations();

        $joinAssoc = array_shift($joinAssocs);
        $this->assertEquals('T1', $joinAssoc->aliasedPrefix());

        $joinAssoc = array_shift($joinAssocs);
        $this->assertEquals('T2', $joinAssoc->aliasedPrefix());
    }

    // aliased join table name
    public function testGetAliasedJoinTableName()
    {
        $joinDep = new Mad_Model_Join_Dependency(new Article, 'Categories');
        $associations = $joinDep->joinAssociations();
        $joinAssoc = array_shift($associations);

        $this->assertEquals('articles_categories', $joinAssoc->aliasedJoinTableName());
    }

    // aliased join table name
    public function testGetAliasedJoinTableNameMultiple()
    {
        $joinDep = new Mad_Model_Join_Dependency(new Category, array('Articles', 'Articles', 'Articles'));
        $joinAssocs = $joinDep->joinAssociations();

        $joinAssoc = array_shift($joinAssocs);
        $this->assertEquals('articles_categories', $joinAssoc->aliasedJoinTableName());

        $joinAssoc = array_shift($joinAssocs);
        $this->assertEquals('articles_categories_join', $joinAssoc->aliasedJoinTableName());

        $joinAssoc = array_shift($joinAssocs);
        $this->assertEquals('articles_categories_join_2', $joinAssoc->aliasedJoinTableName());
    }

    // parent table name
    public function testGetParentTableName()
    {
        $joinDep = new Mad_Model_Join_Dependency(new User, 'Articles');
        $associations = $joinDep->joinAssociations();
        $joinAssoc = array_shift($associations);

        $this->assertEquals('users', $joinAssoc->parentTableName());
    }


    /*##########################################################################
    # Association Join SQL
    ##########################################################################*/

    // get join sql for belongsTo association
    public function testAssociationJoinBelongsTo()
    {
        $joinDep = new Mad_Model_Join_Dependency(new Article, 'User');
        $associations = $joinDep->joinAssociations();
        $joinAssoc = array_shift($associations);

        $joinStr  = $joinAssoc->associationJoin();
        $expected = " LEFT OUTER JOIN users ON users.id = articles.user_id ";
        $this->assertEquals($expected, $joinStr);
    }

    // get join sql for hasOne association
    public function testAssociationJoinHasOne()
    {
        $joinDep = new Mad_Model_Join_Dependency(new User, 'Avatar');
        $associations = $joinDep->joinAssociations();
        $joinAssoc = array_shift($associations);

        $joinStr  = $joinAssoc->associationJoin();
        $expected = " LEFT OUTER JOIN avatars ON avatars.user_id = users.id ";
        $this->assertEquals($expected, $joinStr);
    }

    // get join sql for hasMany association
    public function testAssociationJoinHasMany()
    {
        $joinDep = new Mad_Model_Join_Dependency(new User, 'Articles');
        $associations = $joinDep->joinAssociations();
        $joinAssoc = array_shift($associations);

        $joinStr  = $joinAssoc->associationJoin();
        $expected = " LEFT OUTER JOIN articles ON articles.user_id = users.id ";
        $this->assertEquals($expected, $joinStr);
    }

    // get join sql for hasAndBelongsToMany association
    public function testAssociationJoinHasAndBelongsToMany()
    {
        $joinDep = new Mad_Model_Join_Dependency(new Category, 'Articles');
        $associations = $joinDep->joinAssociations();
        $joinAssoc = array_shift($associations);

        $joinStr  = $joinAssoc->associationJoin();
        $expected = " LEFT OUTER JOIN articles_categories".
                    " ON articles_categories.category_id = categories.id ".
                    " LEFT OUTER JOIN articles".
                    " ON articles.id = articles_categories.article_id ";
        $this->assertEquals($expected, $joinStr);
    }

    /*##########################################################################
    ##########################################################################*/

}