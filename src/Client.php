<?php

namespace Creads\Partners;

use GuzzleHttp\Client as GuzzleClient;

class Client extends GuzzleClient
{
    /**
     * Constructor
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        $config = array_merge($this->getDefaultClientConfig(), $config);
        parent::__construct($config);
    }

    /**
     * Get default configuration to apply the client
     * @return array
     */
    protected function getDefaultClientConfig()
    {
        return [
            'base_uri' => 'https://api.creads-partners.com/v1'
        ];
    }

    /**
     * Returns options to apply every request
     * @return array
     */
    protected function getRequestOptions()
    {
        return [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getConfig('access_token')
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function get($uri, array $options = [])
    {
        return parent::get($uri, array_merge_recursive($this->getRequestOptions(), $options));
    }
}