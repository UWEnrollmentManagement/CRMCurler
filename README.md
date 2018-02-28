# CRM Curler

PHP cURL wrapper for interacting with Microsoft Dynamics CRM. This library is developed by the Department of Enrollment Management at the University of Washington. Support and documentation will not be provided to any person or unit outside of that group, but others may find this code useful as an example.

## Installation

This library is published on packagist. To install using Composer, run `composer require uwdoem/php-curler` from inside your project directory.

## Use

### Creating a Curler instance:
```
use UWDOEM\CRM\Curler\Curler;

$curler = new Curler('https://www.example.edu/crm/api/v8.0/', 'someuser', 'somepassword');
```

### Issuing a GET request. 

Issue a GET request to `'https://www.example.edu/crm/api/v8.0/resources/1/'`. The response body is returned into `$result`.
```
$result = $curler->get('resources/1/');
```

### More Information

The `$curler` object can also issue POST and DELETE requests. You can provide request query variables and request body strings. You can also capture response headers and response codes. More documentation is provided in the code itself.

## Troubleshooting

If you're having difficulty connecting to your CRM server, the `Curler` constructor accepts an optional `$verbose` argument. This will print cURL connection information to the console, ala the `CURLOPT_VERBOSE` option. See the code for more information.
