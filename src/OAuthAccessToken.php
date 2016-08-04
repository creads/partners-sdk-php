<?php

namespace Creads\Partners;

use GuzzleHttp\Client as GuzzleClient;

class OAuthAccessToken implements AuthenticationInterface
{
    protected $baseUri = 'https://connect.creads-partners.com/';
    protected $params = [];
    protected $clientId;
    protected $clientSecret;

    public function __construct($clientId, $clientSecret, $params = [])
    {
        if (isset($params['base_uri'])) {
            $this->baseUri = $params['base_uri'];
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->params = array_merge($this->getDefaultParameters(), $params);

        if (isset($params['grant_type'])) {
            if (in_array($params['grant_type'], ['client_credentials', 'password'])) {
                throw new \Exception(sprintf('Unrecognized grant_type: %s', $params['grant_type']));
            }
            if ('password' === $params['grant_type'] && (!isset($params['username']) || !isset($params['password']))) {
                throw new \Exception("'password' grant_type required a username and a password");
            }
        }
    }

    public function getConfig()
    {
        return [
            'headers' => [
                'Authorization' => 'Bearer '.$this->getAccessToken(),
            ],
        ];
    }

    protected function getDefaultParameters()
    {
        return [
            'tokens_dir' => sys_get_temp_dir(),
       ];
    }

    public function getAccessToken($scope = 'base', $noStoring = false)
    {
        if ($noStoring || $this->isTokenExpired()) {
            $multipartBody = [
                [
                    'name' => 'client_id',
                    'contents' => $this->clientId,
                ],
                [
                    'name' => 'client_secret',
                    'contents' => $this->clientSecret,
                ],
                [
                    'name' => 'grant_type',
                    'contents' => isset($this->params['grant_type']) ? $this->params['grant_type'] : 'client_credentials',
                ],
                [
                    'name' => 'scope',
                    'contents' => 'base',
                ],
            ];
            if (isset($this->params['grant_type']) && $this->params['grant_type'] === 'password') {
                $multipartBody[] = [
                    'name' => 'username',
                    'contents' => $this->params['username'],
                ];
                $multipartBody[] = [
                    'name' => 'password',
                    'contents' => $this->params['password'],
                ];
            }
            $client = new GuzzleClient(['base_uri' => $this->baseUri, 'http_errors' => false]);
            $res = $client->request(
                'POST',
                '/oauth2/token',
                [
                    'multipart' => $multipartBody,
                ]
            );
            if ($res->getStatusCode() > 399) {
                throw new \Exception(sprintf("Couldnt get a token: (%s):\n %s", $res->getStatusCode(), $res->getBody()));
            }
            $body = json_decode($res->getBody(), true);
            if (!isset($body['access_token'])) {
                throw new \Exception('Could not retrieve authorization from Partners.');
            }

            $this->storeToken($body);

            return $body['access_token'];
        } else {
            $storedToken = json_decode(file_get_contents($this->getTokenFilePath()), true);

            return $storedToken['access_token'];
        }
    }

    protected function isTokenExpired()
    {
        $stat = @stat($this->getTokenFilePath());
        if (!$stat) {
            return true;
        }
        $expiresAt = json_decode(file_get_contents($this->getTokenFilePath()), true)['expires_at'];
        if (is_int($expiresAt)) {
            return true;
        }
        $now = new \DateTime();

        return $expiresAt <= $now->getTimestamp();
    }

    protected function getTokenFilePath()
    {
        return rtrim($params['tokens_dir'], '/').'/partners_api_token';
    }

    protected function storeToken($body)
    {
        $now = new \DateTime();
        $expiresAt = $now->getTimestamp() + intval($body['expires_in']);
        $body['expires_at'] = $expiresAt;
        file_put_contents($this->getTokenFilePath(), json_encode($body));
    }
}
