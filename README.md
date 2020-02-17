RESTful SDK
===============
[![Build Status](https://travis-ci.org/sokil/php-rest.png?branch=master)](https://travis-ci.org/sokil/php-rest)
[![Latest Stable Version](https://poser.pugx.org/sokil/php-rest/v/stable.png)](https://packagist.org/packages/sokil/php-rest)

Framework to build client libraries for interacting with RESTful services. 

* [Installation](#installation)

Installation
------------

You can install library through Composer:
```php
{
    "require": {
        "sokil/php-rest": "dev-master"
    }
}
```

Projects based on this library
------------------------------

* [Distributive Manager API](https://github.com/sokil/php-distmanager-sdk) for [Distributive Manager](https://github.com/sokil/distributiveManager)

Basic concepts
--------------
Client library contains `Request` and `Response` classes for every request. `Request` incapsulates HTTP method, URL and parameters, and allows to get `Response` object, which give access to response data, headers and status. `Factory` allows us to auth on server, and to create and send requests.

Factory
-------

Factory must extend `\Sokil\Rest\Client\Factory`. This class incapsulates auth login and creates requests.
