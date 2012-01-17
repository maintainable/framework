Models
******

Overview
========

The ``Mad_Model`` component is an
`object-relational mapping (ORM) <http://en.wikipedia.org/wiki/Object-relational_mapping>`_
layer for the framework. We follow the ActiveRecord pattern where tables map to classes,
rows map to objects, and columns to object attributes.  The implementation is close to
that of Ruby on Rails.

The model layer is where the domain (business) logic of the application lies. This
involves data retrieval, manipulation, and validation.

    `An informal test I like is to imagine adding a radically different layer to an
    application, such as a command-line interface to a Web application. If there's any
    functionality you have to duplicate to do this, that's a sign of where domain logic
    has leaked into the presentation.` -- Martin Fowler

Generating Stubs
================

The framework provides a tool to generate stub files for new Mad_Model, and related classes.
We can use ``script/generate`` script to do this. This script should be run
from the application's root directory as such::

    $> cd /app

    $> php ./script/generate model User

This will create the following 4 file stubs which include the model class, the
unit test stub file, the migration, and the yml fixture file:

- ``/app/app/models/User.php``
- ``/app/test/unit/UserTest.php``
- ``/app/db/migrate/001_create_users.php``
- ``/app/test/fixtures/users.yml``

We will talk more about the testing files in the :ref:`unittests` chapter.


Tables and Classes
==================

When you create a subclass of Mad_Model you are creating a wrapper for a database
table. The mapping of the table class is determined by specific naming conventions.
The table should be named as the plural and underscored version of the
model class name.

+------------+------------+-----------------+-------------------------+-------------------------+
| Table Name | Model Name | Model File      | Test File               | Fixture File            |
+============+============+=================+=========================+=========================+
| users      | User       | models/User.php | test/units/UserTest.php | test/fixtures/users.yml |
+------------+------------+-----------------+-------------------------+-------------------------+

We can create an object to access data in this table by
instantiating a new ``User`` object::

    // wrap the 'users' table by creating a User object</em>
    $user = new User;

Columns and Attributes
======================

Each Model instance corresponds to a row in the database table. The
object's attributes will correspond directly to the database columns, and are
dynamically added using introspection of the database structure::

    // find a user record with the primary key of "1"
    $user = User::find(1);

    // get specific column info
    $name  = $user->name;
    $phone = $user->phone;

    // set attributes and save back to db
    $user->name  = 'Donny';
    $user->phone = '555-1212';
    $user->save();

Migrations
==========

When we generated the User model, the generator also created our
users migration file. All migrations are stored within the
``db/migrate/`` directory of our application, and keep a version
history of database changes within the source tree. You'll see that
the migration file for our ``users`` table has a numbered prefix of
001, which designates it as version 1 of our database history.

This gives us a powerful tool for applying and rolling back any changes we
make to the database. This is especially useful for teams that need to
keep in sync with each other's database changes. Migrations are written in
PHP, which lets you easily make applications that are more platform and
database independent.

In each migration file, we'll see the ``up`` and ``down`` methods.
These will instruct our migration what to do when migrating ``up`` to
revision number 1 of our database, or reverting back ``down`` to
revision number 0::

    class CreateUser extends Mad_Model_Migration_Base
    {
        public function up()
        {
            $t = $this->createTable('users', $options = array());
                $t->column('username',   'string');
                $t->column('company_id', 'integer');
            $t->end();
        }

        public function down()
        {
            $this->dropTable('users');
        }
    }

When we migrate up in this migration, we'll be creating the users table.
When migrating down, well drop the users table again.

We can execute the migration with ``script/task db:migrate``.
Navigate to your application's root directory to run this::

    $> php ./script/task db:migrate
    == 001 CreateUsers: migrating ===========================================
    -- createTable(users)
       -> 1.1460s
    == 001 CreateUsers: migrated (1.1460s) =================================

Running this script will migrate to the newest version of your database
schema, which in our case has successfully updated us to version 1.
It will determine the newest version by scanning the filenames of the
files in ``db/migrate/`` to find the highest sequentially
numbered migration. To instruct the task to migrate to a specific
version, we can add the ``VERSION=`` argument to the
script::

    $> php ./script/task db:migrate VERSION=0
    == 001 CreateUsers: reverting ===========================================
    -- dropTable(users)
       -> 2.0070s
    == 001 CreateUsers: reverted (2.0070s) =================================

