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
class Mad_Model_Association_BelongsToTest extends Mad_Test_Unit
{
    /*##########################################################################
    # Test getters
    ##########################################################################*/

    // the type of association
    public function testGetMacro()
    {
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', array(), new Article);
        $this->assertEquals('belongsTo', $assoc->getMacro());
    }

    // the name of the association
    public function testGetAssocName()
    {
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', array(), new Article);
        $this->assertEquals('User', $assoc->getAssocName());

        $options = array('className' => 'User');
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'whatever', $options, new Article);
        $this->assertEquals('whatever', $assoc->getAssocName());
    }

    // the class for the primary object
    public function testGetClass()
    {
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', array(), new Article);
        $this->assertEquals('Article', $assoc->getClass());
    }

    // the model object for the primary object
    public function testGetModel()
    {
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', array(), new Article);
        $this->assertTrue($assoc->getModel() instanceof Article);
    }

    // the table name for the primary object
    public function testTableName()
    {
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', array(), new Article);
        $this->assertEquals('articles', $assoc->tableName());
    }

    // the value of the primary key
    public function testGetPkValue()
    {
        // 'user_id' foreignKey
        $doc = new Article(array('user_id' => '1'));
        $options = array('foreignKey' => 'user_id');
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', $options, $doc);
        $this->assertEquals('1', $assoc->getPkValue());

        // 'user_id' foreignKey
        $doc = new Article(array('title' => 'asdf'));
        $options = array('foreignKey' => 'title');
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', $options, $doc);
        $this->assertEquals('asdf', $assoc->getPkValue());
    }

    // the class of the associated object
    public function testGetAssocClass()
    {
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', array(), new Article);
        $this->assertEquals('User', $assoc->getAssocClass());

        $options = array('className' => 'User');
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'test', $options, new Article);
        $this->assertEquals('User', $assoc->getAssocClass());
    }

    // the model object of the associated object
    public function testGetAssocModel()
    {
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', array(), new Article);
        $this->assertTrue($assoc->getAssocModel() instanceof User);
    }

    // the table name of the associated object
    public function testGetAssocTable()
    {
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', array(), new Article);
        $this->assertEquals('users', $assoc->getAssocTable());
    }

    public function testGetPkName()
    {
        // normal assoc
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', array(), new Article);
        $this->assertEquals('id', $assoc->getPkName());

        // option passed in
        $options = array('primaryKey' => 'asdf');
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', $options, new Article);
        $this->assertEquals('asdf', $assoc->getPkName());
    }

    public function testGetFkName()
    {
        // normal assoc
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', array(), new Article);
        $this->assertEquals('user_id', $assoc->getFkName());

        // option passed in
        $options = array('foreignKey' => 'asdf');
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', $options, new Article);
        $this->assertEquals('asdf', $assoc->getFkName());
    }

    // test getting an association by name for a model
    public function testGetModelAssociation()
    {
        $article = new Article();
        $assoc = $article->getAssociation('User');
        $this->assertTrue($assoc instanceof Mad_Model_Association_BelongsTo);
    }


    /*##########################################################################
    # Test dynamic method instantiation
    ##########################################################################*/

    // test get the created methods
    public function testGetMethods()
    {
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', array(), new Article);
        $expected = array(
            'user'       => 'getObject',
            'user='      => 'setObject',
            'buildUser'  => 'buildObject',
            'createUser' => 'createObject'
        );
        $this->assertEquals($expected, $assoc->getMethods());
    }


    /*##########################################################################
    # Test actual association method execution
    ##########################################################################*/

    // test belongsTo: getting associated object
    public function testGetAssociation()
    {
        $this->fixtures('articles', 'users');

        $article = Article::find($this->articles('xml_rpc')->id);

        $this->assertTrue($article->user instanceof User);
        $this->assertEquals('Mike', $article->user->name);
    }

    // test belongsTo: make sure associated object gets cached
    public function testGetAssociationCached()
    {
        $this->useMockLogger();

        $this->fixtures('articles', 'users');

        $article = Article::find($this->articles('xml_rpc')->id);
        $article->user;
        $this->assertLogged('User Load');
        $this->clearLog();

        // make sure it doesn't query qgain
        $article->user->name;
        $this->assertNotLogged('User Load');
    }

    // setting object as loaded will prevent it from querying
    public function testSetLoaded()
    {
        $this->useMockLogger();

        $this->fixtures('articles', 'users');

        $article = Article::find($this->articles('xml_rpc')->id);
        $article->setAssociationLoaded('User');
        $this->clearLog();

        // make sure it doesn't query qgain
        $article->user;
        $this->assertNotLogged('User Load');
    }

    // setting object as loaded will prevent it from querying
    public function testIsLoaded()
    {
        $this->fixtures('articles', 'users');
        $article = Article::find($this->articles('xml_rpc')->id);

        $this->assertFalse($article->getAssociation('User')->isLoaded());
        $article->user;
        $this->assertTrue($article->getAssociation('User')->isLoaded());
    }


    /*##########################################################################
    # Saving associations
    ##########################################################################*/

    // test belongsTo: test saving an association object
    public function testSetAssociationNewObject()
    {
        $this->fixtures('articles', 'users');

        $article = new Article(array('title' => 'Test Foo'));
        $user    = new User(array('name' => 'Test Bar'));
        $article->user = $user;

        $this->assertEquals($user, $article->user);

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find('first', array('conditions' => 'title=:title'), array(':title' => 'Test Foo'));
        $user    = User::find('first',    array('conditions' => 'name=:nm'),     array(':nm'    => 'Test Bar'));
        
        $this->assertTrue($article instanceof Article);
        $this->assertTrue($user instanceof User);
        $this->assertEquals($article->user_id, $user->id);
    }

    // test belongsTo: test saving an association object
    public function testSetAssociationNewAssocObject()
    {
        $this->fixtures('articles', 'users');

        $article = Article::find($this->articles('xml_rpc')->id);
        $user    = new User(array('name' => 'Test Bar'));
        $article->user = $user;

        $this->assertEquals($user, $article->user);

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find($this->articles('xml_rpc')->id);
        $user    = User::find('first',    array('conditions' => 'name=:nm'),     
                                          array(':nm'    => 'Test Bar'));

        $this->assertTrue($article instanceof Article);
        $this->assertTrue($user instanceof User);
        $this->assertEquals($user->id, $article->user_id);
    }

    // test belongsTo: test saving an association object
    public function testSetAssociationExistingAssocObject()
    {
        $this->fixtures('articles', 'users');

        $article = Article::find($this->articles('xml_rpc')->id);
        $user    = User::find($this->users('derek')->id);

        $article->user = $user;
        $article->user->name = 'Test Bar';

        $this->assertEquals($user, $article->user);

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find($this->articles('xml_rpc')->id);
        $user    = User::find($this->users('derek')->id);

        $this->assertTrue($article instanceof Article);
        $this->assertTrue($user instanceof User);
        $this->assertEquals($user->id, $article->user_id);

        // make sure name updated
        $this->assertEquals('Test Bar', $user->name);
    }


    /*##########################################################################
    # buildObject associations
    ##########################################################################*/

    // test belongsTo:buildObject
    public function testBuildObject()
    {
        $this->fixtures('articles', 'users');

        $article = new Article(array('title' => 'Test Foo'));
        $user    = $article->buildUser(array('name' => 'Test Bar'));

        $this->assertEquals($user, $article->user);

        // this hasn't saved the associated object yet
        $notExists = User::find('first', array('conditions' => 'name=:nm'),     
                                         array(':nm'        => 'Test Bar'));
        $this->assertNull($notExists);


        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find('first', array('conditions' => 'title=:title'), array(':title' => 'Test Foo'));
        $user    = User::find('first',    array('conditions' => 'name=:nm'),     array(':nm'    => 'Test Bar'));

        $this->assertTrue($article instanceof Article);
        $this->assertTrue($user instanceof User);
        $this->assertEquals($article->user_id, $user->id);
    }

    public function testBuildObjectCanAcceptNoArguments()
    {
        $this->fixtures('articles', 'users');

        $article = Article::find($this->articles('xml_rpc')->id);
        try {
            $user = $article->buildUser();
        } catch (Exception $e) { $this->fail('Unexepected exception raised'); }
    }


    /*##########################################################################
    # createObject associations
    ##########################################################################*/

    // test belongsTo:buildObject
    public function testCreateObject()
    {
        $this->fixtures('articles', 'users');

        $article = new Article(array('title' => 'Test Foo'));
        $newUser = $article->createUser(array('name' => 'Test Bar'));
        $this->assertEquals($newUser->id, $article->user->id);

        // this HAS saved the associated object
        $user    = User::find('first', array('conditions' => 'name=:nm'),     
                                       array(':nm'    => 'Test Bar'));
        $this->assertTrue($user instanceof User);
        $this->assertEquals($article->user_id, $user->id);

        // save, and make sure the association object is created
        $article->save();

        // make sure both were created, and are associated
        $article = Article::find('first', array('conditions' => 'title=:title'), array(':title' => 'Test Foo'));
        $user    = User::find('first',    array('conditions' => 'name=:nm'),     array(':nm'    => 'Test Bar'));

        $this->assertTrue($article instanceof Article);
        $this->assertTrue($user instanceof User);
        $this->assertEquals($article->user_id, $user->id);
    }


    /*##########################################################################
    ##########################################################################*/

}