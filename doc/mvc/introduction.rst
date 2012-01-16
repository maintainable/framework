Introduction
************

This document describes the Maintainable PHP Framework. It is a based
around the Model-View-Controller pattern and is modeled after Ruby on
Rails 1.2.  It is compatible with PHP version 5.1.4 and later.

This software is offered under a BSD license.

Git Repository
==============

The Maintainable PHP Framework is hosted at GitHub:

https://github.com/maintainable/framework

The Git repository is the only way to obtain this software.

Directory Structure
===================

All applications built using the Maintainable Framework share the exact same directory
structure.  This keeps projects consistent, allows team members to easily
transition between projects, and allows for tooling that runs under the
assumption of this structure.

.. image:: /images/main_dir_structure.gif

Most of the server-side application development will take place in
the ``app/`` and ``test/`` directories. Client-side CSS,
images, and JavaScript will be in the ``public/`` directory.
Vendor libraries such as the Maintainable framework itself and its
dependencies reside in ``vendor/`` to permit easy upgrading.

Application Code
----------------

The application code resides under ``app/``:

.. image:: /images/app_dir_structure.gif

It is split into three different layers.  Each layer has a directory
under ``/app``: ``models/``, ``views/``, and ``controllers``.
We also have an additional ``helpers/`` directory under here
for view helper methods.

- Models: ``/app/models/Users.php``
- Controllers: ``/app/controllers/UsersController.php``
- Views: ``/app/views/users/show.html``
- Helpers: ``/app/helpers/UsersHelper.php``

Web-accessible
--------------

Images, CSS, and JavaScript are all stored in ``public/``:

.. image:: /images/web_dir_structure.gif

Configuration
-------------

Environments
^^^^^^^^^^^^

The three different runtime environments are:

- ``development``
- ``production``
- ``test``

Every request will include the common ``config/environment.php`` file and then
its respective ``/config/environments/{environment}.php`` file. These files
include constants and configuration used throughout the application.

Normal MVC code that goes in ``app/`` will never need
``require()`` statements as this is done automatically.
Vendor (library) code in the ``vendor/`` directory also does
not need to be explicitly required because it will be autoloaded
by the PEAR convention (which all vendor files must abide).

URL Routes
^^^^^^^^^^

Request Routing is configured in ``/config/routes.php``. This file defines what
code gets run when a particular URL is requested. This is explained in more detail
under <a href="4_controller.php#c4.2">Request Routing</a>. TODO

Vendor Libraries
----------------

All vendor libraries, including the Maintainable framework itself, are
located under ``vendor/``.  The framework does not invent its own
plugin system or other exotic loading techniques.  Libraries must simply
reside in this directory and abide by the PEAR naming conventions.  The
framework libraries are all under ``vendor/Mad/`` and hence the
classes are prefixed ``Mad_`` by this convention.

Naming Conventions
------------------

TODO table

