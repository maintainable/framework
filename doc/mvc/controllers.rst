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

Generating Stubs
----------------

Now that we know how URLs are mapped to controllers, we can start creating
our own controller classes.

The framework provides a tool to generate stub files for new controller, and
related classes. This is done using the ``script/generate`` script.
This script should be run from the root directory where the project is located,
which is also known as the PHP constant ``MAD_ROOT``::

    $> cd project_name

    $> php ./script/generate controller SearchController index search

.. note::

  Use ``./script/generate`` with no arguments for help.  The example
  above generates a new controller called ``SearchesController`` with two actions:
  ``index`` and ``search``.

This will create the following file stubs which include the controller class,
template files for the actions, the functional test stub file, and a helper class file:

- ``/app/views/Search/``
- ``/app/views/Search/index.html``
- ``/app/views/Search/search.html``
- ``/app/controllers/SearchController.php``
- ``/app/helpers/SearchHelper.php``
- ``/tests/functional/SearchControllerTest.php``

Action Methods
--------------

When a request is being processed by a controller, it will look for a public method
with the name of the action specified through the routes. This means that any
public method in your controller can be executed as an action.

If you have methods in your controller that are not actions, you should make them
``protected`` or ``private``.  The controller will not treat these methods
as actions::

    class DocumentsController extends ApplicationController
    {
        /**
         * This method CAN be executed as an action
         */
        public function show()
        {
        }

        /**
         * This method CANNOT be executed as an action
         */
        protected function _findDocument()
        {
        }
    }

.. note::

    PHP has a limitation where occasionally a name you would like for
    an action method will conflict with a PHP construct. For example,
    PHP will not allow a method named ``new()``, but this is would
    be useful as an action name. When these conflicts arise, you my
    append ``Action()`` to the name -- ``new()`` would
    become ``newAction()``. Only do this when necessary.

Sending Responses
-----------------

When an action is invoked in response to a request, the action needs to generate
a response back to the user. The most common ways of doing this are to:

- Render a template/view
- Render text
- Send a File/Data
- Redirect the user

Rendering Templates
^^^^^^^^^^^^^^^^^^^

The default operation an action will perform (if not told otherwise) is to render
a template. The controller will look for a template in the views directory that
has the same name as the action::

    class DocumentsController extends ApplicationController
    {
        public function show()
        {
        }
    }

For flexibility, we can also specify any template we want it to
render using ``render()``::

    class DocumentsController extends ApplicationController
    {
        public function update()
        {
            $this->render(array('template' => 'show'));
        }
    }



Rendering Text
^^^^^^^^^^^^^^

Action methods can render text directly without using a template by using the
``renderText()`` method. This is mostly used when sending Javascript
data back during AJAX requests::

    class DocumentsController extends ApplicationController
    {
        public function remoteUpdate()
        {
            $this->render(array('text' => 'var saved = true;'));
        }
    }


We can also tell an action to not render any data at all using ``render()``::

    class DocumentsController extends ApplicationController
    {
        public function doNothing()
        {
            $this->render(array('nothing' => true));
        }
    }

Sending Files/Data
^^^^^^^^^^^^^^^^^^

When you want to send from the filesystem or text as a binary file, you can use
the ``sendFile()`` and ``sendText()`` methods. These methods will
allow you to force a dialog box on the user to download the resource:

- ``sendFile()`` sends the contents of a file to the user
- ``sendData()`` sends a string containing binary data to the client. Typically
  the browser will use a combination of content-type and disposition,
  both set in th options, to determine what to do with this data.

Both ``sendFile`` and ``sendData`` take an array of options as a second argument:

- ``filename``: A suggestion to the browser of default filename
  to use when saving.

- ``type``: the content type, defaulting to 'application/octet-stream'

- ``disposition``: Suggest to the browser that th file should be displayed inline
  (option ``inline``) or downloaded and saved (option ``attachment``, the default)

