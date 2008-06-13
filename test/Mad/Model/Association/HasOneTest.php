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
class Mad_Model_Association_HasOneTest extends Mad_Test_Unit
{
    /*##########################################################################
    # Test getters
    ##########################################################################*/

    // the type of association
    public function testGetMacro()
    {
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', array(), new User);
        $this->assertEquals('hasOne', $assoc->getMacro());
    }

    // the name of the association
    public function testGetAssocName()
    {
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', array(), new User);
        $this->assertEquals('Avatar', $assoc->getAssocName());

        $options = array('className' => 'Avatar');
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'whatever', $options, new User);
        $this->assertEquals('whatever', $assoc->getAssocName());
    }

    // the class for the primary object
    public function testGetClass()
    {
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', array(), new User);
        $this->assertEquals('User', $assoc->getClass());
    }

    // the model object for the primary object
    public function testGetModel()
    {
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', array(), new User);
        $this->assertTrue($assoc->getModel() instanceof User);
    }

    // the table name for the primary object
    public function testTableName()
    {
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', array(), new User);
        $this->assertEquals('users', $assoc->tableName());
    }

    // the value of the primary key
    public function testGetPkValue()
    {
        // join on id
        $md = new User(array('id' => '1'));
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', array(), $md);
        $this->assertEquals('1', $assoc->getPkValue());

        // join on diff col
        $md = new User(array('name' => 'asdf'));
        $options = array('primaryKey' => 'name');
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', $options, $md);
        $this->assertEquals('asdf', $assoc->getPkValue());

    }

    // the class of the associated object
    public function testGetAssocClass()
    {
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', array(), new User);
        $this->assertEquals('Avatar', $assoc->getAssocClass());

        $options = array('className' => 'Avatar');
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'test', $options, new User);
        $this->assertEquals('Avatar', $assoc->getAssocClass());
    }

    // the model object of the associated object
    public function testGetAssocModel()
    {
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', array(), new User);
        $this->assertTrue($assoc->getAssocModel() instanceof Avatar);
    }

    // the table name of the associated object
    public function testGetAssocTable()
    {
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', array(), new User);
        $this->assertEquals('avatars', $assoc->getAssocTable());
    }

    public function testGetPkName()
    {
        // normal assoc
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', array(), new User);
        $this->assertEquals('id', $assoc->getPkName());

        // option passed in
        $options = array('primaryKey' => 'asdf');
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', $options, new User);
        $this->assertEquals('asdf', $assoc->getPkName());
    }

    public function testGetFkName()
    {
        // normal assoc
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', array(), new User);
        $this->assertEquals('user_id', $assoc->getFkName());

        // option passed in
        $options = array('foreignKey' => 'asdf');
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', $options, new User);
        $this->assertEquals('asdf', $assoc->getFkName());
    }

    // test getting an association by name for a model
    public function testGetModelAssociation()
    {
        $user = new User();
        $assoc = $user->reflectOnAssociation('Avatar');
        $this->assertTrue($assoc instanceof Mad_Model_Association_HasOne);
    }


    /*##########################################################################
    # Test dynamic method instantiation
    ##########################################################################*/

    // test get the created methods
    public function testGetMethods()
    {
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', array(), new User);
        $expected = array(
            'avatar'       => 'getObject',
            'avatar='      => 'setObject',
            'buildAvatar'  => 'buildObject',
            'createAvatar' => 'createObject'
        );
        $this->assertEquals($expected, $assoc->getMethods());
    }


    /*##########################################################################
    # Test actual association method execution
    ##########################################################################*/

    // getting associated object
    public function testGetAssociation()
    {
        $this->fixtures('users', 'avatars');

        $user = $this->users('derek');
        $this->assertTrue($user->avatar instanceof Avatar);
        $this->assertEquals('derek.jpg', $user->avatar->filepath);
    }

    // make sure associated object gets cached
    public function testGetAssociationCached()
    {
        $this->useMockLogger();

        $this->fixtures('users', 'avatars');

        $user = User::find($this->users('derek')->id);
        $user->avatar;
        $this->assertLogged('Avatar Load');
        $this->clearLog();

        // make sure it doesn't query qgain
        $user->avatar;
        $this->assertNotLogged('Avatar Load');
    }

    // test setting an association object
    public function testSetAssociation()
    {
        $this->fixtures('users', 'avatars');

        $user = User::find($this->users('derek')->id);

        $avatar = new Avatar(array('filepath' => 'test.gif'));
        $user->avatar = $avatar;

        $this->assertEquals($avatar, $user->avatar);
    }

    // setting object as loaded will prevent it from querying
    public function testSetLoaded()
    {
        $this->useMockLogger();

        $this->fixtures('users', 'avatars');

        $user = User::find($this->users('derek')->id);
        $user->setAssociationLoaded('Avatar');
        $this->clearLog();

        // make sure it doesn't query
        $user->avatar;
        $this->assertNotLogged('Avatar Load');
    }

    // setting object as loaded will prevent it from querying
    public function testIsLoaded()
    {
        $this->fixtures('users', 'avatars');

        $user = User::find($this->users('derek')->id);

        $this->assertFalse($user->reflectOnAssociation('Avatar')->isLoaded());
        $user->avatar;
        $this->assertTrue($user->reflectOnAssociation('Avatar')->isLoaded());
    }


    /*##########################################################################
    # Dependency options
    ##########################################################################*/

    // make sure icons are deleted when metadata is destroyed
    public function testDependentDestroy()
    {
        $this->fixtures('users', 'avatars');

        $user   = User::find($this->users('derek')->id);
        $avatar = Avatar::find($this->avatars('derek_image')->id);
        $user->destroy();

        // metadata was deleted
        $e = null;
        try {
            $user = User::find($this->users('derek')->id);
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof Mad_Model_Exception_RecordNotFound);

        // associated md icon was also deleted
        $e = null;
        try {
            $avatar = Avatar::find($this->avatars('derek_image')->id);
        } catch (Exception $e) {}
        $this->assertTrue($e instanceof Mad_Model_Exception_RecordNotFound);
    }


    /*##########################################################################
    # Saving associations
    ##########################################################################*/

    // test hasOne: test saving an association object
    public function testSetAssociationNewObject()
    {
        $this->fixtures('users', 'avatars');

        $user   = new User(array('name' => 'Foo Name', 'company_id' => 1));
        $avatar = new Avatar(array('filepath' => 'test.gif'));
        $user->avatar = $avatar;

        $this->assertEquals($avatar, $user->avatar);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user   = User::find('first',   array('conditions' => 'name=:nm'),     array(':nm' => 'Foo Name'));
        $avatar = Avatar::find('first', array('conditions' => 'filepath=:nm'), array(':nm' => 'test.gif'));

        $this->assertTrue($user instanceof User);
        $this->assertTrue($avatar instanceof Avatar);
        $this->assertEquals($user->id, $avatar->user_id);
    }

    // test hasOne: test saving an association object
    public function testSetAssociationNewAssocObject()
    {
        $this->fixtures('users', 'avatars');

        $user   = User::find($this->users('derek')->id);
        $avatar = new Avatar(array('filepath' => 'test.gif'));
        $user->avatar = $avatar;

        $this->assertEquals($avatar, $user->avatar);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user   = User::find($this->users('derek')->id);
        $avatar = Avatar::find('first', array('conditions' => 'filepath=:nm'), array(':nm' => 'test.gif'));
        $old    = Avatar::find('first', array('conditions' => 'id=:id'), array(':id' => $this->avatars('derek_image')->id));

        $this->assertTrue($user instanceof User);
        $this->assertTrue($avatar instanceof Avatar);
        $this->assertEquals($user->id, $avatar->user_id);
        $this->assertNull($old);
    }

    // test hasOne: test saving an association object
    public function testSetAssociationExistingAssocObject()
    {
        $this->fixtures('users', 'avatars');

        $user   = User::find($this->users('derek')->id);
        $avatar = Avatar::find($this->avatars('mike_image')->id);
        $user->avatar = $avatar;

        $this->assertEquals($avatar, $user->avatar);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user   = User::find($this->users('derek')->id);
        $avatar = Avatar::find($this->avatars('mike_image')->id);

        $this->assertTrue($user instanceof User);
        $this->assertTrue($avatar instanceof Avatar);
        $this->assertEquals($user->id, $avatar->user_id);
    }


    /*##########################################################################
    # buildObject associations
    ##########################################################################*/

    // test belongsTo:buildObject
    public function testBuildObject()
    {
        $this->fixtures('users', 'avatars');

        $user   = new User(array('name' => 'Foo Name', 'company_id' => 1));
        $avatar = $user->buildAvatar(array('filepath' => 'test.gif'));
        $this->assertEquals($avatar, $user->avatar);

        // this hasn't saved the associated object yet
        $notExists = Avatar::find('first', array('conditions' => 'filepath=:nm'), array(':nm' => 'test.gif'));
        $this->assertNull($notExists);

        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user   = User::find('first',   array('conditions' => 'name=:nm'),     array(':nm' => 'Foo Name'));
        $avatar = Avatar::find('first', array('conditions' => 'filepath=:nm'), array(':nm' => 'test.gif'));

        $this->assertTrue($user instanceof User);
        $this->assertTrue($avatar instanceof Avatar);
        $this->assertEquals($user->id, $avatar->user_id);
    }

    public function testBuildObjectCanAcceptNoArguments()
    {
        $this->fixtures('articles', 'users');

        $user = User::find($this->users('derek')->id);
        try {
            $avatar = $user->buildAvatar();
        } catch (Exception $e) { $this->fail('Unexepected exception raised'); }
    }

    /*##########################################################################
    # createObject associations
    ##########################################################################*/

    // test belongsTo:buildObject
    public function testCreateAssocObjectExistingObject()
    {
        $this->fixtures('users', 'avatars');

        $user   = User::find($this->users('derek')->id);
        $avatar = $user->createAvatar(array('filepath' => 'test.gif'));
        $this->assertEquals($avatar, $user->avatar);

        // this hasn't saved the associated object yet
        $newAvatar = Avatar::find('first', array('conditions' => 'filepath=:nm'), array(':nm' => 'test.gif'));
        $this->assertTrue($newAvatar instanceof Avatar);
        $this->assertEquals($newAvatar->user_id, $user->id);


        // save, and make sure the association object is created
        $user->save();

        // make sure both were created, and are associated
        $user   = User::find($this->users('derek')->id);
        $avatar = Avatar::find('first', array('conditions' => 'filepath=:nm'), array(':nm' => 'test.gif'));

        $this->assertTrue($user instanceof User);
        $this->assertTrue($avatar instanceof Avatar);
        $this->assertEquals($user->id, $avatar->user_id);
    }

    // test belongsTo:buildObject
    public function testCreateAssocObjectNewObject()
    {
        $this->fixtures('users', 'avatars');

        $user = new User(array('name' => 'Foo Name'));

        // Can't create an object before metadata is saved
        try {
            $avatar = $user->createAvatar(array('filepath' => 'test.gif'));

        } catch (Exception $e) {}
        $this->assertTrue($e instanceof Mad_Model_Association_Exception);
    }

    /*##########################################################################
    ##########################################################################*/
}