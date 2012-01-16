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

We will talk more about the testing files in the chapter describing the
<a href="6_unit_test.php">Test</a> TODO package.

Tables and Classes
==================

When you create a subclass of Mad_Model you are creating a wrapper for a database
table. The mapping of the table class is determined by specific naming conventions.
The table should be named as the plural and underscored version of the
model class name.

TODO table

We can create a object to access data in this table by
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

Change a column's precision/scale:

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


