creads/partners-api
-------------------

A simple PHP client and CLI for Creads Partners API.

## Use the library in your project

### Installation

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

### Usage

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

First you need to get a fresh OAuth2 access token:

...

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
    if (404 == $e->getResponse()->getStatusCode()) {
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
if (404 == $e->getResponse()->getStatusCode()) {
    //do something
}
```

## Use the CLI application

### Installation

If you don't need to use the library as a dependency but want to interract with Cread Partners API from your CLI.
You can install the binary globally with composer:

    composer global require creads/partners-api:@dev

Then add the bin directory of composer to your PATH in your ~/.bash_profile (or ~/.bashrc) like this:

    export PATH=~/.composer/vendor/bin:$PATH

You can update the application later with:

    composer global update creads/partners-api

### Usage

Get some help:

    bin/partners --help

Log onto the API (needed the first time):

    bin/partners login

Avoid to type your password each time token expires, using "client_credentials" grant type:

    bin/partners login --grant-type=client_credentials

Or if you are not allowed to authenticated with "client_credentials", save your password locally:

    bin/partners login --save-password

Get a resource:

    bin/partners get /

```json
{
    "name": "Creads Partners API",
    "version": "1.0.0-alpha12"
}
```

Including HTTP-headers in the output with `-i`:

    bin/partners get -i /

```sh
200 OK
Cache-Control: no-cache
Content-Type: application/json
Date: Sat, 12 Sep 2015 17:31:58 GMT
Server: nginx/1.6.2
Content-Length: 72
Connection: keep-alive
{
    "name": "Creads Partners API",
    "version": "1.0.0"
}
```

Filtering result thanks to JSON Path (see http://goessner.net/articles/JsonPath).
For instance, get only the version number of the API:

    bin/partners get / -f '$.version'

Or get the organization I am member of:

    bin/partners get /me -f '$.member_of.*.organization'

Create a resource:

...

Update a resource:

...

Update a resource using an editor:

    bin/partners get /me | vim - | bin/partners post /me

Update a resource using *Sublime Text*:

    bin/partners get /me | subl - | bin/partners post /me
