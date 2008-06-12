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
class Mad_Model_Association_HasManyTest extends Mad_Test_Unit
{

    /*##########################################################################
    # Test getters
    ##########################################################################*/

    // the type of association
    public function testGetMacro()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', array(), new User);
        $this->assertEquals('hasMany', $assoc->getMacro());
    }

    // the name of the association
    public function testGetAssocName()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', array(), new User);
        $this->assertEquals('Articles', $assoc->getAssocName());

        $options = array('className' => 'Article');
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'whatever', $options, new User);
        $this->assertEquals('whatever', $assoc->getAssocName());
    }

    // the class for the primary object
    public function testGetClass()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', array(), new User);
        $this->assertEquals('User', $assoc->getClass());
    }

    // the model object for the primary object
    public function testGetModel()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', array(), new User);
        $this->assertTrue($assoc->getModel() instanceof User);
    }

    // the table name for the primary object
    public function testTableName()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', array(), new User);
        $this->assertEquals('users', $assoc->tableName());
    }

    // the value of the primary key
    public function testGetPkValue()
    {
        // pk defaults to 'folderid' (from FolderDO)
        $user = new User(array('id' => '1'));
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', array(), $user);
        $this->assertEquals('1', $assoc->getPkValue());

        // set to get value from a different col as the p
        $user = new User(array('name' => 'asdf'));
        $options = array('primaryKey' => 'name');
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', $options, $user);
        $this->assertEquals('asdf', $assoc->getPkValue());
    }

    // the class of the associated object
    public function testGetAssocClass()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', array(), new User);
        $this->assertEquals('Article', $assoc->getAssocClass());

        $options = array('className' => 'Article');
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'test', $options, new User);
        $this->assertEquals('Article', $assoc->getAssocClass());
    }

    // the model object of the associated object
    public function testGetAssocModel()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', array(), new User);
        $this->assertTrue($assoc->getAssocModel() instanceof Article);
    }

    // the table name of the associated object
    public function testGetAssocTable()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', array(), new User);
        $this->assertEquals('articles', $assoc->getAssocTable());
    }

    public function testGetPkName()
    {
        // normal assoc
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', array(), new User);
        $this->assertEquals('id', $assoc->getPkName());

        // option passed in
        $options = array('primaryKey' => 'asdf');
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', $options, new User);
        $this->assertEquals('asdf', $assoc->getPkName());
    }

    public function testGetFkName()
    {
        // normal assoc
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', array(), new User);
        $this->assertEquals('user_id', $assoc->getFkName());

        // option passed in
        $options = array('foreignKey' => 'asdf');
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', $options, new User);
        $this->assertEquals('asdf', $assoc->getFkName());
    }

    // test getting an association by name for a model
    public function testGetModelAssociation()
    {
        $user = new User();
        $assoc = $user->reflectOnAssociation('Articles');
        $this->assertTrue($assoc instanceof Mad_Model_Association_HasMany);
    }


    /*##########################################################################
    # Test dynamic method instantiation
    ##########################################################################*/

    // test get the created methods
    public function testGetMethods()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', array(), new User);
        $expected = array(
            'articles'        => 'getObjects',
            'articles='       => 'setObjects',
            'articleIds'      => 'getObjectIds',
            'articleIds='     => 'setObjectIds',
            'articleCount'    => 'getObjectCount',
            'addArticle'      => 'addObject',
            'replaceArticles' => 'replaceObjects',
            'deleteArticles'  => 'deleteObjects',
            'clearArticles'   => 'clearObjects',
            'findArticles'    => 'findObjects',
            'buildArticle'    => 'buildObject',
            'createArticle'   => 'createObject'
        );
        $this->assertEquals($expected, $assoc->getMethods());
    }

    // setting object as loaded will prevent it from querying
    public function testSetLoaded()
    {
        $this->useMockLogger();
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $user->setAssociationLoaded('Articles');
        $this->clearLog();

        // make sure it doesn't query qgain
        $user->articles;
        $this->assertNotLogged('Article Load');
    }

    // setting object as loaded will prevent it from querying
    public function testIsLoaded()
    {
        $this->fixtures('users', 'articles');
        $user = User::find($this->users('derek')->id);

        $this->assertFalse($user->reflectOnAssociation('Articles')->isLoaded());
        $user->articles;
        $this->assertTrue($user->reflectOnAssociation('Articles')->isLoaded());
    }


    /*##########################################################################
    # Test getting associated objects
    ##########################################################################*/

    // test hasMany association
    public function testGetAssociation()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $this->assertTrue($user->articles instanceof Mad_Model_Collection);
        $this->assertTrue($user->articles[0] instanceof Article);
    }

    // test hasMany: make sure associated objects gets cached
    public function testGetAssociationCached()
    {
        $this->useMockLogger();
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $user->articles;
        $this->assertLogged('Article Load');
        $this->clearLog();

        // make sure it doesn't query qgain
        $user->articles;
        $this->assertNotLogged('Article Load');
    }


    /*##########################################################################
    # Test getting associated object ids
    ##########################################################################*/

    public function testGetAssociationIds()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $this->assertTrue(is_array($user->articleIds));
        $this->assertTrue(count($user->articleIds) > 0);

        $this->assertEquals($this->articles('testing_js')->id, $user->articleIds[0]);
        $this->assertEquals($this->articles('prototype')->id,  $user->articleIds[1]);
    }

    public function testGetAssociationIdsCached()
    {
        $this->useMockLogger();
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $user->articleIds;
        $this->assertLogged('Article Load');
        $this->clearLog();

        // make sure it doesn't query qgain
        $user->articleIds;
        $this->assertNotLogged('Article Load');
    }


    /*##########################################################################
    # Test getting associated object count
    ##########################################################################*/

    // test association count
    public function testGetAssociationCount()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);

        $this->assertTrue(is_numeric($user->articleCount));
        $this->assertTrue($user->articleCount > 0);

        $this->assertEquals(count($user->articles), (int)$user->articleCount);
    }

    // test hasMany: make sure associated count gets cached
    public function testGetAssociationCountCached()
    {
        $this->useMockLogger();
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $user->articleCount;
        $this->assertLogged('Article Count');
        $this->clearLog();

        // make sure it doesn't query qgain
        $user->articleCount;
        $this->assertNotLogged('Article Count');
    }


    /*##########################################################################
    # Setting associations objects
    ##########################################################################*/

    // test hasMany: test saving an association object
    public function testSetAssociationNewObjects()
    {
        $this->fixtures('users', 'articles');

        $user = new User(array('name' => 'Name Foo'));

        $article1 = new Article(array('title' => 'Article 1'));
        $article2 = new Article(array('title' => 'Article 2'));
        $user->articles = array($article1, $article2);

        $this->assertEquals($article1, $user->articles[0]);
        $this->assertEquals($article2, $user->articles[1]);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user = User::find('first', array('conditions' => 'name=:nm'), array(':nm' => 'Name Foo'));
        $this->assertTrue($user instanceof User);

        $article1 = Article::find('first', array('conditions' => 'title=:nm'), array(':nm' => 'Article 1'));
        $article2 = Article::find('first', array('conditions' => 'title=:nm'), array(':nm' => 'Article 2'));
        $this->assertTrue($article1 instanceof Article);
        $this->assertTrue($article2 instanceof Article);

        $this->assertEquals($article1->user_id, $user->id);
        $this->assertEquals($article2->user_id, $user->id);
    }

    // test hasMany: test saving an association object
    public function testSetAssociationNewAssocObjects()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $this->assertTrue(count($user->articles) > 0);

        $article1 = new Article(array('title' => 'Article 1'));
        $article2 = new Article(array('title' => 'Article 2'));
        $user->articles = array($article1, $article2);

        $this->assertEquals($article1, $user->articles[0]);
        $this->assertEquals($article2, $user->articles[1]);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user = User::find($this->users('derek')->id);
        $this->assertEquals(2, count($user->articles));

        $article1 = Article::find('first', array('conditions' => 'title=:nm'), array(':nm' => 'Article 1'));
        $article2 = Article::find('first', array('conditions' => 'title=:nm'), array(':nm' => 'Article 2'));
        $this->assertTrue($article1 instanceof Article);
        $this->assertTrue($article2 instanceof Article);

        $this->assertEquals($article1->user_id, $user->id);
        $this->assertEquals($article2->user_id, $user->id);
    }

    // test hasMany: test saving an association object
    public function testSetAssociationExistingAssocObjects()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $this->assertTrue(count($user->articles) > 0);

        $article1 = Article::find($this->articles('xml_rpc')->id);
        $article2 = Article::find($this->articles('best_practices')->id);
        $user->articles = array($article1, $article2);

        $this->assertEquals($article1, $user->articles[0]);
        $this->assertEquals($article2, $user->articles[1]);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user = User::find($this->users('derek')->id);
        $this->assertTrue($user instanceof User);
        $this->assertEquals(2, count($user->articles));

        $article1 = Article::find($this->articles('xml_rpc')->id);
        $article2 = Article::find($this->articles('best_practices')->id);
        $this->assertTrue($article1 instanceof Article);
        $this->assertTrue($article2 instanceof Article);

        $this->assertEquals($article1->user_id, $user->id);
        $this->assertEquals($article2->user_id, $user->id);
    }

    // can't set object associations by both id and object reference
    public function testSetAssociationMixedArgs()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);

        $e = null;
        try {
            $user->articles = array($this->articles('best_practices')->id);

            $article = Article::find($this->articles('xml_rpc')->id);
            $user->addArticle($metadata);

        } catch (Exception $e) {}
        $this->assertTrue($e instanceof Mad_Model_Association_Exception);
    }


    /*##########################################################################
    # Setting association object ids
    ##########################################################################*/

    // test hasMany: test saving an association object
    public function testSetAssociationIdsNewObject()
    {
        $this->fixtures('users', 'articles');

        $user = new User(array('name' => 'User Foo 1'));
        $user->articleIds = array($this->articles('xml_rpc')->id, $this->articles('best_practices')->id);

        $this->assertEquals($this->articles('xml_rpc')->id,        $user->articleIds[0]);
        $this->assertEquals($this->articles('best_practices')->id, $user->articleIds[1]);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user = User::find('first', array('conditions' => 'name=:nm'), array(':nm' => 'User Foo 1'));
        $this->assertTrue($user instanceof User);
        $this->assertEquals(2, count($user->articles));

        $article1 = Article::find($this->articles('xml_rpc')->id);
        $article2 = Article::find($this->articles('best_practices')->id);
        $this->assertTrue($article1 instanceof Article);
        $this->assertTrue($article2 instanceof Article);

        $this->assertEquals($article1->user_id, $user->id);
        $this->assertEquals($article2->user_id, $user->id);
    }

    // set primary ids for existing associated models directly
    public function testSetAssociationIdsExistingAssocObjects()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $this->assertTrue(count($user->articles) > 0);

        $user->articleIds = array($this->articles('xml_rpc')->id, $this->articles('best_practices')->id);

        $this->assertEquals($this->articles('xml_rpc')->id,        $user->articleIds[0]);
        $this->assertEquals($this->articles('best_practices')->id, $user->articleIds[1]);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user = User::find($this->users('derek')->id);
        $this->assertTrue($user instanceof User);
        $this->assertEquals(2, count($user->articles));

        $article1 = Article::find($this->articles('xml_rpc')->id);
        $article2 = Article::find($this->articles('best_practices')->id);
        $this->assertTrue($article1 instanceof Article);
        $this->assertTrue($article2 instanceof Article);

        $this->assertEquals($article1->user_id, $user->id);
        $this->assertEquals($article2->user_id, $user->id);
    }


    /*##########################################################################
    # Adding association objects
    ##########################################################################*/

    // add associated objects to new model
    public function testAddAssociationNewObjects()
    {
        $this->fixtures('users', 'articles');

        $user = new User(array('name' => 'User Foo 1'));
        $article1 = new Article(array('title' => 'Article 1'));
        $article2 = new Article(array('title' => 'Article 2'));

        $user->addArticle($article1);
        $user->addArticle($article2);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user = User::find('first', array('conditions' => 'name=:nm'), array(':nm' => 'User Foo 1'));
        $this->assertTrue($user instanceof User);

        $article1 = Article::find('first', array('conditions' => 'title=:nm'), array(':nm' => 'Article 1'));
        $article2 = Article::find('first', array('conditions' => 'title=:nm'), array(':nm' => 'Article 2'));
        $this->assertTrue($article1 instanceof Article);
        $this->assertTrue($article2 instanceof Article);

        $this->assertEquals($article1->user_id, $user->id);
        $this->assertEquals($article2->user_id, $user->id);
    }

    // add associated objects to existing collection in an existing model
    public function testAddAssociationExistingObjects()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $origCnt = count($user->articles);
        $this->assertTrue($origCnt > 0);

        $user->addArticle(Article::find($this->articles('xml_rpc')->id));
        $user->addArticle(Article::find($this->articles('best_practices')->id));

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user = User::find($this->users('derek')->id);
        $this->assertTrue($user instanceof User);
        $this->assertEquals($origCnt + 2, count($user->articles));

        $article1 = Article::find($this->articles('xml_rpc')->id);
        $article2 = Article::find($this->articles('best_practices')->id);
        $this->assertTrue($article1 instanceof Article);
        $this->assertTrue($article2 instanceof Article);

        $this->assertEquals($article1->user_id, $user->id);
        $this->assertEquals($article2->user_id, $user->id);

        // make sure 2 were added to total count
        $newCnt = count($user->articles);
        $this->assertEquals(2, $newCnt - $origCnt);
    }

    // add associated objects to new model
    public function testAddAssociationsExistingObjects()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $origCnt = count($user->articles);
        $this->assertTrue($origCnt > 0);

        $user->addArticle(array(Article::find($this->articles('xml_rpc')->id), 
                                Article::find($this->articles('best_practices')->id)));

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user = User::find($this->users('derek')->id);
        $this->assertTrue($user instanceof User);
        $this->assertEquals($origCnt + 2, count($user->articles));

        $article1 = Article::find($this->articles('xml_rpc')->id);
        $article2 = Article::find($this->articles('best_practices')->id);
        $this->assertTrue($article1 instanceof Article);
        $this->assertTrue($article2 instanceof Article);

        $this->assertEquals($article1->user_id, $user->id);
        $this->assertEquals($article2->user_id, $user->id);

        // make sure 2 were added to total count
        $newCnt = count($user->articles);
        $this->assertEquals(2, $newCnt - $origCnt);
    }


    /*##########################################################################
    # Test clearing associations
    ##########################################################################*/

    public function testClearAssociations()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $this->assertTrue($user->articleCount > 0);

        $user->clearArticles();
        $user->save();

        // refetch to make sure data was cleared
        $user = User::find($this->users('derek')->id);
        $this->assertTrue($user instanceof User);
        $this->assertEquals('0', $user->articleCount);
    }


    /*##########################################################################
    # Test deleting specific associations
    ##########################################################################*/

    // delete associated objects by id
    public function testDeleteAssociationsById()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $origCnt = count($user->articles);

        $user->deleteArticles(array($this->articles('testing_js')->id,    
                                    $this->articles('prototype')->id));
        $this->assertEquals(2, $origCnt - count($user->articles));

        // save, and make sure association was deleted
        $user->save();

        $user = User::find($this->users('derek')->id);
        $this->assertEquals(2, $origCnt - count($user->articles));
    }

    // delete associated objects
    public function testDeleteAssociationsByObject()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $origCnt = count($user->articles);

        $user->deleteArticles(array(Article::find($this->articles('testing_js')->id), 
                                    Article::find($this->articles('prototype')->id)));
        $this->assertEquals(2, $origCnt - count($user->articles));

        // save, and make sure association was deleted
        $user->save();

        $user = User::find($this->users('derek')->id);
        $this->assertEquals(2, $origCnt - count($user->articles));
    }


    /*##########################################################################
    # Test replacing associated objects
    ##########################################################################*/

    // replace existing associations using ids
    public function testReplaceAssociationsByIds()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $this->assertTrue(count($user->articles) > 0);

        $user->replaceArticles(array($this->articles('testing_js')->id,    
                                     $this->articles('xml_rpc')->id));

        $this->assertEquals($this->articles('testing_js')->id, $user->articleIds[0]);
        $this->assertEquals($this->articles('xml_rpc')->id,    $user->articleIds[1]);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user = User::find($this->users('derek')->id);
        $this->assertTrue($user instanceof User);
        
        $this->assertEquals(2, count($user->articles));

        $article1 = Article::find($this->articles('testing_js')->id);
        $article2 = Article::find($this->articles('xml_rpc')->id);
        $this->assertTrue($article1 instanceof Article);
        $this->assertTrue($article2 instanceof Article);

        $this->assertEquals($article1->user_id, $user->id);
        $this->assertEquals($article2->user_id, $user->id);
    }

    // replace existing associations using objects
    public function testReplaceAssociationsByObjects()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $this->assertTrue(count($user->articles) > 0);

        $article1 = Article::find($this->articles('testing_js')->id);
        $article2 = Article::find($this->articles('xml_rpc')->id);
        $user->replaceArticles(array($article1, $article2));

        $this->assertEquals($article1, $user->articles[0]);
        $this->assertEquals($article2, $user->articles[1]);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user = User::find($this->users('derek')->id);
        $this->assertTrue($user instanceof User);
        
        $this->assertEquals(2, count($user->articles));

        $article1 = Article::find($this->articles('testing_js')->id);
        $article2 = Article::find($this->articles('xml_rpc')->id);
        $this->assertTrue($article1 instanceof Article);
        $this->assertTrue($article2 instanceof Article);

        $this->assertEquals($article1->user_id, $user->id);
        $this->assertEquals($article2->user_id, $user->id);
    }


    /*##########################################################################
    # Test finding specific associations
    ##########################################################################*/

    // find first single associated object
    public function testFindAssociations()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $articles = $user->findArticles('all', array('conditions' => "title = :title",
                                                     'order'      => "title"),
                                               array(':title' => 'Testing Javascript in Rails'));
        $this->assertTrue($articles instanceof Mad_Model_Collection);
        $this->assertTrue(count($articles) > 0);

        foreach ($articles as $article) {
            $this->assertEquals('Testing Javascript in Rails', $article->title);
            $this->assertEquals($this->users('derek')->id, $article->user_id);
        }
    }

    public function testFindFirstAssociation()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $article = $user->findArticles('first', array('conditions' => "title = :title",
                                                      'order'      => "title"),
                                                array(':title' => 'Testing Javascript in Rails'));
        $this->assertTrue($article instanceof Article);
    }

    /*##########################################################################
    # buildObject associations
    ##########################################################################*/

    // test HasMany:buildObject
    public function testBuildObject()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $article = $user->buildArticle(array('title' => 'Article Foo'));
        $this->assertEquals($article, $user->articles[count($user->articles)-1]);

        // this hasn't saved the associated object yet
        $notExists = Article::find('first', array('conditions' => 'title=:title'), 
                                            array(':title' => 'Article Foo'));
        $this->assertNull($notExists);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user = User::find($this->users('derek')->id);
        $article = Article::find('first', array('conditions' => 'title=:title'),     
                                          array(':title' => 'Article Foo'));

        $this->assertTrue($user instanceof User);
        $this->assertTrue($article instanceof Article);
        $this->assertEquals($article->user_id, $user->id);
    }

    public function testBuildObjectCanAcceptNoArguments()
    {
        $this->fixtures('articles', 'users');

        $user = User::find($this->users('derek')->id);
        try {
            $article = $user->buildArticle();
        } catch (Exception $e) { $this->fail('Unexepected exception raised'); }
    }


    /*##########################################################################
    # createObject associations
    ##########################################################################*/

    // test HasMany:buildObject
    public function testCreateObject()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $article = $user->createArticle(array('title' => 'Article Foo'));
        $this->assertEquals($article, $user->articles[count($user->articles)-1]);


        // this HAS saved the associated object
        $newArticle = Article::find('first', array('conditions' => 'title=:title'), 
                                             array(':title' => 'Article Foo'));
        $this->assertTrue($newArticle instanceof Article);
        $this->assertEquals($user->id, $newArticle->user_id);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user = User::find($this->users('derek')->id);
        $article = Article::find('first', array('conditions' => 'title=:title'),     
                                          array(':title' => 'Article Foo'));

        $this->assertTrue($user instanceof User);
        $this->assertTrue($article instanceof Article);
        $this->assertEquals($article->user_id, $user->id);
    }

    // test HasMany:buildObject
    public function testCreateObjectWithEmptyArguments()
    {
        $this->fixtures('users', 'articles');

        $user = User::find($this->users('derek')->id);
        $article = $user->createArticle();
        $this->assertEquals($article, $user->articles[count($user->articles)-1]);


        // this HAS saved the associated object
        $newArticle = Article::find('first', array('conditions' => 'title=:title'), 
                                             array(':title' => ''));
        $this->assertTrue($newArticle instanceof Article);
        $this->assertEquals($user->id, $newArticle->user_id);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user = User::find($this->users('derek')->id);
        $article = Article::find('first', array('conditions' => 'title=:title'),     
                                          array(':title' => ''));

        $this->assertTrue($user instanceof User);
        $this->assertTrue($article instanceof Article);
        $this->assertEquals($article->user_id, $user->id);
    }

    /*##########################################################################
    ##########################################################################*/
}