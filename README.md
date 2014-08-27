# JustAPI

Simple REST API based on plain PHP.

It works as a front controller to call an appropriate service class for the requested API call.

## Setup

Checkout as vendor/justso/justapi
Create a config.json file containing at least the following attributes:

```
  domain: FQDN of domain.
  packages: List of packages to be used by the PHP autoloader. They should be located directly under 'vendor'.
  services: List of services, having a pattern as key and a service locator as value.
  environments: List of environments your application is going to run in, e.g. 'production' or 'development'.
```

The service locator is either a PHP class name or begins with the keyword 'file:', saying that service calls matching this pattern are handled by another list of services, located in the 'vendor' folder.
The name after 'file:' is the location of the service.

Each environment you specify, should contain at least the AppRoot which is the local path on the server where your code is checked out, e.g. '/var/www/'. Additionally, it should specify the URLs for the application and optionally the API.

Here is a complete example for such a config.json file:

```
{
    "domain": "justtexts.justso.de",
    "languages": [
        "de",
        "en"
    ],
    "packages": [
    ],
    "environments": {
        "production": {
            "approot": "/var/www/prod",
            "appurl":  "https://justtexts.justso.de",
            "apiurl":  "https://justtexts.justso.de/api"
        },
        "integration": {
            "approot": "/var/www/test",
            "appurl":  "https://test.justtexts.justso.de",
            "apiurl":  "https://test.justtexts.justso.de/api"
        },
        "autotest": {
            "approot": "/var/lib/jenkins/jobs/JustTexts/workspace",
            "appurl":  "http://localhost/justtexts",
            "apiurl":  "http://localhost/justtexts/api"
        },
        "development": {
            "approot": "/var/www/justtexts",
            "appurl":  "http://local.justtexts.de",
            "apiurl":  "http://local.justtexts.de/api"
        }
    },
    "services": {
        "justtexts/*": "file:vendor/justso/justtexts/services.json",
        "justgen/*":   "file:vendor/justso/justgen/services.json"
    }
}
```

If using Apache, you should configure it to have

```
Alias /api /path/to/my/project/vendor/justso/justapi/FrontController.php
```

This should invoke the front controller whenever a service is called on the sub directory '/api'.
It is possible to use JustAPI in a subdomain, e.g. 'api.example.com' as well. Apache should then be configured to send all requests to the front controller.

## Autoloader

JustAPI contains an PSR-0 compliant autoloader. It is recommended to use it for your custom classes as well. You only have to specify them in your config.json file in the 'packages' list.

## Specifying controller classes

To actually handle API requests, you must implement classes and derive from RestService to handle these requests and specify them in config.json. So, if your service class 'My\Cool\RestService' should handle requests on '/api/my/service', your 'services' entry should look like this:

```
"/api/my/service": "My\\Cool\\Service"
```

If your class should handle calls like '/api/my/service/77' as well, the entry should contain a wildcard:

```
"/api/my/service/*": "My\\Cool\\Service"
```

All requests matching the pattern are given to the service class then. It is possible to use wildcards in the middle of a service specification as well, e.g.

```
"/api/my/service/*/subservice": "My\\Cool\\Service"
```

The service class can overwrite one or more of the following methods:

- getAction
- postAction
- putAction
- deleteAction

Output is placed in $this->environment and never be printed or echoed.