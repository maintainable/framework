<?php
/**
 * @category   Mad
 * @package    Mad_Model
 * @subpackage UnitTests
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
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
 * @copyright  (c) 2007-2009 Maintainable Software, LLC
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
class Mad_Model_Association_BaseTest extends Mad_Test_Unit
{
    /*##########################################################################
    # Test constructing
    ##########################################################################*/

    // make sure this can't be directly instantiated
    public function testSingleton()
    {
        // test for private constructor
        $ref = new ReflectionClass('Mad_Model_Association_Base');
        $this->assertFalse($ref->isInstantiable());
    }

    // test creating a belongsTo
    public function testFactoryBelongsTo()
    {
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', array(), new Article);
        $this->assertInstanceOf('Mad_Model_Association_BelongsTo', $assoc);
    }

    // test creating a hasOne
    public function testFactoryhasOne()
    {
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', array(), new User);
        $this->assertInstanceOf('Mad_Model_Association_HasOne', $assoc);
    }

    // test creating a hasMany
    public function testFactoryHasMany()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', array(), new User);
        $this->assertInstanceOf('Mad_Model_Association_HasMany', $assoc);
    }

    // test creating a hasMany :through
    public function testFactoryHasManyThrough()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), new Article);
        $this->assertInstanceOf('Mad_Model_Association_HasManyThrough', $assoc);
    }

    // test creating a hasAndBelongsToMany
    public function testFactoryMasAndBelongsToMany()
    {
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertInstanceOf('Mad_Model_Association_HasAndBelongsToMany', $assoc);
    }


    /*##########################################################################
    # Test getters
    ##########################################################################*/

    public function testGetMacroBelongsTo()
    {
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', array(), new Article);
        $this->assertEquals('belongsTo', $assoc->getMacro());
    }

    public function testGetMacroHasOne()
    {
        $assoc = Mad_Model_Association_Base::factory('hasOne', 'Avatar', array(), new User);
        $this->assertEquals('hasOne', $assoc->getMacro());
    }

    public function testGetMacroHasMany()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Articles', array(), new User);
        $this->assertEquals('hasMany', $assoc->getMacro());
    }

    public function testGetMacroHasManyThrough()
    {
        $assoc = Mad_Model_Association_Base::factory('hasMany', 'Tags', array('through' => 'Taggings'), new Article);
        $this->assertEquals('hasManyThrough', $assoc->getMacro());
    }

    public function testGetMacroHasAndBelongsToMany()
    {
        $assoc = Mad_Model_Association_Base::factory('hasAndBelongsToMany', 'Categories', array(), new Article);
        $this->assertEquals('hasAndBelongsToMany', $assoc->getMacro());
    }

    // test getting the association name
    public function testGetAssocName()
    {
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', array(), new Article);
        $this->assertEquals('User', $assoc->getAssocName());

        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'Test', array(), new Article);
        $this->assertEquals('Test', $assoc->getAssocName());

        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'Users', array(), new Article);
        $this->assertEquals('Users', $assoc->getAssocName());
    }

    // test getting the option values
    public function testOptions()
    {
        $options = array('className' => 'User');
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'Test', $options, new Article);

        $expected = array('className'  => 'User', 'foreignKey' => null,
                          'primaryKey' => null,   'include'    => null);
        sort($expected);

        $options = $assoc->getOptions();
        sort($options);
        $this->assertEquals($expected, $options);
    }

    // test giving an invalid option
    public function testInvalidOption()
    {
        try {
            $options = array('invalid' => 'Test');
            $assoc = Mad_Model_Association_Base::factory('belongsTo', 'Test', $options, new Article);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertRegExp('/unknown key/i', $e->getMessage());
        }
    }

    // test getting the model object
    public function testGetModel()
    {
        $article = new Article();

        $options = array('className' => 'User');
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'Test', $options, $article);

        $this->assertEquals($article, $assoc->getModel());
    }

    /*##########################################################################
    # Test namespaces
    ##########################################################################*/

    public function testNamespacedHasManyAssociation()
    {
        $this->setFixtureClass(array('fax_jobs'       => 'Fax_Job', 
                                     'fax_recipients' => 'Fax_Recipient'));
        $this->fixtures('fax_jobs', 'fax_recipients');

        $job = Fax_Job::find($this->fax_jobs('fax_job_1')->id);
        $recipients = $job->fax_recipients;
        $this->assertInstanceOf('Mad_Model_Collection', $recipients);
        $this->assertInstanceOf('Fax_Recipient',        $recipients[0]);

        $recipient = Fax_Recipient::find($this->fax_recipients('fax_recipient_1')->id);
        $this->assertInstanceOf('Fax_Job', $recipient->fax_job);
    }


    /*##########################################################################
    # Test constructing
    ##########################################################################*/

    // test changed property
    public function testSetChanged()
    {
        $options = array();
        $assoc = Mad_Model_Association_Base::factory('belongsTo', 'User', $options, new Article);

        $assoc->setChanged(true);
        $this->assertTrue($assoc->isChanged());

        $assoc->setChanged(false);
        $this->assertFalse($assoc->isChanged());
    }


    /*##########################################################################
    # Test eager loading
    ##########################################################################*/

    // find with string
    public function testFindIncludeA()
    {
        $this->useMockLogger();

        $this->fixtures('users', 'articles', 'comments', 
                        'categories', 'articles_categories');
        // initial query ran
        $user = User::find('first', array('include' => 'Articles'));
        $this->assertLogged('User Load');
        $this->clearLog();

        // no additional query is run for association
        $user->articles;
        $this->assertNotLogged('User Load');
    }

    // find with single element array
    public function testFindIncludeB()
    {
        $this->useMockLogger();

        $this->fixtures('users', 'articles', 'comments', 
                        'categories', 'articles_categories');

        // initial query ran
        $user = User::find('first', array('include' => array('Articles')));
        $this->assertLogged('User Load');
        $this->clearLog();

        // no additional query is run for association
        $user->articles;
        $this->assertNotLogged('User Load');
    }

    // find multiple associations
    public function testFindIncludeC()
    {
        $this->useMockLogger();

        $this->fixtures('users', 'articles', 'comments', 
                        'categories', 'articles_categories');

        // initial query ran
        $user = User::find('first', array('include' => array('Articles', 
                                                             'Comments')));
        $this->assertLogged('User Load');
        $this->clearLog();

        // no additional query is run for associations
        $user->articles;
        $this->assertNotLogged('Load');

        $user->comments;
        $this->assertNotLogged('Load');
    }

    // find with associative array
    public function testFindIncludeD()
    {
        $this->useMockLogger();

        $this->fixtures('users', 'articles', 'comments', 
                        'categories', 'articles_categories');

        // initial query ran
        $user = User::find('first', array('include' => array('Articles' =>  
                                                               'Comments')));
        $this->assertLogged('User Load');
        $this->clearLog();

        // no additional query is run for associations
        $user->articles;
        $this->assertNotLogged('Load');

        $user->articles[0]->comments;
        $this->assertNotLogged('Load');
    }

    // find with associative array that references an array
    public function testFindIncludeE()
    {
        $this->useMockLogger();

        $this->fixtures('users', 'articles', 'comments', 
                        'categories', 'articles_categories');

        // initial query ran
        $user = User::find('first', array('include' => array('Articles' =>  
                                                               array('Comments', 'Tags'))
                                         ));
        $this->assertLogged('User Load');
        $this->clearLog();

        // no additional query is run for associations
        $user->articles;
        $this->assertNotLogged('Load');

        $user->articles[0]->comments;
        $this->assertNotLogged('Load');

        $user->articles[0]->tags;
        $this->assertNotLogged('Load');
    }

    // find with mix of normal/associative array
    public function testFindIncludeF()
    {
        $this->useMockLogger();

        $this->fixtures('users', 'articles', 'comments', 
                        'categories', 'articles_categories');

        // initial query ran
        $user = User::find('first', array('include' => array('Comments', 
                                                             'Articles' => 'Tags')
                                         ));
        $this->assertLogged('User Load');
        $this->clearLog();

        // no additional query is run for associations
        $user->comments;
        $this->assertNotLogged('Load');

        $user->articles;
        $this->assertNotLogged('Load');

        $user->articles[0]->tags;
        $this->assertNotLogged('Load');
    }

    // find with normal/associative array that references multi-array
    public function testFindIncludeG()
    {
        $this->useMockLogger();

        $this->fixtures('users', 'articles', 'comments', 
                        'categories', 'articles_categories');

        // initial query ran
        $user = User::find('first', array('include' => array('Comments', 
                                                             'Articles' => 
                                                             array('Comments', 'Tags'))
                                         ));
        $this->assertLogged('User Load');
        $this->clearLog();

        // no additional query is run for associations
        $user->comments;
        $this->assertNotLogged('Load');

        $user->articles;
        $this->assertNotLogged('Load');

        $user->articles[0]->tags;
        $this->assertNotLogged('Load');

        $user->articles[0]->comments;
        $this->assertNotLogged('Load');
    }

    // find with normal/associative array that references multi-array
    public function testFindIncludeH()
    {
        $this->useMockLogger();

        $this->fixtures('users', 'articles', 'comments', 
                        'categories', 'articles_categories');

        // initial query ran
        $user = User::find('first', array('include' => array('Articles' => 
                                                       array('Comments' => 'User'))
                                         ));
        $this->assertLogged('User Load');
        $this->clearLog();

        // no additional query is run for associations
        $user->articles;
        $this->assertNotLogged('Load');

        $user->articles[0]->comments;
        $this->assertNotLogged('Load');
        
        $user->articles[0]->comments[0]->user;
        $this->assertNotLogged('Load');
    }

    // deeply nested hierarchy
    public function testFindIncludeNestedHierarchy()
    {
        $this->fixtures('categories');

        // initial query ran
        $cat = Category::find('first', array('include' => array('Categories' => 
                                                          array('Categories' => 
                                                          array('Categories' => 
                                                                'Categories')))));
        $this->assertInstanceOf('Category', $cat);
    }


    /*##########################################################################
    # Eager loading with namespaced models
    ##########################################################################*/

    public function testFindIncludeNamespacedModels()
    {
        $this->setFixtureClass(array('fax_jobs'        => 'Fax_Job', 
                                     'fax_recipients'  => 'Fax_Recipient', 
                                     'fax_attachments' => 'Fax_Attachment'));
        $this->fixtures('articles', 'fax_jobs', 'fax_recipients', 'fax_attachments');

        $jobs = Fax_Job::find('all', array('order'   => 'fax_jobs.id',
                                           'include' => array('Fax_Recipients', 
                                                              'Articles')));

        // 5 total jobs
        $this->assertEquals(2, count($jobs));
        $job = $jobs[0];

        // first job has 2 recipients
        $this->assertEquals(2, count($job->fax_recipients));

        // and two articles
        $this->assertEquals(2, count($job->articles));

        // and two attachments
        $this->assertEquals(2, count($job->fax_attachments));
    }


    /*##########################################################################
    # Test eager limit/offset
    ##########################################################################*/

    public function testFindIncludeLimit()
    {
        $this->fixtures('users', 'articles', 'comments', 
                        'categories', 'articles_categories');

        $users = Article::find('all', array('include' => 'Categories', 
                                            'limit'   => 2));
        $this->assertEquals(2, count($users));
    }

    // find with string
    public function testFindIncludeLimitConditions()
    {
        $this->fixtures('users', 'articles', 'comments', 
                        'categories', 'articles_categories');

        $users = Article::find('all', array('conditions' => 'users.first_name = :name', 
                                            'include' => array('User', 'Categories'), 
                                            'limit'   => 2), 
                                      array(':name' => 'Derek'));
        $this->assertEquals(2, count($users));
    }

    /*##########################################################################
    ##########################################################################*/

}