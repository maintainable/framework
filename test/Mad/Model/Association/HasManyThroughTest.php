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
class Mad_Model_Association_HasManyThroughTest extends Mad_Test_Unit
{

    /*##########################################################################
    # Test getters
    ##########################################################################*/

    // the type of association
    public function testGetMacro()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), new Article);
        $this->assertEquals('hasManyThrough', $assoc->getMacro());
    }

    // the name of the association
    public function testGetAssocName()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), new Article);
        $this->assertEquals('Tags', $assoc->getAssocName());

        $options = array('through' => 'Taggings', 'className' => 'Tag');
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'foo', $options, new Article);
        $this->assertEquals('foo', $assoc->getAssocName());
    }

    // the class for the primary object
    public function testGetClass()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), new Article);
        $this->assertEquals('Article', $assoc->getClass());
    }

    // the model object for the primary object
    public function testGetModel()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), new Article);
        $this->assertTrue($assoc->getModel() instanceof Article);
    }

    // the table name for the primary object
    public function testTableName()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), new Article);
        $this->assertEquals('articles', $assoc->tableName());
    }

    // the value of the primary key
    public function testGetPkValue()
    {
        // pk defaults to 'folderid' (from FolderDO)
        $article = new Article(array('id' => '1'));
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), $article);
        $this->assertEquals('1', $assoc->getPkValue());

        // set to get value from a different col as the p
        $article = new Article(array('title' => 'asdf'));
        $options = array('through' => 'Taggings', 'primaryKey' => 'title');
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', $options, $article);

        $this->assertEquals('asdf', $assoc->getPkValue());
    }

    // the class of the associated object
    public function testGetAssocClass()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), new Article);
        $this->assertEquals('Tag', $assoc->getAssocClass());

        $options = array('through' => 'Taggings', 'className' => 'Tag');
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'test', $options, new Article);
        $this->assertEquals('Tag', $assoc->getAssocClass());
    }

    // the model object of the associated object
    public function testGetAssocModel()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), new Article);
        $this->assertTrue($assoc->getAssocModel() instanceof Tag);
    }

    // the table name of the associated object
    public function testGetAssocTable()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), new Article);
        $this->assertEquals('tags', $assoc->getAssocTable());
    }

    public function testGetFkName()
    {
        // normal assoc
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), new Article);
        $this->assertEquals('article_id', $assoc->getFkName());

        // option passed in
        $options = array('through' => 'Taggings', 'foreignKey' => 'asdf');
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', $options, new Article);
        $this->assertEquals('asdf', $assoc->getFkName());
    }

    public function testGetAssocFkName()
    {
        // normal assoc
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), new Article);
        $this->assertEquals('tag_id', $assoc->getAssocFkName());

        // option passed in
        $options = array('through' => 'Taggings', 'associationForeignKey' => 'blah');
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', $options, new Article);
        $this->assertEquals('blah', $assoc->getAssocFkName());
    }

    public function testGetJoinTable()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), new Article);
        $this->assertEquals('taggings', $assoc->getJoinTable());
    }

    // test getting an association by name for a model
    public function testGetModelAssociation()
    {
        $article = new Article();
        $assoc = $article->reflectOnAssociation('Tags');
        $this->assertTrue($assoc instanceof Mad_Model_Association_HasManyThrough);
    }


    /*##########################################################################
    # Test dynamic method instantiation
    ##########################################################################*/

    // test get the created methods
    public function testGetMethods()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), new Article);
        $expected = array(
            'tags'        => 'getObjects',
            'tagIds'      => 'getObjectIds',
            'tagCount'    => 'getObjectCount',
            'addTag'      => 'addObject',
            'deleteTags'  => 'deleteObjects',
            'clearTags'   => 'clearObjects',
            'findTags'    => 'findObjects',
        );
        $this->assertEquals($expected, $assoc->getMethods());
    }

    public function testSetLoaded()
    {
        $this->useMockLogger();

        $this->fixtures('articles', 'taggings', 'tags');

        $article = Article::find($this->articles('testing_js')->id);
        $article->setAssociationLoaded('Tags');
        $this->clearLog();

        // make sure it doesn't query qgain
        $article->tags;
        $this->assertNotLogged('Tag Load');
    }

    // setting object as loaded will prevent it from querying
    public function testIsLoaded()
    {
        $this->fixtures('articles', 'taggings', 'tags');
        $article = Article::find($this->articles('testing_js')->id);

        $this->assertFalse($article->reflectOnAssociation('Tags')->isLoaded());
        $article->tags;
        $this->assertTrue($article->reflectOnAssociation('Tags')->isLoaded());
    }


    /*##########################################################################
    # Test getting associated objects
    ##########################################################################*/

    // test hasMany association
    public function testGetAssociation()
    {
        $this->fixtures('articles', 'taggings', 'tags');

        $article = Article::find($this->articles('testing_js')->id);

        $this->assertTrue($article->tags instanceof Mad_Model_Collection);
        $this->assertTrue($article->tags[0] instanceof Tag);
    }

    public function testGetAssociationCached()
    {
        $this->useMockLogger();

        $this->fixtures('articles', 'taggings', 'tags');

        $article = Article::find($this->articles('testing_js')->id);
        $article->tags;
        $this->assertLogged('Tag Load');
        $this->clearLog();

        // make sure it doesn't query qgain
        $article->tags;
        $this->assertNotLogged('Tag Load');
    }

    public function testGetAssociationIds()
    {
        $this->fixtures('articles', 'taggings', 'tags');

        $article = Article::find($this->articles('testing_js')->id);

        $this->assertTrue(is_array($article->tagIds));
        $this->assertTrue(count($article->tagIds) > 0);

        $this->assertEquals($this->tags('ruby_tag')->id, $article->tagIds[0]);
        $this->assertEquals($this->tags('js_tag')->id,   $article->tagIds[1]);
    }

    // test association count
    public function testGetAssociationCount()
    {
        $this->fixtures('articles', 'taggings', 'tags');

        $article = Article::find($this->articles('testing_js')->id);

        $this->assertTrue(is_numeric($article->tagCount));
        $this->assertTrue($article->tagCount > 0);

        $this->assertEquals(count($article->tags), (int)$article->tagCount);
    }

    public function testGetAssociationCountCached()
    {
        $this->useMockLogger();

        $this->fixtures('articles', 'taggings', 'tags');

        $article = Article::find($this->articles('testing_js')->id);
        $article->tagCount;
        $this->assertLogged('Tag Count');
        $this->clearLog();

        // make sure it doesn't query qgain
        $article->tagCount;
        $this->assertNotLogged('Tag Count');
    }


    /*##########################################################################
    # Adding association objects
    ##########################################################################*/

    // add associated objects to new model. objects MUST already be saved
    public function testAddAssociationNewObjects()
    {
        $this->fixtures('articles', 'taggings', 'tags');

        $article = Article::find($this->articles('testing_js')->id);
        $tag1 = new Tag(array('name' => 'Foo'));
        $tag2 = new Tag(array('name' => 'Bar'));

        $article->addTag($tag1);
        $article->addTag($tag2);

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find($this->articles('testing_js')->id);

        $tag1 = Tag::find('first', array('conditions' => 'name=:nm'), 
                                           array(':nm' => 'Foo'));
        $tag2 = Tag::find('first', array('conditions' => 'name=:nm'), 
                                           array(':nm' => 'Bar'));
        $this->assertTrue($tag1 instanceof Tag);
        $this->assertTrue($tag2 instanceof Tag);

        $this->assertEquals($tag1->articles[0]->id, $article->id);
        $this->assertEquals($tag2->articles[0]->id, $article->id);
    }

    // add associated objects to existing collection in an existing model
    public function testAddAssociationExistingObjects()
    {
        $this->fixtures('articles', 'taggings', 'tags');

        $article = Article::find($this->articles('xml_rpc')->id);
        $origCnt = count($article->tags);
        $this->assertTrue($origCnt > 0);

        $article->addTag(Tag::find($this->tags('ruby_tag')->id));
        $article->addTag(Tag::find($this->tags('js_tag')->id));

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find($this->articles('xml_rpc')->id);
        $this->assertEquals($origCnt + 2, count($article->tags));

        $tag1 = Tag::find($this->tags('ruby_tag')->id);
        $tag2 = Tag::find($this->tags('js_tag')->id);

        $this->assertTrue(in_array($article->id, $tag1->articleIds));
        $this->assertTrue(in_array($article->id, $tag2->articleIds));
    }

    // add associated objects to new model
    public function testAddAssociationsExistingObjects()
    {
        $this->fixtures('articles', 'taggings', 'tags');

        $article = Article::find($this->articles('xml_rpc')->id);
        $origCnt = count($article->tags);
        $this->assertTrue($origCnt > 0);

        $article->addTag(array(Tag::find($this->tags('ruby_tag')->id), 
                               Tag::find($this->tags('js_tag')->id)
                        ));

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find($this->articles('xml_rpc')->id);
        $this->assertEquals($origCnt + 2, count($article->tags));

        $tag1 = Tag::find($this->tags('ruby_tag')->id);
        $tag2 = Tag::find($this->tags('js_tag')->id);

        $this->assertTrue(in_array($article->id, $tag1->articleIds));
        $this->assertTrue(in_array($article->id, $tag2->articleIds));
    }

    public function testClearAssociations()
    {
        $this->fixtures('articles', 'taggings', 'tags');

        $article = Article::find($this->articles('testing_js')->id);
        $this->assertTrue($article->tagCount > 0);

        $article->clearTags();
        $article->save();

        // refetch to make sure data was cleared
        $article = Article::find($this->articles('testing_js')->id);
        $this->assertEquals('0', $article->tagCount);
    }

    // delete associated objects by id
    public function testDeleteAssociationsById()
    {
        $this->fixtures('articles', 'taggings', 'tags');

        $article = Article::find($this->articles('testing_js')->id);
        $origCnt = count($article->tags);

        $article->deleteTags(array($this->tags('ruby_tag')->id));
        $this->assertEquals(1, $origCnt - count($article->tags));

        // save, and make sure association was deleted
        $article->save();

        $article = Article::find($this->articles('testing_js')->id);
        $this->assertEquals(1, $origCnt - count($article->tags));
    }

    // delete associated objects
    public function testDeleteAssociationsByObject()
    {
        $this->fixtures('articles', 'taggings', 'tags');

        $article = Article::find($this->articles('testing_js')->id);
        $origCnt = count($article->tags);

        $article->deleteTags(array(Tag::find($this->tags('ruby_tag')->id)));
        $this->assertEquals(1, $origCnt - count($article->tags));

        // save, and make sure association was deleted
        $article->save();

        $article = Article::find($this->articles('testing_js')->id);
        $this->assertEquals(1, $origCnt - count($article->tags));
    }

    // find first single associated object
    public function testFindAssociations()
    {
        $this->fixtures('articles', 'taggings', 'tags');

        $article = Article::find($this->articles('testing_js')->id);

        $tags = $article->findTags('all', array('conditions' => "name = :nm",
                                                'order'      => "name"),
                                          array(':nm' => 'Ruby'));
        $this->assertTrue($tags instanceof Mad_Model_Collection);
        $this->assertTrue(count($tags) > 0);

        foreach ($tags as $tag) {
            $this->assertEquals('Ruby', $tag->name);
            $this->assertTrue(in_array($article->id, $tag->articleIds));
        }
    }

    /*##########################################################################
    ##########################################################################*/
}