Here we have specified in the ``migrate`` command to
revert back to ``VERSION=0``. When executed, the
migration drops the user table that we had specified in the
``down`` method of this migration. The framework keeps track
of the migration version you are on by automatically creating a table
named ``schema_info`` the first time you run a
migration. This table use a single column named
``version`` to remember the version number::

    mysql> use my_app_development;
    Database changed
    mysql> select * from schema_info;
    +---------+
    | version |
    +---------+
    |       0 |
    +---------+

We can run migrations in production mode by adding the
``MAD_ENV=production`` to the list of arguments to
``script/task db:migrate``.

Let's now take a look at all the different operations we can
perform within a migration file.

Create a Table
--------------

Each ``$t->column()`` call within the ``createTable('users')``
block specifies a column for the table we are creating. The first argument
is the column name, and the second is the data type. Since column type
keywords vary across different database platforms, the framework uses a database
independent syntax to specify the type of column we are creating. The
valid types are ``binary``, ``boolean``, ``date``, ``datetime``,
``decimal``, ``float``, ``integer``, ``string``, ``text``, ``time``,
``timestamp``.

The last argument to the column creation method is an associative array of
options for the column. This is where you can specify if this column uses a
null constraint, default value, or character limit. We've taken advantage of
these options to limit our ``password`` column to 40 characters, and
add a default value of 0 to the ``is_admin`` column::

    $t = $this->createTable('user', $options = array());
        $t->column('username',  'string',  array('null' => false));
        $t->column('password',  'string',  array('limit' => 40));
        $t->column('company_id' 'integer');
        $t->column('is_admin',  'boolean', array('default' => '0'));
        $t->column('profile',   'text');

        // magic cols
        $t->column('created_at', 'datetime');
        $t->column('updated_at', 'datetime');
    $t->end();

A primary key column named ``id`` will be automatically created for each table.

There are a couple reserved names for special columns used to store the date and time
of when user record was created or updated. These columns are named
``created_at`` and ``updated_at``. Mad_Model will automatically insert
the current time into these columns when we insert or update user records.
We'll typically add these columns to all tables that have data being modified
by the application.

An optional ``$options`` array can be given as the second argument to ``createTable()``:

- ``primaryKey``: create the primary key (id) for the table (defaults to true)
- ``force``: drop any existing table by the same name (boolean)
- ``temporary``: create a temporary table (boolean)
- ``*``: other options can be added to append to the create statement

Rename a Table
--------------

Rename the table ``users`` to ``clients``::

    $this->renameTable('users', 'clients');

Drop a Table
------------

Drop the ``users`` table::

    $this->dropTable('users');

Add a Column
------------

An a ``fax_number`` column to the ``users`` table::

    $this->addColumn('users', 'fax_number', 'string', array('limit' => 10));

Remove a Column
---------------

Remove the ``fax_number`` column from the ``users`` table::

    $this->removeColumn('users', 'fax_number');

Change Column Default
---------------------

Change the default value of the ``is_admin`` column of the ``users`` table::

    $this->changeColumnDefault('users', 'is_admin', '1');

Change a Column
---------------

Change the type and limit of the ``phone`` column of the ``users`` table::

    $this->changeColumn('users', 'phone', 'integer', array('limit' => '10'));

Change a column's precision/scale::

    $this->changeColumn('users', 'cash_on_hand', 'decimal',
                         array('precision' => '5', 'scale' => '2'));

Rename a Column
---------------

Rename the ``phone`` column to ``phone_number``::

    $this->renameColumn('users', 'phone', 'phone_number');

Add an Index
------------

Add an index on a single column::

    $this->addIndex('users', 'company_id');

Add an index on multiple columns::

    $this->addIndex('users', array('name', 'company_id'));

Add a unique index::

    $this->addIndex('users', 'email', array('unique' => true));

Specify the name of an index instead of using the framework's default::

    $this->addIndex('users', 'is_admin', array('name' => 'admin'));

Remove an Index
---------------

Remove an index on a single column::

    $this->removeIndex('users', array('column' => 'company_id'));

Remove an index on multiple columns::

    $this->removeIndex('users', array('column' => array('name', 'company_id')));

Remove an index by its name::

    $this->removeIndex('users', array('name' => 'admin'));

Executing SQL
-------------

Even though we have methods to cover most operations you'll need to
perform on a table, you can always drop down to SQL to do what you need::

    $this->execute("INSERT INTO users (id, name) VALUES (1, 'Fred')");

