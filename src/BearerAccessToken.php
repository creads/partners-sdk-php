<?php

namespace Creads\Partners;

class BearerAccessToken implements AuthenticationInterface
{
    protected $accessToken;

    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getConfig()
    {
        return [
            'headers' => [
                'Authorization' => 'Bearer '.$this->accessToken,
            ],
        ];
    }
}
