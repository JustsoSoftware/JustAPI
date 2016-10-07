# JustAPI

Simple REST API based on plain PHP.

It works as a front controller to call an appropriate service class for the requested API call.

## Installation

### Composer
  composer require justso/justapi

### git
  git clone git://github.com/JustsoSoftware/JustAPI.git vendor/justso/justapi
  
## Setup

Create a `config.json` file in your root folder containing at least the following attributes:

```
  domain: FQDN of domain.
  packages: List of packages to be used by the PHP autoloader. They should be located directly under 'vendor'.
  services: List of services, having a pattern as key and a service locator as value.
  environments: List of environments your application is going to run in, e.g. 'production' or 'development'.
```

The service locator is either a PHP class name or begins with the keyword 'file:', saying that service calls matching
this pattern are handled by another list of services, located in the 'vendor' folder.
The name after 'file:' is the location of the service.

Each environment you specify, should contain at least the AppRoot which is the local path on the server where your code
is checked out, e.g. '/var/www/'. Additionally, it should specify the URLs for the application and optionally the API.

Here is a complete example for such a config.json file:

```
{
    "domain": "my-project.de",
    "packages": {
        "MyPackage": "/path/to/my/package"
    },
    "environments": {
        "production": {
            "approot": "/var/www/prod",
            "appurl":  "https://my-project.de",
            "apiurl":  "https://api.my-project.de",
            "apipprefix": "/"
        },
        "integration": {
            "approot": "/var/www/test",
            "appurl":  "https://test.my-project.de",
            "apiurl":  "https://test.my-project.de/api",
            "apipprefix": "/api/"
        },
        "autotest": {
            "approot": "/var/lib/jenkins/jobs/my-project/workspace",
            "appurl":  "http://localhost/my-project",
            "apiurl":  "http://localhost/my-project/api"
        },
        "development": {
            "approot": "/var/www/my-project",
            "appurl":  "http://local.my-project.de",
            "apiurl":  "http://local.my-project.de/api"
        }
    },
    "services": {
        "/api/myService":      "\\MyPackage\\MyService",
        "/api/otherService/*": "\\OtherPackage\\OtherService"
    }
}
```

If using Apache, you should configure it to have

```
Alias /api /path/to/my/project/vendor/justso/justapi/FrontController.php
```

In NginX, you could work with rewriting:

```
location /api/ {
    rewrite ^.*$ vendor/justso/justapi/FrontController.php;
}
```

This should invoke the front controller whenever a service is called on the sub directory '/api'.
It is possible to use JustAPI in a subdomain, e.g. 'api.example.com' as well.

## Autoloader

JustAPI contains a PSR-0 compliant autoloader. It is recommended to use it for your custom classes as well.
You only have to specify them in your config.json file in the 'packages' list with its path.

## SystemEnvironment

A key element of all classes in JustAPI is `SystemEnvironment` class and its `SystemEnvironmentInterface`. It is the
connection to all operating system functions, contains information about the installation, given parameters and the
file system.
 
## Dependency Injection Container

The package contains a simple Dependency Injection Container which can be configured in `/conf/dependencies.php` file.
It should contain only a `return` statement with a list of definitions with an identifier each. It is a good practice
to use the class name for the definition as the identifier, though it is possible to use other names.

If you use this convention and simply want to create new objects, then no definition is needed for that. You can
instantiate new objects then by calling `$object = $dic->get('\MyNameSpace\MyClassName');`.

It is possible to give an array of parameters to the `get()` call as a second parameter. If you don't specify any
parameters, at least the SystemEnvironment of the DependencyContainer is used as a parameter to the constructor.

If you want a class to be a singleton, place an entry in your `dependencies.php` file using the `singleton()` function:

```
return [
    '\MYNameSpace\MyClassName' => $this->singleton(MyNameSpace\MyClassName::class, [$this->env, 123])
];
```

This makes sure that every call to `get()` will return the same object. This object is instantiated the first time
`get()` is called for that object with the SystemEnvironment and a scalar value of `123` as constructor parameters.

## Specifying controller classes

To actually handle API requests, you must implement classes and derive from RestService to handle these requests and
 specify them in config.json. So, if your service class 'My\Cool\RestService' should handle requests on
 '/api/my/service', your 'services' entry should look like this:

```
"my/service": "My\\Cool\\Service"
```

If your class should handle calls like '/api/my/service/77' as well, the entry may contain a wildcard (`*`):

```
"my/service/*": "My\\Cool\\Service"
```

All requests matching the pattern are given to the service class then. It is possible to use wildcards in the middle
 of a service specification as well, e.g.

```
"my/service/*/subservice": "My\\Cool\\Service"
```

The service class can implement one or more of the following methods:

- getAction
- postAction
- putAction
- deleteAction

Output is placed in $this->environment and never be printed or echoed.

## Upgrading from version 2

If you upgrade from version 2, you should change a few things in your config.json file.

- In version 2 you only needed to specify the package name to make the auto loader find your package. But it required your package to be in `/vendor`. This is not required any more, but you need to specify both, the name of the package and it's location as a key/value pair in `packages`
- Routing API pathes is more flexible now and can handle other path prefixes than `/api` as well. But you are required to specify the full path of your service in your service rules.
- Function `DependencyContainerInterface::newInstanceOf()` was removed, so any calls should be replaced by a call to `get()` on the DependencyContainer object. This affects the DependencyContainer itself as well as the SystemEnvironment, which implements this interface.

## Support & More

If you need support, please contact us: http://justso.de