CRUD
====

Mad_Model makes it very to perform the four basic operations on database
tables: Create, Read, Update, and Delete. The operations in this section work
work with a ``Folder`` class to describe how to manipulate data in a table
named ``folders``.

Creating New Rows
-----------------

Since tables are represented as classes, and each object represents a row in the
database, it would make sense that we would create a new object to insert a new
record. We have to make sure that we use ``save()`` to insert the record or it only
exists in memory::

    // insert folder by setting properties
    $folder = new Folder;
    $folder->name        = 'My New Folder';
    $folder->description = 'Folder Description';
    $folder->save();

Mad_Model objects also take an array as an optional constructor argument. This can
be used as a shortcut for loading attributes for a new object::

    // set the properties using an attribute array
    $folder = new Folder(array('name'        => 'My New Folder',
                               'description' => 'Folder Description'));
    $folder->save();

You'll notice we didn't pass in the primary key to this object before saving. This
is because the primary key for this particular object is auto-incremented. We can
get the id by referencing it after the object has been saved::

    // save and get the newly inserted id
    $folder->save();
    $newFolderId = $folder->id;

Another way to insert records is using the convenience method ``create()``, which allows
us to insert data without instantiating the object first::

    // create single records
    $folder = Folder::create(array('name'        => 'My New Folder',
                                   'description' => 'Folder Description'));

We can also create multiple objects by passing in an array::

    $folders = Folder::create(array(
                               array('name'        => 'Folder 1',
                                     'description' => 'Folder Description 1'),
                               array('name'        => 'Folder 2',
                                     'description' => 'Folder Description 2')));

Find Existing Rows
------------------

The simplest way of specifying a row in the table is by using its primary key.
Every model supports the ``find()`` method which is very versatile. Rows can be
retrieved using a single primary key, or an array of primary keys::

    // retrieve a single folder by primary key
    $folder = Folder::find(123);

    // retrieve a collection of folders by primary key
    $folders = Folder::find(array(123, 456, 789));

If any of the IDs given do not exist, the find() will throw a
Mad_Model_Exception_RecordNotFound. This is because Model assumes that when
searching by primary keys, that the specific IDs given should be present
in the database (otherwise, where would those IDs come from?).

More often than not you will need more power. The above example just scratches
the surface of ``find()``. Find has a completely different method of working when
you pass it either ``all`` or ``first`` as the first argument::. The ``first`` string
when passed in will restrict the result set to a single record, and the ``all``
string will return an array of Folder objects that match the given conditions::

    // retrieve the first Folder
    $folder = Folder::find('first');

    // retrieve all Folders
    $folders = Folder::find('all');

Finder Options
--------------

