# PHP Framework for REST API

Reviewed a number of PHP frameworks. Key selection criteria included:

* Simplicity/Lightweight
* Performance
* Modularity, e.g. support for Dependency Injection
* Plugins for more advanced use cases
* Simple/lightweight database abstraction layer with support for native SQL and parameter binding
* RESTful API as a core use case

Selected http://silex.sensiolabs.org/ because:

* Experienced development team - second generation framework. Created by the developers of the Symfony 2 framework.
* Built-in dependnecy injection
* Uses proven core components from the Symfony 2 project
* Can plugin additional components from Symfony
* Lightweight - include only what is needed
* Supports a number of more advanced use cases with available service providers (plugins)
* Easily generate JSON or XML

# To Install

Dependencies:

* PHP 5.3+
* Database - using MySQL. SQL script in tests/sql. Database configuration in app/bootstrap.php. Using pdo_mysql driver.
* Composer package manager for PHP
* Apache Web Server

You need to make sure that the php.ini file (etc/php.ini on OS X) is configured for PHP and set the document root for the web directory in the Apache config (/etc/apache2/http.conf on OS X); e.g.

    DocumentRoot "/Users/markmo/src/admin-server/web"

    <Directory "/Users/markmo/src/admin-server/web/">
      Options Indexes FollowSymLinks
      AllowOverride All
      Order deny,allow
      Allow from all
      Options -Indexes -ExecCGI
    </Directory>

To install Composer, run the following from the command line:

    curl -s http://getcomposer.org/installer | php

    or (if you get permission denied errors)

    curl -s http://getcomposer.org/installer | sudo php

Note: you may get the warnings on OS X

The detect_unicode setting must be disabled.
Add the following to the end of your `php.ini`:

    detect_unicode = Off

Then install the Silex dependencies under the project dir:

    php composer.phar install

Routes are defined in app/app.php

# Debug PHP using Xdebug and IntelliJ

* Download the Xdebug prebuilt binaries or sources. You will need to have xdebug.so placed in your lib path

* Detailed PhpStorm instructions can be found here: http://confluence.jetbrains.com/display/PhpStorm/Xdebug+Installation+Guide

* Edit php.ini to enable xdebug.so (OS X Example)

    [xdebug]
    zend_extension="/usr/lib/php/extensions/no-debug-non-zts-20090626/xdebug.so"
    xdebug.remote_enable=1
