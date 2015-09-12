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

$client = new Client([
    'access_token' => $token
]);
```

Get information about the API:

```php
$response = $client->get('/');
echo json_decode($response->getBody(), true)['version'];
//1.0.0
```

Get information about me:

```php
$response = $client->get('/me');
echo json_decode($response->getBody(), true)['firstname'];
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

When HTTP errors occurs (4xx and 5xx responses) , the library throws a `GuzzleHttp\Exception\ClientException` object:

```php
use GuzzleHttp\Exception\ClientException;

try {
    $client = new Client([
        'access_token' => $token
    ]);
    $response = $client->get('/unknown-url');
    //...
} catch (ClientException $e) {
    if (401 == $e->getResponse()->getStatusCode()) {
        //do something
    }
}
```

``

If you prefer to disable throwing exceptions on an HTTP protocol error:

```php
$client = new Client([
    'access_token' => $token,
    'http_errors' => false
]);
$response = $client->get('/unknown-url');
//...
```

## Using the CLI application