The real power of ``find()`` comes in its second argument, which is an array of options that
can be passed in to build the SQL statement. Let's start with the ``conditions`` option to
see how Mad_Model works with SQL::

    // find folders within the parent_id=181 with more than 10 documents
    $folders = Folder::find('all', array('conditions' => 'parent_id = :parent_id AND
                                                          document_count > :count'),
                                   array(':parent_id' => '181',
                                         ':count'     => '10'));

    // loop through the collection
    foreach ($folders as $folder) {
        print $folder->name;
    }


    // get a specific element in the collection<
    $specificFolder = $folders[3];

.. note::

    The third argument to ``find()`` is an array of bind variables. It is
    extremely important to **always bind your variables** to avoid SQL injection attacks.

The result will be a Mad_Model_Collection object which will be conveniently
accessible with array-like syntax. This means you can do a ``foreach()`` over
it or access specific elements. If we were to run the same find using
``first`` instead of ``all``, the result would be a single Folder object.

One thing you'll notice about the example above is that we're not trying to avoid
SQL. The ``conditions`` argument as well as many of the other options of ``find()``
are indeed just SQL. The aim is not to completely replace SQL with an object model but
rather to embrace SQL while reducing the duplication involved in writing it.

The options available as the second argument to ``find()`` are as follows:

- ``select``: retrieve specific columns
- ``from``: specify FROM tables
- ``conditions``: set SQL WHERE conditions
- ``order``: set result ordering
- ``group``: set result grouping
- ``offset``: offset of the result set
- ``limit``: limit of the result set
- ``include``: eager load associated models

TODO finish section

Associations
============

The real fun in Mad_Model comes with the associations.  Mad_Model allows you
to tie model objects together through database foreign-key relationships.

Once we have the correct relationships declared in the ``_initialize``
method of the model, we can refer directly to related objects of that model. If
we were to say that "Folder has many Documents", we could then reference the
documents within a folder model through the relationship::

    // print the name of each document within the folder.
    $folder = Folder::find(123);
    foreach ($folder->documents as $document) {
        print $document->name;
    }

There are four different relationships that can be defined between models:

- ``belongsTo``: specify a one-to-one association
- ``hasOne``: specify a one-to-one association
- ``hasMany``: specify a one-to-many association
- ``hasAndBelongsToMany``: specify a many-to-many association

In all the relationship methods, the first argument is the name of the association to
be added. By default, you will want to make this the Name of the associated class. For
example, a Document "belongsTo" a Folder::

    class Document extends Mad_Model_Base
    {
        public function _initialize()
        {
            $this->belongsTo('Folder')
        }
    }

The plurality of the class name changes with one-to-many and many-to-many relationships
so that it reads in a more natural way. Notice how a Document belongsTo Folder, while a
Folder hasMany Documents::

    class Folder extends Mad_Model_Base
    {
        public function _initialize()
        {
            $this->hasMany('Documents')
        }
    }

While this makes our associations nice and easy to read, the name of the association
is not tied down to the name of the model. This comes in handy if you need multiple
relationships to the same model.

The second argument in all relationship definitions is an array of options to configure
the relationship. If you create a custom name for an association (not based directly on
the name of the associated model), you will have to specify which model class it refers
to using the ``className`` option::

    class Folder extends Mad_Model_Base
    {
        public function _initialize()
        {
            $this->hasMany('Docs', array('className'  => 'Documents'));
        }
    }

We can now refer to this association as ``docs` instead of ``documents``::

    $folder = Folder::find(123);
    foreach ($folder->docs as $doc) {
        print $doc->name;
    }

Each association has specific options, as well as specific properties/methods that
are dynamically added when the association is declared.

Belongs-To
----------

The ``belongsTo()`` method allows us to specify a ``one-to-one`` relationship
with another model. This declaration must be made in the model that contains the
foreign key.

.. image:: /images/belongs_to.gif

Options:

- ``className``: specify the model class of the associated object
- ``foreignKey``: specify the foreign key column name used in the relationship
- ``include``: eager loaded associations to include when this association is called

In this example, Folder belongsTo Document::

    class Document extends Mad_Model_Base
    {
        public function _initialize()
        {
            $this->belongsTo('Folder')
        }
    }

We can now use the relationship referring to the associated object as ``folder``::

    $doc = Document::find(123);
    print $doc->folder->name;

Properties/methods added with ``belongsTo``:

- ``{assocName}``: access associated object
- ``{assocName} =``: assign associated object
- ``build{AssocName}``: assign associated object by building a new one (associated object doesn't save)
- ``create{AssocName}``: assign associated object by creating a new one (saves associated object)

Access the associated object::

    $folder = $document->folder;

Assign the associated object and save it::

    $document->folder = Folder::find(123);
    $document->save();

Build a new object to use in the association and save it::

    $folder = $document->buildFolder(array('name' => 'New Folder'));
    $document->save();

    // build new object to use as association & save new association.
    // This option will automatically save the associated object, but !not!
    // the actual association with the current object until you use save().
    $folder = $document->createFolder(array('name' => 'New Folder'));
    $document->save();

Has-One
-------

The ``hasOne()`` method also allows us to specify a ``one-to-one``
relationship with another model. This declaration is made in the model that
contains the primary key.

.. image:: /images/has_one.gif

Options:

- ``className``: specify the model class of the associated object
- ``foreignKey``: specify the foreign key column name used in the relationship
- ``include``: eager loaded associations to include when this association is called

In this example, User hasOne AvatarImage::

    class User extends Mad_Model_Base
    {
        public function _initialize()
        {
            $this->hasOne('AvatarImage')
        }
    }

We can now use the relationship referring to the associated object as ``avatarImage``::

    $user = User::find(123);
    print $user->avatarImage->name;

Properties/methods added with ``hasOne``:

- ``{assocName}``: access associated object
- ``{assocName} =``: assign associated object
- ``build{AssocName}``: assign associated object by building a new one (associated object doesn't save)
- ``create{AssocName}``: assign associated object by creating a new one (saves associated object)

Access associated object::

    $avatarImage = $user->avatarImage;

Assign associated object and save new association::

    $user->avatarImage = new AvatarImage(array('name' => 'profile.gif'));
    $user->save();

Build new object to use as association & save new object/association::

    $user->buildAvatarImage(array('name' => 'profile.gif'));
    $user->save();

    // build new object to use as association & save new association.
    // This option will automatically save the associated object, but !not!
    // the actual association with the current object until you use save().
    $user->createAvatarImage(array('name' => 'privileged.gif'));
    $user->save();

Has-Many
--------

The ``hasMany()`` method allows us to specify a ``one-to-many``
relationship with another model. This declaration is made in the model that
contains the primary key.

.. image:: /images/has_many.gif

Options:

- ``className``: specify the model class of the associated object
- ``foreignKey``: specify the foreign key column name used in the relationship
- ``conditions``: conditions that the association must meet (WHERE conditions). These must be prefixed with table name.
- ``order``: ordering of the results to bring back (ORDER BY statement). These must be prefixed with table name.

In this example, Folder hasMany Documents::

    class Folder extends Mad_Model_Base
    {
        public function _initialize()
        {
            $this->hasMany('Documents')
        }
    }

We can now use the relationship referring to the associated objects as documents::

    // use the relationship
    $folder = Folder::find(123);
    foreach ($folder->documents as $document) {
        print $document->name;
    }

Properties/methods added with ``hasMany``:

- ``{assocName}s``: access collection of associated objects
- ``{assocName}s =``: assign collection of associated objects
- ``{assocName}Ids``: access array of associated object's primary keys
- ``{assocName}Ids =``: assign array of associated primary keys
- ``{assocName}Count``: count associated objects
- ``add{AssocName}``: add an object to the associated objects
- ``replace{AssocName}s``: replace associated objects with new assignment of objects
- ``delete{AssocName}s``: delete specific associated objects
- ``clear{AssocName}s``: clear all associated objects
- ``find{AssocName}s``: find subset of associated objects
- ``build{AssocName}``: add associated object by building a new one (associated object doesn't save)
- ``create{AssocName}``: add associated object by creating a new one (saves associated object)

Access collection of associated objects::

    $documents = $folder->documents;

Assign array of associated objects and save associations::

    $folder->documents = array(Document::find(123), Document::find(234));
    $folder->save();

Access array of associated object's primary keys::

    $documentIds = $folder->documentIds;

Set associated objects by primary keys::

    $folder->documentIds = array(123, 234);
    $folder->save();

Get the count of associated objects::

    $docCount = $folder->documentCount;

Add an associated object to the collection and save it::

    $folder->addDocument(Document::find(123));
    $folder->save();

Replace the associated collection with the given list. Will only perform update/inserts when necessary::

    $folder->replaceDocuments(array(Document::find(123), Document::find(234)));
    $folder->replaceDocuments(array(123, 234));
    $folder->save();

Delete specific associated objects from the collection::

    $folder->deleteDocuments(array(Document::find(123), Document::find(234)));
    $folder->deleteDocuments(array(123, 234));
    $folder->save();

Clear all associated objects::

    $folder->clearDocuments();
    $folder->save();

Search for a subset of documents within the associated collection::

    $docs = $folder->findDocuments('all', array('conditions' => 'document_type_id = :type'),
                                          array(':type' => 1));

Build new object to add to association collection & save new object/association::

    $document = $folder->buildDocument(array('name' => 'New Document'));
    $document->save();

    // build new object to add to association collection & save new association.
    // This option will automatically save the associated object, but !not!
    // the actual association with the current object until you use save().</em>
    $document = $folder->createDocument(array('name' => 'New Document'));
    $document->save();

TODO has-many-through

Validations
===========

When you are using the Model to insert or modify data in the database, most of
the time you will need to validate data. The framework has a standard
way to do this so that you can easily check the data given by a user and return
a user-friendly message of any changes that need to be made to save the data.

Validation are added to a model using validation in the ``_initialize()`` method.
There are six types of validations supported:

- ``validatesFormatOf``: validate format of attribute values
- ``validatesInclusionOf``: validate that the value falls within a list of acceptable values
- ``validatesLengthOf``: validate length of attribute values
- ``validatesNumericalityOf``: validate that attribute values are numeric
- ``validatesPresenceOf``: validate existence of value for attribute values
- ``validatesUniquenessOf``: validate uniqueness of attribute value

Validation Types
----------------

Format
^^^^^^

``validatesFormatOf`` can ensure that the value is alpha, digit,
alnum, or that the value matches a given regexp pattern::

    protected function _initialize()
    {
        $this->validatesFormatOf('date_value', array('with' => '/\d{4}-\d{2}-\d{2}/'),
                                   'message' => 'has to be formatted (YYYY-MM-DD)');

        $this->validatesFormatOf('number_value', array('on'   => 'update',
                                                       'with' => '[digit]'));
    }

Options:

- ``on``: validate on either save/insert/update (defaults to ``save``)
- ``with``: The ctype/regex to validate against - ``[alpha]``, ``[digit]``, ``[alnum]``, or ``/regex/``
- ``message``: Custom error message (default is: ``is invalid``)

Inclusion
^^^^^^^^^

``validatesInclusionOf`` validates that the value falls within an array of
acceptable values::

    protected function _initialize()
    {
        $this->validatesInclusionOf('answer', array('in' => array('yes', 'no')));
    }

Options:

- ``in``: validate that the submitted value falls within this array of values
- ``on``: validate on either save/insert/update (defaults to ``save``)
- ``allowNull``: Consider null values valid (defaults to ``false``)
- ``strict``: Enforce identity when comparing values
- ``message``: Custom error message (default is: ``is not included in the list``)

TODO lengthOf

Validation Methods
------------------

There are three different methods for validating data:

- ``validate``: executed before all updates/inserts
- ``validateOnCreate``: executed before all inserts
- ``validateOnUpdate``: executed before all updates

When you add one or more of the above methods to your model, it will automatically
be registered to execute before data is saved.  Adding errors from within these methods
is done via the ``errors->add()`` or ``errors->addToBase()`` methods::

    Class Folder extends Mad_Model_Base
    {
        /**
         * This method will execute before any update/insert operation
         * it makes sure that the description is not empty, and that the name
         * isn't changed.
         */
        public function validate()
        {
            // arguments of add() are the attribute name and message
            if (empty($this->description)) {
                $this->errors->add("description", "cannot be blank");
            }

            // we can also add errors not associated with a attributes
            if (empty($this->name)) {
                $this->errors->addToBase('Fix the name!');
            }
        }
    }

Validation Errors
-----------------

When a validation error is encountered during a save operation, a list
of errors is added to the model in the object's ``errors`` property.
The ``save()`` method will return false when errors are encountered.
The ``errors`` property on the object is actually an instance of
``Mad_Model_Errors``, which is an iterable list of errors. To
get an array with the full error messages encountered, we will use the
``$folder->errors->fullMessages()`` method::

    $folder = Folder::find(123);
    $folder->description = '';
    if (!$folder->save()) {
        $errors = $folder->errors->fullMessages();
        foreach ($errors as $error) {
          print "$error\n";
        }
    }

Alternately, we can use exception handling to catch validation errors.
This only works when we use the ``saveEx()`` method to save
our object. It is preferred to not use exception handling when accessing
errors. The ``errors`` attribute mentioned above is more useful when
we are using form helpers to do the work of displaying errors::

    try {
        $folder = Folder::find(123);
        $folder->description = '';
        $folder->saveEx();

    } catch (Mad_Model_Exception_Validation $e) {
        foreach ($e->getMessages() as $message) {
            print $message;
        }
    }

Callbacks
=========

Mad_Model has ways of monitoring and intercepting the execution inserts, updates, and
deletes via the standard Model methods. We can write code that gets invoked at
any significant event in the life cycle of a model object:

- ``beforeValidation``: executed before validation
- ``afterValidation``: executed after validation
- ``beforeSave``: executed before inserts/updates
- ``afterSave``: executed after inserts/updates
- ``beforeCreate``: executed before inserts
- ``afterCreate``: executed after inserts
- ``beforeUpdate``: executed before updates
- ``afterUpdate``: executed after updates
- ``beforeDestroy``: executed before deletes
- ``afterDestroy``: executed after deletes

A common use of callbacks is to perform pre-validation formatting of data::

    public function User extends Mad_Model_Base
    {
        public function beforeValidation()
        {
            if (!strstr($this->url, '://')) {
                $this->url = "http://" . $this->url;
            }
        }
    }

