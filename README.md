Maintainable PHP Framework
==========================

This is a framework based around the Model-View-Controller pattern
and modeled after Ruby on Rails 1.2.  It is compatible with PHP
version 5.1.4 and later.


Repository Layout
-----------------

The repository shares the same layout as an application built with the
framework.  This is mostly because of historical reasons.


Starting a New Application
--------------------------

You can use `./script/createapp appname` to generate an application
directory in the current directory. Adjust the path to the createapp
script to create the application in a different place.

If you are familiar with Rails, using the framework should be rather
straightfoward at this point.

The framework expects its `index.php` file is located in the `DocumentRoot` of
an Apache `VirtualHost` and that `mod_rewrite` is enabled.  If you aren't familiar
with how to set this up, the framework is probably not for you.  We haven't
made any attempt to support running it out of a subdirectory or on shared hosting.

One noticeable difference from Rails is that files are named with uppercase
as in `PostsController.php` rather than `posts_controller.php`.  This is
largely because our libraries use PEAR-like conventions, where the uppercase
naming style is used.  Browse the repository directories under `app/` to see
the naming of controllers, models, and views.  Similarly, we use camelCase
names for methods (e.g. `respondTo` rather than `respond_to`).

You can generate stubs with `./script/generate`.  You'll probably want to
start with `./script/generate model post`, which will create a `Post` model
file with associated migration and test files.  Use `./script/generate`
with no arguments for help.

Tasks such as `db:migrate` are run as `./script/task db:migrate`.  For a
list of tasks, use `./script/task -T`.  If you copied the `Rakefile` and have
Rake installed, you can call the tasks using the `rake` command.


Packages and Dependencies
-------------------------

Applications built with the framework use class naming conventions similar to
Rails, e.g. classes named `Post` and `PostsController`.  However, the
framework itself is built entirely with PEAR-style naming conventions and
does not pollute the global space.

Classes required by the framework are placed in the `vendor/` directory,
which is a simple PEAR-style directory.  Any framework classes are
prefixed with `Mad_` (Mike and Derek) and placed under `vendor/Mad/`.

There are some libraries from other projects included in the `vendor/`
directory as well that are dependencies.  We have no interest in building
a monolith and try to share our code with other projects as we can.  Most
of the dependencies in `vendor/` started out as `Mad_` classes that found
homes elsewhere.  We hope in the future that the framework will continue
to shrink in this way.


Running Tests
-------------

The repository has the same layout as an application.  The framework tests
are run the same way as an application's tests would be.  Change to the
`test/` directory and run `phpunit AllTests`.

Before you can run the tests, you need to create a database and configure
the connection in `database.yml`.  You also need to build the tests database
using the file `db/tests/madmodel_test.sql`.


State of Development
--------------------

Most of the development was done around the time of Rails 1.2.  As such,
Rails developers will notice many of the important additions since
Rails 2.0 are not implemented.


Contributing Patches
--------------------

The best way to contribute patches is to fork the repository on GitHub and
then send us a pull request with your changes.

It's unlikely that we'll accept patches that deviate from the "Rails way".
For example, a patch that implements support for Smarty templates will not
be accepted.  It's best to make your own fork for those kinds of changes.

If you implement a useful Rails feature that we have not yet implemented,
it's more likely your patch will be accepted.  We will also accept patches
that improve the implementation of existing features.

We will not accept patches unless they use the same coding standards and
include reasonable test coverage.  Please refrain from sending us huge
patches.  Incremental improvements are best.


Developers
----------

* [Mike Naberezny](http://github.com/mnaberez)
* [Derek DeVries](http://github.com/devrieda)


Acknowledgements
----------------

We would like to thank [Chuck Hagenbuch](http://github.com/chuck) of the
[Horde Project](http://horde.org) and [Sebastian Bergmann](http://github.com/sebastianbergmann)
of [PHPUnit](http://phpunit.de) for helping us with maintenance by
integrating some of our code into their projects.

We would also like to thank all the folks who have provided patches
or support in other ways.
