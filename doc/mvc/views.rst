Views
*****

Overview
========

In the MVC pattern, presentation logic is strictly separated
from application logic.  In the framework, views are templates
for presentation that are are written in HTML with embedded PHP helpers.

Templates
=========

Templates are stored in ``app/views``.  Each controller has its own
directory for template files based on the name of the controller (eg. ``UsersController``
=> ``app/views/users``). There is also a ``app/views/shared`` directory to store
templates that are shared between different controllers.

Templates are typically HTML with bits of embedded PHP code.  The PHP code can only be used
for presentation logic.  For increased readability we use PHP's
`Alternative Syntax <http://www.php.net/manual/en/control-structures.alternative-syntax.php>`_
for control structures. This applies to all control structures including
``if``, ``else``, ``elseif``, ``while``, ``for``, and ``switch``::

    <? if (! $this->user): ?>
      <div>No User</div>

    <? else: ?>
      <div><?= Welcome, $this->h( $this->user->name ) ?></div>

    <? endif ?>

Instance Variables
------------------

An action and its corresponding view share instance variables.  There is
no need to explicitly assign variables to a view object::

    class UsersController
    {
        public function show()
        {
            $this->user = User::find( $this->params['id'] );
        }
    }

In the example aboce, ``$this->user`` is available in both
the controller and its view.

Accessing instance variables of the view that do not exist will not cause a PHP notice
as they normally would.  Instead, they will simply return NULL.

Short Tag Emulation
-------------------

PHP includes a feature called short_open_tag that allows you to write less verbose
and more readable templates::

    <?php if ($foo): ?>           long tags  (wrong)
    <? if ($foo): ?>              short tags (right)

    <?php echo $this->h($foo) ?>  long tags  (wrong)
    <?= $this->h($foo) ?>         short tags (right)

The short tags are much nicer but are sometimes avoided for portability.
In environments where the short_open_tag feature has been disabled, they can't
normally be used.

The framework includes a special feature that overcomes this limitation.  Short
tags can be used all the time, regardless of the state of ``short_open_tag`` in the
PHP environment.  Short tags are preferred and should always be used.

Array Shorthand
---------------

The view has many helpers available that provide powerful features such as
form generation.  You will use these frequently.  Most of these helpers
have options that are specified as an associative array::

    <? $form = $this->formFor('message', array('url' => array('action' => 'create'))) ?>
          <?= $form->textField('subject', array('class' => 'text')) ?>
          <?= $form->textArea('content', array('class' => 'text', 'rows' => '5')) ?>
    <? $form->end() ?>

Since we use these options so often, views provide a special feature that allows
associative arrays to be written as ``[]`` instead of ``array()``.

Here's the example from above but cleaned up with the ``[]`` notation::

    <? $form = $this->formFor('message', ['url' => ['action' => 'create']]) ?>
          <?= $form->textField('subject', ['class' => 'text']) ?>
          <?= $form->textArea('content', ['class' => 'text', 'rows' => '5']) ?>
    <? $form->end() ?>

Using this notation for associative arrays makes the views easier to type and read.
Always use this when specifying options for helpers.

Escaping Output
---------------

It is your responsibility to always escape your output::

    WRONG:
    <?= $this->user->name ?>

    Right:
    <?= $this->h( $this->user->name ) ?>

.. warning::

    Never send data to the output without escaping it!


Helpers
=======

Views separate the presentation from the controllers and models.  Views are
allowed to have logic, provided that the logic is only for presentation purposes.
This presentation logic is small bits of PHP code embedded in the HTML.

Bits of presentation logic code can be extracted into helper methods.  Once
extracted, a helper method can be called in the view in place of the former code
block.  Extracting presentation logic into helpers is a best practice and
helps views clean and DRY.

Helpers are simply methods of a class.  The framework mixes the helpers into
the view behind the scenes, and makes the appear as methods inside the view.
An example of a helper class with a single ``highlight()`` helper
follows::

    class UsersHelper extends ApplicationHelper
    {
        /**
         * Highlight a phrase within the given text
         * @param   string  $text
         * @param   string  $phrase
         * @return  string
         */
        public function highlight($text, $phrase)
        {
            $escaped = $this->h($text);
            $highlighted = "<strong class=\"highlight\">$escaped</strong>";

            if (empty($phrase) || empty($text)) {
                return $text;
            }
            return preg_replace("/($phrase)/", $highlighted, $text);
        }
    }

