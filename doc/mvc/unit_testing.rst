
.. _unittests:

Unit Testing
============

Overview
--------

A good automated testing strategy is crucial to keeping our applications maintainable.
The framework provides a testing structure suite to get you on the right track
setting up tests. Test files are stored under the ``test/`` directory.

.. image:: /images/test_dir_structure.gif

The framework refers to two different groups of tests. Unit Tests for our models,
and Functional Tests for our controller and views. As you create stubs for your
models and controllers using ``script/generate``, the framework will
automatically create corresponding test stubs.

Running Tests
-------------

Installing PHPUnit
^^^^^^^^^^^^^^^^^^

Our testing framework is built on `PHPUnit <http://phpunit.de/>`_ and
this must be installed on your system.  To check if PHPUnit is installed properly,
run the ``phpunit --version`` command::

    $> phpunit --version

    PHPUnit 3.2.0beta9 by Sebastian Bergmann.

If the output does not look similar to the above and indicate version 3.2 or
greater, please see the installation instructions in the
`PHPUnit Manual <http://www.phpunit.de/manual/3.2/en/installation.html>`_.

Running a Single Test
^^^^^^^^^^^^^^^^^^^^^

You can run any single test file in the <em>test/unit/</em> or
``test/functional/`` directories::

    $> cd /your-application/test/unit
    $> phpunit DocumentTest


The above will run only the tests that have been written for the
Document model. It may take a few seconds and should display output
that is similar to::

    PHPUnit 3.2.0beta9 by Sebastian Bergmann.
    ..........

    Time: 16.990113019943
    OK (10 Tests)

    $>

Each dot represents a passed test. If there are any Failures or errors, they will
be represented by ``F`` or ``E`` respectively with a message describing what went wrong::

    PHPUnit 3.2.0beta9 by Sebastian Bergmann.
    F.........

    Time: 11.186962842941
    There was 1 failure:
    1) testFindByPk(DocumentTest)
    expected <1234> but was: <0>
    ../your-application/test/unit/DocumentTest.php:47
    ../vender/Mad/Test/Unit.php:125

    FAILURES!!!
    Tests run: 10, Failures: 1, Errors: 0, Incomplete Tests: 0.

    $>

We are given a wealth of information to fix what went wrong including the name of the
test that failed, the value we expected in our test assertion, and the backtrace of
the error.

Running All Tests
^^^^^^^^^^^^^^^^^

All of the tests for your application are located in the ``test/`` directory
under you application's root directory.  To run all of the tests, change to the
``test/`` directory and run ``AllTests.php``::

    $> cd /your-application/test
    $> phpunit AllTests.php

Running a Test Group
^^^^^^^^^^^^^^^^^^^^

Tests are organized into two groups: unit tests and functional tests.
When you run ``AllTests.php`` as shown above, it will run all tests
in both groups.

It is often convenient to run all of the tests of a particular
group, e.g. just the unit tests or just the functional tests. You
can do this with the ``--group`` switch for PHPUnit::

    $> cd /your-application/test
    $> phpunit --group unit AllTests

Populating the Database
-----------------------

A fixture is the system's state before the test is performed. We set this up by
loading sample data into the database for the test to perform. This sample data is
loaded before the test and then cleared out at the end of the test. This ensures
that no test will have an effect on the operation of a different test.

.. image:: /images/unit_test.gif

YAML Overview
^^^^^^^^^^^^^

`YAML <http://www.yaml.org/>`_ (Rhymes with "camel") is our preferred method
for loading test data. The YAML fixture files can be found in ``test/fixtures``
and should end with a ``.yml`` extension.

YAML is great because it is very readable. YAML is formatted using a few
simple guidelines. Note that spacing is important (2 space indents, no tabs).

A comment::

    # a comment in YAML format

A Mapping is a key associated with a value::

    String:  Any string of characters
    Int:     12
    Boolean: true
    Null:    NULL
    Float:   3.43

A Sequence is a list of items::

    - item 1
    - item 2
    - item 3

An Inline Sequence is a shortcut for writing a sequence when your values
consist of only one word::

    [item1, item2, item3]

A Mapping of a Sequence::

    teardown:
      - do this first
      - do this second
      - do this third

A Mapping of an Inline Sequence::

    requires: [folders, documents]

A Mapping of a Mapping::

    public:
      id:          1
      parent_id:   0
      name:        Documents
      path:       ./Documents

Fixture Files
^^^^^^^^^^^^^

Our fixtures are by convention named after the table in which they are inserted. So in
the simplest form, inserting two records into the folders table would look like the
following and would be in ``test/fixtures/folders.yml``::

    public:
      id:        1
      parent_id: 0
      name:      Documents
      path:      ./Documents

    private:
      id:         2
      parent_id:  1
      name:       Private
      path:       ./Documents/Private

This fixture would load two records into the database, but also make the yml records
accessible within the test itself by their respective names (public/private). It is
important to name each record with a unique name.