Send a JPEG and display it inline::

    class DownloadsController extends ApplicationController
    {
        public function sendJPG()
        {
            $this->sendFile('/path/to/filename.jpg', array(
                                        'type'        => 'image/jpeg',
                                        'disposition' => 'inline'));
        }
    }

Send a string containing CSV text as an attachment::

    class DownloadsController extends ApplicationController
    {
        public function sendCSV()
        {
            $csvText = $this->_getCsvText();
            $this->sendData($csvText, array('filename'    => 'ChannelReport.csv',
                                            'type'        => 'application/ms-excel',
                                            'disposition' => 'attachment'));
        }
    }

Redirects
^^^^^^^^^

An action always performs one of two tasks: it either renders or it redirects.

The ``redirectTo()`` method is used to perform all redirects.  A redirect will
typically be another action name but can also be a URL::

    class CustomersController extends ApplicationController
    {
        public function edit()
        {
            // save data here

            $this->redirectTo(array('action' => 'index'));
        }
    }

In the example above, the ``edit()`` action saves the data
and then redirects back to the ``index()`` action.  Since both of
these actions are in the same controller, the controller name is implied.

To redirect to an action in another controller, set the controller name
in the array like ``array('controller' => 'documents', 'action' => 'index')``.

To redirect to another URL, use a string instead of an array.  This can be an
absolute URL such as ``http://example.com`` or a relative one such
as ``/foo/bar``.  Only use these when necessary.  The best practice is to
specify redirects in terms of controllers and actions, not as URL strings.

Controller Environment
----------------------

Initialize
^^^^^^^^^^

After each controller is instantiated, it will execute the code in the
``_initialize()`` method. This allows us to perform code in a single place
for all actions on a given controller::

    class ExploreController extends ApplicationController
    {
        protected $_foo;

        /**
         * Run this code before all action methods
         */
        protected function _initialize()
        {
            $this->_foo = 'bar';
        }
    }

Request
^^^^^^^

All the data that was sent in the HTTP request made by the browser to our
application is stored in our HttpRequest object. This object is available
to the controller using the ``$this->_request`` property.  Here are some of
the more commonly used methods of the request object:

- ``getUri``: Get the requested URI
- ``getMethod``: Get the request method
- ``getRemoteIp``: Get the IP address of the user
- ``isAjax``: Check if this is an Ajax request?
- ``getServer``: Get a ``$_SERVER`` variable
- ``getEnv``: Get an ``$_ENV`` variable

Get the user's ip address from the request::

    $ipAddress = $this->_request->getRemoteIp();

GET/POST/Params/Cookie/Session information is also available through the request,
but our convention for accessing these is through the methods explained in
:ref:`accessing_data`.

Response
^^^^^^^^

The goal of a controller is to generate a response to send back to the
browser.  Most of the time this is done behind the scenes using ``render()``,
``redirectTo()``, and ``sendFile()`` methods.

The response object is available for modification directly in the controller using
the ``$this->_response`` property::

    $this->_response->setHeader('X-Robots-Tag: noindex, nofollow');

.. _accessing_data:

Accessing Data
--------------

Route Params
^^^^^^^^^^^^

Inside controllers, we can access the pieces of URLs that were
configured in :ref:`routing`::

    $this->connect(":controller/:action/:id");

When we match against this route against URL such as ``/documents/show/123``,
The data in the ``id`` portion of the url (``123``) will be accessible through
the controller action using the ``params`` object, which behaves similar to
an array::

    class DocumentsController extends ApplicationController
    {
        public function show()
        {
            $id = $this->params['id'];

            $id = $this->params->get('id', 0); // default to 0
        }
    }

When accessing params inside controllers, the framework exposes objects behave
similarly to an array but overcome some of their headaches.  The most useful
aspect of this is that when a key does not exist, ``$this->params['id']``
will simply return NULL and no PHP notice will be raised.

When NULL is not the best default value, you can give any default value
by using the ``get()`` method on this object such as
``$this->params->get('id', 1)``.  If the 'id' key is not present or the value
at 'id' is NULL, it will be defaulted to 1.

