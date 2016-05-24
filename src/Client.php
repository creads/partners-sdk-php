<?php

namespace Creads\Partners;

use GuzzleHttp\Client as GuzzleClient;

/**
 * @todo use a handler to set oauth bearer token
 */
class Client extends GuzzleClient
{
    /**
     * Constructor
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        $config = array_merge($this->getDefaultClientConfig(), $config);

        if (!empty($config['access_token'])) {
            $config['headers'] = [
                'Authorization' => 'Bearer '.$config['access_token'],
            ];
        }
        parent::__construct($config);
    }

    /**
     * Get default configuration to apply the client.
     *
     * @return array
     */
    protected function getDefaultClientConfig()
    {
        return [
            'base_uri' => 'https://api.creads-partners.com/v1',
        ];
    }

    public function put($uri, $body = [], $options = [])
    {
        $requestBody = array_merge($options, ['json' => $body]);

        return parent::request('PUT', $uri, $requestBody);
    }

    public function post($uri, $body = [], $options = [])
    {
        $requestBody = array_merge($options, ['json' => $body]);

        return parent::request('POST', $uri, $requestBody);
    }

    public function get($uri = '')
    {
        $response = parent::get($uri);
        $parsedResponse = json_decode($response->getBody(), true);

        return $parsedResponse;
    }

    // /**
    //  * Returns options to apply every request
    //  * @return array
    //  */
    // protected function getRequestOptions()
    // {
    //     return [
    //         'headers' => [
    //             'Authorization' => 'Bearer ' . $this->getConfig('access_token')
    //         ]
    //     ];
    // }
}