Using the helper in a view::

    <div><?= $this->highlight($this->var, 'bob') ?><div>

It is acceptable to put HTML into helper class methods because they exist
to assist with presentation. However, it is not acceptable to put ``print``/``echo``
statements within a helper class.  Helper methods always return a value that is
displayed in the view like ``<?= $this->highlight($text) ?>``.

The name of the helper class above is ``UsersHelper``, so we know by convention that
these methods will be available to the views of UsersController.

Organization
------------

As shown above, helpers are methods that are organized into classes.
A view typically has access to helpers from many sources:

- ``UsersHelper``: Each controller has corresponding views that are
  specific to that controller.  All views of the same controller share
  helpers that are not shared with views of other controllers.  The views of
  ``UsersController`` share ``UsersHelper``.

- ``ApplicationHelper``: By default, the framework will create an
  ``ApplicationHelper`` which other helper classes extend.  For example,
  ``UsersHelper extends ApplicationHelper``.  Due to this inheritence,
  all views of all controllers can access the helpers in ``ApplicationHelper``.

- ``Built-In Helpers``: The framework has a number of built-in helpers
  for tasks such as formatting numbers and dates, building forms,
  generating hyperlinks, and more.  All of the built-in helpers of the
  framework are always available to all views.

The framework will instantiate helper classes automatically and then mix them
together through overloading. Inside a view, helper methods from all of the
sources above be called by simply using ``<?= $this->helperMethod() ?>``.

Built-in Helpers
----------------

Many convenient helper classes are available within the framework itself, and
the list of these will grow as time goes on. These are located in the
``vendor/Mad/View/Helper/`` directory and are always available all the time.

The helpers provided by the Mad_View component are very close to the
helpers provided by Ruby on Rails 1.2.

Layouts
=======

When building our application, most of the time you'll have a common layout between
different pages. The layout being the menu, header, and navigation. The framework
has a way to share this code between different actions so that we only need one
shared layout between similar pages.

Layout templates are stored in the ``app/views/layouts`` directory.

Using Layouts
-------------

Let's take a look at an example layout template::

    <!-- in /app/views/layouts/myLayout.html -->
    <html>
      <head>
        <title><?= $this->h( $this->pageTitle ) ?></title>
      </head>
      <body>
        <?= $this->contentForLayout ?>
      </body>
    </html>

You'll notice the variable ``<?= $this->contentForLayout ?>`` in the template. This
is a special variable that tells us where our action template will be rendered within
the layout code.

We can use this layout by using the controller's ``setLayout()`` method in
``_initialize``::

    class UsersController
    {
        protected function _initialize()
        {
            // add this layout for all actions
            $this->setLayout('myLayout');
        }

        protected function helloWorld()
        {
            $this->pageTitle = "Hello From Users!";
        }
    }

You'll notice we can also set variables in our actions to be available in the
layout template. Now if we were to add a template for our ``helloWorld`` action::

    <!-- in /app/views/users/helloWorld.html -->
    <h1>Hello World</h1>

When the helloWorld action renders, it will first render the action template
(``app/views/users/helloWorld.html``).  Then it will replace our
``$this->contentForLayout`` layout variable layout with this content to
produce our final result::

    <html>
      <head>
        <title>Hello From Users!</title>
      </head>
      <body>
        <h1>Hello World</h1>
      </body>
    </html>

Disabling Layouts
-----------------

There are times when you won't want every action in the controller to use our
layout (especially when using Ajax). It is easy enough to disable layout for a
specific action by adding ``$this->useLayout(false)`` to that method::

    class UsersController
    {
        protected function _initialize()
        {
            // set this layout for all actions
            $this->setLayout('myLayout');
        }

        public function showSpecial()
        {
            // don't use layout here
            $this->useLayout(false);

            $this->render(array('text' => '...'));
        }
    }

Partials
========

Partial templates are snippets of HTML code that are reusable between different
actions. They make it easy to make our templates as DRY as possible. Partial
templates are named with a leading prefix to differentiate them from our normal
templates. An example would be: ``app/views/users/_user.html``.

Render Partial
--------------

Rendering a partial template within another template is done using the helper method:
``render(['partial' => '...'])``. The leading underscore and extension is omitted when
referring to the partial file::

    <div>
    <? foreach ($this->users as $user): ?>
        <?= $this->render(['partial' => 'user']); ?>
    <? endforeach ?>
    </div>

Any instance variables available in the main template are also available
in partial templates.