Take a moment to understand the pattern above because you will use it frequently.
The ``params``, ``cookie``, ``flash``, and ``session`` objects all work this way.

GET and POST
^^^^^^^^^^^^

Data that you would normally access from ``$_GET`` and ``$_POST``
is made available through the ``params`` object.

Similar to ``$_GET['sort_by']``::

    $id = $this->params['sort_by'];

Similar to ``$_POST['sort_by']``::

    $id = $this->params['sort_by'];

Similar to ``$_POST['sort_by']``, default to ``asc`` if not set::

    $id = $this->params->get('sort_by', 'asc');

You will access route params as well as GET and POST params all
from the ``params`` object. You will always get the data this
way -- you will never use these superglobals. To remove the
temptation, the superglobals are actually erased.

Cookies
^^^^^^^

Cookie data that you would normally access through the ``$_COOKIE``
superglobal is made available by the ``cookie`` object.  Be very careful
that any data stored this way is not sensitive data, and remember that
it is not trusted input when read back into the application.

Remember our folder id::

    $this->cookie['folder_id'] = 123;

Retrieve the folder id, or null if none::

    $folderId = $this->cookie['folder_id'];

Retrieve the folder id, or 123 if none::

    $folderId = $this->cookie->get('folder_id', 123);

Session
^^^^^^^

Session data that you would normally access through the ``$_SESSION``
superglobal is made available by the ``session`` object.  You can get
and set values in the session.

Remember our folder id::

    $this->session['folder_id'] = 123;

Retrieve the folder id, or null if none::

    $folderId = $this->session['folder_id'];

Retrieve the folder id, or 123 if none::

    $folderId = $this->session->get('folder_id', 123);

Flash
^^^^^

Flash allows us a way to communicate between different actions in a controller
across HTTP requests.  It is a special value in the session that is available
for the next request only.

This is most useful in situations where the user has performed a POST request to
modify some data, and you want to display a message to the user about the
errors/success of the operation on the next request. For example::

User sends request to save changes, setting flash on success::

    public function save()
    {
        try {
            Document::updateAttribute('name', $this->params['name']);
            $this->flash['sucess'] = "Saved changes successfully.";

            $this->redirectTo(array('action' => 'show'));
            return;

        } catch (Mad_Model_Exception_Validation $e) {
            // handle errors
        }

    }

The next request uses the flash to display a message::

    public function show()
    {
        if ($msg = $this->flash['success']) {
            // set variables for view to display the message
        }
    }

Filters
-------

Filters allow us to write code that is executed before or after the action method
that is requested. We define these by making a declaration of the filter in the
``_initialize`` method of the controller. There are two different types of
filters available in the framework:

- ``beforeFilter``
- ``afterFilter``

Before Filter
^^^^^^^^^^^^^

Before filters get executed before the action in the current request. This allows
us an easy way to add custom code that must execute before every action for a
controller. The second argument to ``beforeFilter`` is an array of options:

Before filters get executed before the action in the current request. This allows
us an easy way to add custom code that must execute before every action for a
controller. The second argument to ``beforeFilter`` is an array of options:

- ``only``: Only execute the filter before these methods
- ``except``: Execute the filter before all methods except these

Execute ``_checkAccess()`` before all actions in this controller::

    protected function _initialize()
    {
        $this->beforeFilter('_checkAccess');
    }

Execute ``_checkAccess()`` before all actions except ``index`` and ``search``::

    protected function _initialize()
    {
        $this->beforeFilter('_checkAccess', array('except' =>
                                              array('index', 'search')));
    }

After Filter
^^^^^^^^^^^^

After filters get executed after the action in the current request. This allows
us an easy way to add custom code that must execute after every action for a
controller. The second argument to ``afterFilter`` is an array of options:

 - ``only``: Only execute the filter after these methods
 - ``except`: Execute the filter after all methods except these

Execute ``_logUser()`` after `download`` and ``print`` actions::

    protected function _initialize()
    {
        $this->afterFilter('_logUser', array('only' =>
                                       array('download', 'print')));
    }

