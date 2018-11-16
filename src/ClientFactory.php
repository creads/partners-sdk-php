<?php

namespace Creads\Partners;

class ClientFactory
{
    static public function create(\ArrayAccess $configuration)
    {
        if (!isset($configuration['access_token'])) {
            throw new \InvalidArgumentException('Missing "access_token" parameter in configuration');
        }

        if (!isset($configuration['api_base_uri'])) {
            throw new \InvalidArgumentException('Missing "api_base_uri" parameter in configuration');
        }

        //@todo create a service
        return new Client(new BearerAccessToken($configuration['access_token']), [
            'base_uri'    => $configuration['api_base_uri'],
            'http_errors' => false,
        ]);
    }
}