.. _controllers:

Controllers
===========

Overview
--------

Controllers are the glue to our application.  The controller system:

- Accepts the incoming HTTP request made by the browser
- Processes the URL using routing to determine which controller/action to use
- Dispatches the request to the correct controller/action
- Sends an HTTP response back to the browser with the result

.. image:: /images/controller.gif

Instead of each page of the application being a separate PHP file in the
document root, we send all requests to a single PHP file.  This file
handles instantiating a controller to process the request
based on the URL string.

This is a flexible way of handling requests because the URL is no longer
tied directly to a PHP file, and can be changed at any time to suit our needs.

.. _routing:

Request Routing
---------------

The first step to understanding controllers is learning how the framework
handles a URL such as ``example.com/customers/show/123`` to
determine which controller and action will process the request.

The process of mapping URLs to controllers and actions is performed internally
by Horde_Routes.  This integration is transparent
to you.  When you get into advanced uses, you may find the
`Horde_Routes documentation <http://dev.horde.org/routes/>`_
helpful for more information about routing.

Configuration
^^^^^^^^^^^^^

If you open up the routes configuration file ``config/routes.php``, you will
see a list of declarations such as::

    $routes->connect(':controller/:action/:id');

Each one of these declarations specifies a route connecting a URL to a controller
and action. The string given acts as a pattern to match against incoming URLs. The
above route would match any incoming URL with 3 parts. The URL
``customers/show/123`` matches the route example:

.. image:: /images/routes_map.gif

When the route matches, it gives us the following parameters::

    $params = array('controller' => 'customers',
                    'action'     => 'show',
                    'id'         => '1');

Using this information, the framework will execute the ``show()`` action
in ``CustomersController``. It will also make the ``id`` parameter
available with the value of ``1``.

Since our URL convention uses underscores, the application will automatically convert
URL-style controller/action strings to their PHP counterparts. It will translate
a URL such as ``/fax_jobs/start_pending``:

- ``fax_jobs`` => ``FaxJobsController``
- ``start_pending`` => ``startPending()``

The application will try to match each route in the order they are defined in
the ``config/routes.php`` file, from the top to the bottom.
It will stop once it finds a matching route, and will throw a
``Mad_Controller_Exception`` if the URL does not match any route.

Route Components
^^^^^^^^^^^^^^^^

The patterns accepted in the route string are composed of route components. Each
component is separated by a forward-slash(``/``), and is matched to one or
more URL components. The components can be 1 of 3 variations:

- ``:name``
- ``*name``
- ``name``

``:name`` sets the parameter ``name`` to the
corresponding value found in the URL::

    $this->connect(":name1/:name2");

    // Match URL: "/foo/bar"
    $params = array('name1' => 'foo',
                    'name2' => 'bar');

``*name`` will match all remaining components in the URL. It will set the parameter
``name`` to the string that makes up the remaining components. Because this pattern
consumes the remainder of the URL, it must appear at the end of the pattern::

    $this->connect(":name1/*name2");

    // Match URL: "/foo/bar/baz"
    $params = array('name1' => 'foo',
                    'name2' => 'bar/baz');


``name`` will match the route exactly to the matching text in the URL. The pattern
``explore/:action/:id``, would only match a URL that starts with the string
``explore``::

    $this->connect("foo/:name1");

    // Does NOT match: /bar/baz
    // Does match: /foo/bar
    $params = array('name1' => 'bar');

Route Options
^^^^^^^^^^^^^

The second argument to ``$route->connect()`` is an array of options. The
options will typically set either:

- The default value of a component
- A requirement the component must pass

The following options will ensure that ``id`` is a digit (numeric) in order for
this route to match.  It will also set the parameter value of ``id = null``
if no ``id`` is given in the URL::

    $this->connect(':controller/:action/:id', array(
                                'defaults'     => array('id' => null),
                                'requirements' => array('id' => '[0-9]+')));

More advanced options are also available, please consult the
`Horde_Routes documentation <http://dev.horde.org/routes/>`_
for information on these.

Route Defaults
^^^^^^^^^^^^^^

You can give any component a default value that will get assigned to it if
that component is empty in the URL.  This can be done in one of two ways:

Set default action as ``index``, the default id as ``null``::

    $this->connect(':controller/:action/:id', array('action' => 'index',
                                                    'id'     => null));

Same defaults as above, using ``defaults`` array::

    $this->connect(':controller/:action/:id', array(
                                    'defaults' => array('action' => 'index',
                                                        'id'     => null)));

Both of these would match the URL ``/explore`` because they specify a default
value for the empty action and id::

    $params = array('controller' => 'explore',
                    'action'     => 'index',
                    'id'         => null);

In the above examples, both of these routes are identical in how they behave.
The first example shows the shorthand of adding a default value. Which
method you choose to use depends on what is required of the route, and what
is the most readable format to maintain.

Route Requirements
^^^^^^^^^^^^^^^^^^

If you give ``requirement`` for a route component, that component in the
URL being matched must satisfy the requirement for the route to match.  A
requirement is a Perl-compatible regular expression without the surrounding
delimiters::

    /*
     * Does NOT match:
     *   "/customers/destroy/123" - action must be either index/search
     *   "/customers/show/abc"    - id must be numeric
     *
     * Does match:
     *   "/explore/search/123"
     */
    $this->connect(':controller/:action/:id', array(
                         'requirements' => array('action' => 'index|show',
                                                 'id'     => '[0-9]+')));

Notice above that the requirements do not have the delimiters
that would be used with a PHP function like ``preg_match()``.  For example,
where ``preg_match`` would use ``/[0-9]+/``, the equivalent
requirement is simply ``[0-9]+``.

Common Pitfalls
^^^^^^^^^^^^^^^

One thing to always remember when writing a new route is that every route must
set a 'controller' param, and an 'action' param. Without these parameters,
the application has no clue where to send the request, and will fail every time.

The route is not required to have the controller/action parameters as
actual components::

    // this is perfectly acceptable. We set controller/action as defaults
    $this->connect('search', array('controller' => 'search',
                                   'action'     => 'display'));

Default Route
^^^^^^^^^^^^^

While we can add all the custom route configuation we need, most of
the time we have no need to. We have a simple default route that
covers 90% of the URLs we will need to use::

    // the implicit default
    $this->connect(':controller/:action/:id', array('id' => '[0-9]+'));

There is one thing special about the ``action`` and ``id`` params used in
any route.  They come will automatic defaults of ``'action' => 'index',
'id' => null``.



