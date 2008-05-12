<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007 Maintainable Software, LLC
 * @license    Proprietary and Confidential 
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
 * @license    Proprietary and Confidential
 */
class Mad_Model_Association_HasAndBelongsToManyTest extends Mad_Test_Unit
{
    /*##########################################################################
    # Test getters
    ##########################################################################*/

    // the type of association
    public function testGetMacro()
    {
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertEquals('hasAndBelongsToMany', $assoc->getMacro());
    }

    // the name of the association
    public function testGetAssocName()
    {
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertEquals('Categories', $assoc->getAssocName());

        $options = array('className' => 'Categories');
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'whatever', $options, new Article);
        $this->assertEquals('whatever', $assoc->getAssocName());
    }

    // the class for the primary object
    public function testGetClass()
    {
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertEquals('Article', $assoc->getClass());
    }

    // the model object for the primary object
    public function testGetModel()
    {
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertTrue($assoc->getModel() instanceof Article);
    }

    // the table name for the primary object
    public function testTableName()
    {
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertEquals('articles', $assoc->tableName());
    }

    // the value of the primary key
    public function testGetPkValue()
    {
        // 'parent_folderid' foreignKey
        $article = new Article(array('id' => '1'));
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), $article);
        $this->assertEquals('1', $assoc->getPkValue());

        // diff foreignKey in documentid
        $article = new Article(array('title' => '2'));
        $options = array('primaryKey' => 'title');
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', $options, $article);
        $this->assertEquals('2', $assoc->getPkValue());
    }

    // the class of the associated object
    public function testGetAssocClass()
    {
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertEquals('Category', $assoc->getAssocClass());

        $options = array('className' => 'Category');
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'test', $options, new Article);
        $this->assertEquals('Category', $assoc->getAssocClass());
    }

    // the model object of the associated object
    public function testGetAssocModel()
    {
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertTrue($assoc->getAssocModel() instanceof Category);
    }

    // the table name of the associated object
    public function testGetAssocTable()
    {
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertEquals('categories', $assoc->getAssocTable());
    }

    public function testGetPkName()
    {
        // normal assoc
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertEquals('id', $assoc->getPkName());

        // option passed in
        $options = array('primaryKey' => 'asdf');
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', $options, new Article);
        $this->assertEquals('asdf', $assoc->getPkName());
    }

    public function testGetFkName()
    {
        // normal assoc
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertEquals('article_id', $assoc->getFkName());

        // option passed in
        $options = array('foreignKey' => 'asdf');
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', $options, new Article);
        $this->assertEquals('asdf', $assoc->getFkName());
    }

    public function testGetAssocPkName()
    {
        // normal assoc
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertEquals('id', $assoc->getAssocPkName());

        // option passed in
        $options = array('associationPrimaryKey' => 'blah');
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', $options, new Article);
        $this->assertEquals('blah', $assoc->getAssocPkName());
    }

    public function testGetAssocFkName()
    {
        // normal assoc
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertEquals('category_id', $assoc->getAssocFkName());

        // option passed in
        $options = array('associationForeignKey' => 'blah');
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', $options, new Article);
        $this->assertEquals('blah', $assoc->getAssocFkName());
    }

    // test construct/association key (default is no join)
    public function testGetJoinTable()
    {
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertEquals('articles_categories', $assoc->getJoinTable());

        $options = array('joinTable' => 'test');
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', $options, new Article);
        $this->assertEquals('test', $assoc->getJoinTable());
    }

    // test getting an association by name for a model
    public function testGetModelAssociation()
    {
        $article = new Article();
        $assoc = $article->getAssociation('Categories');
        $this->assertTrue($assoc instanceof Mad_Model_Association_HasAndBelongsToMany);
    }

    /*##########################################################################
    # Test dynamic method instantiation
    ##########################################################################*/

    // test get the created methods
    public function testGetMethods()
    {
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Category);
        $expected = array(
            'categories'        => 'getObjects',
            'categories='       => 'setObjects',
            'categoryIds'       => 'getObjectIds',
            'categoryIds='      => 'setObjectIds',
            'categoryCount'     => 'getObjectCount',
            'addCategory'       => 'addObject',
            'replaceCategories' => 'replaceObjects',
            'deleteCategories'  => 'deleteObjects',
            'clearCategories'   => 'clearObjects',
            'findCategories'    => 'findObjects'
        );
        $this->assertEquals($expected, $assoc->getMethods());
    }

    // setting object as loaded will prevent it from querying
    public function testSetLoaded()
    {
        $this->useMockLogger();
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);
        $article->setAssociationLoaded('Categories');
        $this->clearLog();

        // make sure it doesn't query qgain
        $article->categories;
        $this->assertNotLogged('Category Load');
    }

    // setting object as loaded will prevent it from querying
    public function testIsLoaded()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');
        $article = Article::find($this->articles('prototype')->id);

        $this->assertFalse($article->getAssociation('Categories')->isLoaded());
        $article->categories;
        $this->assertTrue($article->getAssociation('Categories')->isLoaded());
    }


    /*##########################################################################
    # Test getting associated objects
    ##########################################################################*/

    // test HABTM association
    public function testGetAssociation()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');
        $article = Article::find($this->articles('prototype')->id);

        $this->assertTrue($article->categories instanceof Mad_Model_Collection);
        $this->assertTrue($article->categories[0] instanceof Category);
    }

    // test HABTM: make sure associated object gets cached
    public function testGetAssociationCached()
    {
        $this->useMockLogger();
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);
        $article->categories;
        $this->assertLogged('Category Load');
        $this->clearLog();

        // make sure it doesn't query qgain
        $article->categories;
        $this->assertNotLogged('Category Load');
    }


    /*##########################################################################
    # Test getting associated object count
    ##########################################################################*/

    // test association count
    public function testGetAssociationCount()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);

        $this->assertTrue(is_numeric($article->categoryCount));
        $this->assertTrue($article->categoryCount > 0);

        $this->assertEquals(count($article->categories), (int)$article->categoryCount);
    }

    // test HABTM: make sure associated count gets cached
    public function testGetAssociationCountCached()
    {
        $this->useMockLogger();
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);
        $article->categoryCount;
        $this->assertLogged('Category Count');
        $this->clearLog();

        // make sure it doesn't query qgain
        $article->categoryCount;
        $this->assertNotLogged('Category Count');
    }


    /*##########################################################################
    # Test getting associated object ids
    ##########################################################################*/

    // test HABTM association
    public function testGetAssociationIds()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');
        $article = Article::find($this->articles('prototype')->id);

        $this->assertTrue(is_array($article->categoryIds));
        $this->assertTrue(count($article->categoryIds) > 0);

        $this->assertEquals($this->categories('php')->id, $article->categoryIds[0]);
    }

    // test HABTM: make sure associated object gets cached
    public function testGetAssociationIdsCached()
    {
        $this->useMockLogger();

        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);
        $article->categoryIds;
        $this->assertLogged('Category Load');
        $this->clearLog();

        // make sure it doesn't query qgain
        $article->categoryIds;
        $this->assertNotLogged('Category Load');
    }


    /*##########################################################################
    # Setting associations objects
    ##########################################################################*/

    // test HABTM: test saving an association object
    public function testSetAssociationNewObjects()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = new Article(array('title' => 'Article A'));
        $category1 = new Category(array('name' => 'Category A'));
        $category2 = new Category(array('name' => 'Category B'));
        $article->categories = array($category1, $category2);

        $this->assertEquals($category1, $article->categories[0]);
        $this->assertEquals($category2, $article->categories[1]);

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find('first', array('conditions' => 'title=:title'), 
                                          array(':title' => 'Article A'));
        $this->assertTrue($article instanceof Article);

        $cat1 = Category::find('first', array('conditions' => 'name=:nm'), array(':nm' => 'Category A'));
        $cat2 = Category::find('first', array('conditions' => 'name=:nm'), array(':nm' => 'Category B'));
        $this->assertTrue($cat1 instanceof Category);
        $this->assertTrue($cat2 instanceof Category);

        $this->assertEquals($article->categories[0]->id, $cat1->id);
        $this->assertEquals($article->categories[1]->id, $cat2->id);
    }

    // test HABTM: test saving an association object
    public function testSetAssociationNewAssocObjects()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);
        $this->assertTrue(count($article->categories) > 0);

        $category1 = new Category(array('name' => 'Category A'));
        $category2 = new Category(array('name' => 'Category B'));
        $article->categories = array($category1, $category2);

        $this->assertEquals($category1, $article->categories[0]);
        $this->assertEquals($category2, $article->categories[1]);

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find($this->articles('prototype')->id);
        $this->assertTrue($article instanceof Article);

        $cat1 = Category::find('first', array('conditions' => 'name=:nm'), array(':nm' => 'Category A'));
        $cat2 = Category::find('first', array('conditions' => 'name=:nm'), array(':nm' => 'Category B'));
        $this->assertTrue($cat1 instanceof Category);
        $this->assertTrue($cat2 instanceof Category);

        $this->assertEquals($article->categories[0]->id, $cat1->id);
        $this->assertEquals($article->categories[1]->id, $cat2->id);
    }

    // test HABTM: test saving an association object
    public function testSetAssociationExistingAssocObjects()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);
        $this->assertTrue(count($article->categories) > 0);

        $category1 = Category::find($this->categories('ruby')->id);
        $category2 = Category::find($this->categories('programming')->id);
        $article->categories = array($category1, $category2);

        $this->assertEquals($category1, $article->categories[0]);
        $this->assertEquals($category2, $article->categories[1]);

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find($this->articles('prototype')->id);
        $this->assertEquals(2, count($article->categories));

        $cat1 = Category::find($this->categories('ruby')->id);
        $cat2 = Category::find($this->categories('programming')->id);

        $this->assertTrue($cat1 instanceof Category);
        $this->assertTrue($cat2 instanceof Category);

        $this->assertEquals($article->categories[0]->id, $cat1->id);
        $this->assertEquals($article->categories[1]->id, $cat2->id);
    }

    // can't set object associations by both id and object reference
    public function testSetAssociationMixedArgs()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);

        $e = null;
        try {
            $article->categoryIds = array($this->categories('ruby')->id);

            $cat = Category::find($this->categories('php')->id);
            $article->addCategory($cat);

        } catch (Exception $e) {}
        $this->assertTrue($e instanceof Mad_Model_Association_Exception);
    }


    /*##########################################################################
    # Setting association object ids
    ##########################################################################*/

    // test HABTM: test saving an association object
    public function testSetAssociationIdsNewObject()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = new Article(array('title' => 'Foo'));
        $article->categoryIds = array($this->categories('ruby')->id, $this->categories('programming')->id);

        $this->assertEquals($this->categories('ruby')->id,        $article->categoryIds[0]);
        $this->assertEquals($this->categories('programming')->id, $article->categoryIds[1]);

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find('first', array('conditions' => 'title=:title'), 
                                          array(':title' => 'Foo'));
        $this->assertTrue($article instanceof Article);
        $this->assertEquals(2, count($article->categories));

        $cat1 = Category::find($this->categories('ruby')->id);
        $cat2 = Category::find($this->categories('programming')->id);

        $this->assertTrue($cat1 instanceof Category);
        $this->assertTrue($cat2 instanceof Category);

        $this->assertEquals($article->categories[0]->id, $cat1->id);
        $this->assertEquals($article->categories[1]->id, $cat2->id);
    }

    // set primary ids for existing associated models directly
    public function testSetAssociationIdsExistingAssocObjects()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);
        $this->assertTrue(count($article->categories) > 0);

        $article->categoryIds = array($this->categories('ruby')->id, $this->categories('programming')->id);

        $this->assertEquals($this->categories('ruby')->id,        $article->categoryIds[0]);
        $this->assertEquals($this->categories('programming')->id, $article->categoryIds[1]);

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find($this->articles('prototype')->id);
        $this->assertEquals(2, count($article->categories));

        $cat1 = Category::find($this->categories('ruby')->id);
        $cat2 = Category::find($this->categories('programming')->id);

        $this->assertTrue($cat1 instanceof Category);
        $this->assertTrue($cat2 instanceof Category);

        $this->assertEquals($article->categories[0]->id, $cat1->id);
        $this->assertEquals($article->categories[1]->id, $cat2->id);
    }


    /*##########################################################################
    # Adding association objects
    ##########################################################################*/

    // add associated objects to new model
    public function testAddAssociationNewObjects()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = new Article(array('title' => 'Article A'));
        $category1 = new Category(array('name' => 'Category A'));
        $category2 = new Category(array('name' => 'Category B'));

        $article->addCategory($category1);
        $article->addCategory($category2);

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find('first', array('conditions' => 'title=:title'), 
                                          array(':title' => 'Article A'));
        $this->assertTrue($article instanceof Article);

        $cat1 = Category::find('first', array('conditions' => 'name=:nm'), array(':nm' => 'Category A'));
        $cat2 = Category::find('first', array('conditions' => 'name=:nm'), array(':nm' => 'Category B'));
        $this->assertTrue($cat1 instanceof Category);
        $this->assertTrue($cat2 instanceof Category);

        $this->assertEquals($article->categories[0]->id, $cat1->id);
        $this->assertEquals($article->categories[1]->id, $cat2->id);
    }

    // add associated objects to existing collection in an existing model
    public function testAddAssociationExistingObjects()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('logging')->id);
        $origCnt = count($article->categories);
        $this->assertEquals(0, $origCnt);

        $article->addCategory(Category::find($this->categories('programming')->id));
        $article->addCategory(Category::find($this->categories('ruby')->id));

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find($this->articles('prototype')->id);
        $this->assertTrue($article instanceof Article);

        // make sure 2 were added to total count
        $newCnt = count($article->categories);
        $this->assertEquals(2, $newCnt - $origCnt);
    }

    // add associated objects to existing collection in an existing model
    public function testAddAssociationExistingObjectsUnique()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);
        $origCnt = count($article->categories);
        $this->assertTrue($origCnt > 0);

        $article->addCategory(Category::find($this->categories('programming')->id));
        $article->addCategory(Category::find($this->categories('ruby')->id));

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find($this->articles('prototype')->id);
        $this->assertTrue($article instanceof Article);

        // make sure 2 were added to total count
        $newCnt = count($article->categories);
        $this->assertEquals(1, $newCnt - $origCnt);
    }


    // add associated objects to existing collection in an existing model
    public function testAddAssociationsExistingObjects()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('logging')->id);
        $origCnt = count($article->categories);
        $this->assertEquals(0, $origCnt);

        $article->addCategory(array(Category::find($this->categories('programming')->id), 
                                    Category::find($this->categories('ruby')->id)));

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find($this->articles('prototype')->id);
        $this->assertTrue($article instanceof Article);

        // make sure 2 were added to total count
        $newCnt = count($article->categories);
        $this->assertEquals(2, $newCnt - $origCnt);
    }


    /*##########################################################################
    # Test clearing associations
    ##########################################################################*/

    public function testClearAssociations()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);
        $this->assertTrue($article->categoryCount > 0);

        $article->clearCategories();
        $article->save();

        // refetch to make sure data was cleared
        $article = Article::find($this->articles('prototype')->id);
        $this->assertTrue($article instanceof Article);
        $this->assertEquals('0', $article->categoryCount);
    }


    /*##########################################################################
    # Test deleting specific associations
    ##########################################################################*/

    // delete associated objects by id
    public function testDeleteAssociationsById()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);
        $origCnt = count($article->categories);

        $article->deleteCategories(array($this->categories('php')->id, $this->categories('ruby')->id));
        $this->assertEquals(2, $origCnt - count($article->categories));

        // save, and make sure association was deleted
        $article->save();

        $article = Article::find($this->articles('prototype')->id);
        $this->assertEquals(2, $origCnt - count($article->categories));
    }

    // delete associated objects
    public function testDeleteAssociationsByObject()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);
        $origCnt = count($article->categories);

        $article->deleteCategories(array(Category::find($this->categories('php')->id), 
                                         Category::find($this->categories('ruby')->id)));
        $this->assertEquals(2, $origCnt - count($article->categories));

        // save, and make sure association was deleted
        $article->save();

        $article = Article::find($this->articles('prototype')->id);
        $this->assertEquals(2, $origCnt - count($article->categories));
    }


    /*##########################################################################
    # Test replacing associated objects
    ##########################################################################*/

    // replace existing associations using ids
    public function testHasManyReplaceAssociationsByIds()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);
        $this->assertTrue(count($article->categories) > 0);

        $article->replaceCategories(array($this->categories('ruby')->id, $this->categories('programming')->id));

        $this->assertEquals($this->categories('ruby')->id,        $article->categoryIds[0]);
        $this->assertEquals($this->categories('programming')->id, $article->categoryIds[1]);

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find($this->articles('prototype')->id);
        $this->assertEquals(2, count($article->categories));

        $cat1 = Category::find($this->categories('ruby')->id);
        $cat2 = Category::find($this->categories('programming')->id);

        $this->assertEquals($article->categories[0]->id, $cat1->id);
        $this->assertEquals($article->categories[1]->id, $cat2->id);
    }

    // replace existing associations using objects
    public function testHasManyReplaceAssociationsByObjects()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);
        $this->assertTrue(count($article->categories) > 0);

        $article->replaceCategories(array(Category::find($this->categories('ruby')->id), 
                                          Category::find($this->categories('programming')->id)));

        $this->assertEquals($this->categories('ruby')->id,        $article->categoryIds[0]);
        $this->assertEquals($this->categories('programming')->id, $article->categoryIds[1]);

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find($this->articles('prototype')->id);
        $this->assertEquals(2, count($article->categories));

        $cat1 = Category::find($this->categories('ruby')->id);
        $cat2 = Category::find($this->categories('programming')->id);

        $this->assertEquals($article->categories[0]->id, $cat1->id);
        $this->assertEquals($article->categories[1]->id, $cat2->id);
    }


    /*##########################################################################
    # Test finding specific associations
    ##########################################################################*/

    // find first single associated object
    public function testFindAssociation()
    {
        $this->fixtures('categories', 'articles', 'articles_categories');

        $article = Article::find($this->articles('prototype')->id);
        $cats = $article->findCategories('all', array('conditions' => "name = :name"),
                                                array(':name' => 'Ruby'));
        $this->assertTrue($cats instanceof Mad_Model_Collection);
        $this->assertTrue(count($cats) > 0);

        foreach ($cats as $cat) {
            $this->assertEquals('Ruby', $cat->name);
        }
    }


    /*##########################################################################
    ##########################################################################*/
}