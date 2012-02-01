Support Objects
***************

The framework provides various utility classes to take care of common operations such
as logging, configuration, and debugging. These can be found in
``/vendor/Mad/Support``.

Logging
=======

The framework exposes a logger object to the application. The logger
writes to different logs depending on the environment in which
the application is run:

- Development log data is sent to ``/log/development.log``
- Testing log data is sent to ``/log/test.log``
- Production log data is sent to ``/log/production.log``

The logger is a preconfigured instance of
`Horde_Log <http://www.horde.org/libraries/Horde_Log>`_.
See its documentation for details on its standard log levels, usage, etc.

You can access the logger instance from a controller
through the ``_logger`` property::

    class CustomersController extends ApplicationController
    {
        public function index()
        {
            $this->_logger->info('Informational message');

            $this->_logger->debug('Debug message');
        }
    }

Base Object
===========

When working with objects in PHP, and our framework models in particular,
we want to have consistent interfaces to our objects and promote encapsulation.

PHP provides some interesting challenges to this goal because it provides
properties, methods, and ways of handling missing properties and methods.
How to use all of these features is left to the developer,
and many PHP programs have inconsistent interfaces as a result.

Models in the framework expose their attributes as PHP properties.  If the
database table ``users`` has a column ``name``, then the model
object exposes it as ``$user->name``.  This is always predictable and
models should not have attributes other ways like ``$user->getName()``,
``$user->name()``, etc. for consistency and clarity.

Models extend ``Mad_Model_Base``, which itself extends ``Mad_Support_Object``.
This base object provides features for creating objects with uniform attribute
access.  You can use these features for adding virtual attributes to models or
for creating your own objects.

Here is a simple example of creating an object called User, which extends
the base object but is does not have a corresponding database table::

    class User extends Mad_Support_Object {
      protected $_name;
      protected $_phone;

      public function __construct() {
        $this->attrAccessor('name', 'phone');
      }
    }

    $user = new User;
    $user->name = 'Fred';
    echo $user->name; // Fred

The method ``attrAccessor()`` creates an attribute on the
instance that can be both read and written. Two other variations
exist: ``attrReader()`` creates attributes that can only be
read, and ``attrWriter()`` creates attributes that can only be
written.

The advantages of the above example are not clear until the implementation
of User needs to change.  The attributes promote encapsulation by allowing
the implementation to change while the interface remains the same::

    class User extends Mad_Support_Object {
      protected $_name;
      protected $_phone;

      public function __construct() {
        $this->attrAccessor('name', 'phone');
      }

      public function getName()
      {
        return isset($this->_name) ? $this->_name : 'Anonymous';
      }
    }

    $user = new User;
    echo $user->name; // "Anonymous"

In the example above, calling ``$user->name`` now consults the
new ``getName()`` method.  The implementation has changed
but the interface remains the same (``$user->name``).

Mad_Support_Object works by using the missing property handlers
``__get()`` and ``__set()``. When one of our virtual
attributes is accessed, it first scans for a method like
``getName()`` or ``setName()`` on the object. If not
found, it looks for a protected property like ``_name``. If
that is also not found, it searches for a method called
``_get()`` or ``_set()``. Finally, an exception is thrown if
nothing is available to satisfy the conditions.

You can use Mad_Support_Object to create you own custom objects as
shown above, or to add new virtual attributes to objects that inherit
from Mad_Model_Base.  For more details on the implementation, see
the inline source documentation for Mad_Support_Object.

Array Object
============

The Standard PHP Library (SPL) provides ArrayObject, which
facilitates building objects with an array-like interface.
In PHP and the framework in particular, we often work with associative arrays.
``Mad_Support_ArrayObject`` extends SPL's ArrayObject to provide
additional conveniences for working with associative arrays.

To use it, instantiate a new Mad_Support_ArrayObject::

    // new array
    $a = new Mad_Support_ArrayObject();
    $a['foo'] = 'bar';
    echo $a['foo']; // bar

    // existing array
    $existing = array('foo' => 'bar');
    $a = new Mad_Support_ArrayObject($existing);
    echo $a['foo']; // bar

One advantage of using Mad_Support_ArrayObject is its handling of nonexistant
keys in the array.  In PHP, when an array contains a value that does not exist,
a notice is raised.  This frequently leads to messy ``isset()`` checks with
the ternary operator.  These are tedious, error-prone, and hard to read.  Instead,
Mad_Support_Object just returns NULL when a key does not exist::

    // php array
    $a = array();
    var_dump($a['foo']);  // NULL, but PHP notice raised

    // array object
    $a = new Mad_Support_ArrayObject();
    var_dump($a['foo']);  // NULL, and no PHP notice raised

A similar issue has to do with default values.  When a key does not exist or the
value at that key is NULL, we often want a default value that is not NULL.  This is
done with the ``get()`` method::

    // php array
    $a = array();
    $foo = isset($a['foo']) ? $a['foo'] : 42;

    // array object
    $a = new Mad_Support_ArrayObject();
    $foo = $a->get('foo', 42);

The feature shown very useful when used with GET and POST
parameters, or any array where the keys and values are unreliable.
In fact, the ``$this->params`` object accessible in
controllers is built using Mad_Support_ArrayObject.

Another useful utility method is ``update``, which will update
the array object with the contents of another array in the same way
as ``array_merge()``::

    // php array
    $a = array('foo' => 'bar');
    $b = array('baz' => 'qux');
    $a = array_merge($a, $b);
    var_dump($a); // array('foo' => 'bar', 'baz' => 'qux')

    // array object
    $a = new Mad_Support_ArrayObject(array('foo' => 'bar'));
    $a->update(array('baz' => 'qux'));
    var_dump($a); // array('foo' => 'bar', 'baz' => 'qux')

There are other useful methods available in Mad_Support_ArrayObject
for getting the keys and values, popping off values, clearing the array, and more.

Extension Proxy
===============

One of the problems that hampers the testability of PHP code is the coupling
created by accessing all of the PHP global functions. This happens often
because a large number of useful extensions are accessed only through global
functions. Consider the following code snippet::

    $res = ldap_connect($host, $port);
    if (! $res) {
      // error logging
      return false;
    }

There are two code paths shown above: the connection succeeding, and it
failing. Both of them are very difficult to test because of the coupling to
the global function ``ldap_connect()`` provided by the LDAP extension.

To make it succeed, you’d need an LDAP server. Causing it to fail is easier
but the could take a very long time until the connection timeout occurs. Also,
the code can’t be tested at all without the LDAP extension. All of these
problems are unacceptable.

The solution is to use to the extension through an object instead
of calling the extension function directly. Since most PHP
extensions prefix all of their functions with the name followed by
an underscore, it’s easy to wrap them. This is what
Mad_Support_ExtensionProxy provides.

Our connection example becomes::

    $ldap = new Mad_Support_ExtensionProxy('ldap');

    $res = $ldap->connect($host, $port);
    if (! $res) {
      // error logging
      return false;
    }

The difference in usage is trivial but this version is easily testable. It now
depends only on an ``$ldap`` instance, which the class needing LDAP can receive in
its constructor. To test, now just pass a mock object for ``$ldap``.
