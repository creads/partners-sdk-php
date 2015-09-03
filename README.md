# partners-api-php

A simple PHP client and CLI for Creads Partners API.

## Installing Partners API PHP

The recommended way to install Guzzle is through
[Composer](http://getcomposer.org).

Install Composer:

```bash
curl -sS https://getcomposer.org/installer | php
```

Run the Composer command to install the latest stable version:

```bash
composer.phar require creads/partners-api
```

## Using the library

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

### Get an OAuth2 token

First you need to get a fresh OAuth2 access token:

...
 
###  How to

Instance a new client with the token:

```php
use Creads\Partners\Client;

$client = new Client($token);
```

Get information about the API:

```php
$root = $client->get('/');
echo $root['version'];
//1.0.0
```

Get information about me:

```php
$me = $client->get('/me');
echo $me['firstname'];
//John
```

Update my firstname:

```php
$client->put('/me', [
    'firstname' => 'John'
]);
```

Create a project:

```php
$client->post('/projects', [
	'title' => '',
	'description' => '',
	'organization' => '',
    'firstname' => 'John',
    'product' => ''
    'price' => ''
]);
``

### Errors and exceptions handling

When HTTP errors occurs, the library throws an `\Creads\Partners\HttpException` object:

```php
use Creads\Partners\HttpException;

try {
	$me = $client->get('/me');
	echo $me['firstname'];
} catch (HttpException) {
    if (401 == $response->getStatusCode()) {
        //do something
    }
}
```

If you prefer the client to do something on any http errors:

```php
$client->error(function(Response $response) {
    if (401 == $response->getStatusCode()) {
        //do something
    }
});
```

> Other non HTTP exceptions will continue to be thrown except if you want to globally catch them with:

```php
use Creads\Partners\RuntimeException;

$client->exception(function(RuntimeException $exception) {
	if ($exception instance)
});
```

## Using the CLI application