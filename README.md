creads/partners-api
-------------------

A simple PHP client and CLI for Creads Partners API.

## Use the library in your project

### Installation

The recommended way to install the library is through
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
<?php 
use Creads\Partners\Client;

$client = new Client([
    'access_token' => $token
]);
```

Get information about the API:

```php
<?php 
$response = $client->get('/');
echo json_decode($response->getBody(), true)['version'];
//1.0.0
```

Get information about me:

```php
<?php 
$response = $client->get('me');
echo json_decode($response->getBody(), true)['firstname'];
//John
```

Update my firstname:

```php
<?php 
$client->put('me', [
    'firstname' => 'John'
]);
```

Delete a comment of mine:

```php
$client->delete('comments/1234567891011');
```

Create a project:

```php
<?php 
$client->post('projects', [
	'title' => '',
	'description' => '',
	'organization' => '',
    'firstname' => 'John',
    'product' => ''
    'price' => ''
]);
```

### Authentication

To provide a token as explained in [Usage](###Usage), you need to ask for one. As described in [OAuth2 Specification](https://tools.ietf.org/html/rfc6749) this is achieved via a `token` endpoint, in our case:

```
curl -X POST 
-F "grant_type=client_credentials"
-F "client_id=your_app_id"
-F "client_secret=your_app_secret"
"https://api.creads-partners.com/oauth2/token"
```

Which will return something like this :

```json
{
  "access_token": "YOUR_ACCESS_TOKEN",
  "expires_in": 3600,
  "token_type": "bearer"
}
```

The `access_token` property is what you need to join to your requests.

Using **PHP** and **an HTTP Client** (Guzzle, for instance), you could wrap **Partners API PHP Client** in order to ensure you have an access token or get one before sending any request and pass it to our Client.

```php
<?php 

use Creads\Partners\Client as PartnersClient;
use GuzzleHttp\Client as GuzzleClient;

$token = null;

function getToken()
    {
        if (!$token) {
            $client = new GuzzleClient(['base_uri' => 'https://api.creads-partners.com/v1/', 'http_errors' => false]);
            $res = $client->request(
                'POST',
                '/oauth2/token',
                [
                    'multipart' => [
                        [
                            'name' => 'client_id',
                            'contents' => 'YOUR_APP_ID',
                        ],
                        [
                            'name' => 'client_secret',
                            'contents' => 'YOUR_APP_SECRET',
                        ],
                        [
                            'name' => 'grant_type',
                            'contents' => 'client_credentials',
                        ],
                    ],
                ]
            );
            if ($res->getStatusCode() > 399) {
                throw new \Exception(sprintf("Couldnt get a token: (%s):\n %s", $res->getStatusCode(), $res->getBody()));
            }
            $body = json_decode($res->getBody(), true);
            if (!isset($body['access_token'])) {
                throw new \Exception('Could not retrieve authorization from Partners.');
            }

            $token = $body['access_token'];
        }

        return $token;
    }


 $config = [
    'access_token' => getToken(),
    'base_uri' => 'https://api.creads-partners.com/v1/',
	];

$partnersClient = new PartnersClient($config);
// Here $partnersClient is ready to be used as explained before !
```

### Errors and exceptions handling

When HTTP errors occurs (4xx and 5xx responses) , the library throws a `GuzzleHttp\Exception\ClientException` object:

```php
<?php 
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

If you prefer to disable throwing exceptions on an HTTP protocol error:

```php
<?php 
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
