<?php

namespace Creads\Partners;

interface AuthenticationInterface
{
    public function __construct($clientId, $clientSecret, $params = [])

    public function getConfig();
}
