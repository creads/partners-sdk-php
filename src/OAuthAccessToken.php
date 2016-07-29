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
        $this->params = $params;

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

    public function getAccessToken($scope = 'base', $noCache = false)
    {
        if ($noCache || $this->isCacheExpired()) {
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

            $this->writeTokenCache($body);

            return $body['access_token'];
        } else {
            $cachedToken = json_decode(file_get_contents($this->getTokenCacheFilePath()), true);

            return $cachedToken['access_token'];
        }
    }

    protected function isCacheExpired()
    {
        $stat = @stat($this->getTokenCacheFilePath());
        if (!$stat) {
            return true;
        }
        $expiresAt = json_decode(file_get_contents($this->getTokenCacheFilePath()), true)['expires_at'];
        $now = new \DateTime();

        return $expiresAt <= $now->getTimestamp();
    }

    protected function getTokenCacheFilePath()
    {
        if (isset($params['cache_dir'])) {
            return rtrim($params['cache_dir'], '/').'/partners_api_token';
        }

        return rtrim(sys_get_temp_dir(), '/').'/partners_api_token';
    }

    protected function writeTokenCache($body)
    {
        $now = new \DateTime();
        $expiresAt = $now->getTimestamp() + intval($body['expires_in']);
        $body['expires_at'] = $expiresAt;
        file_put_contents($this->getTokenCacheFilePath(), json_encode($body));
    }
}
