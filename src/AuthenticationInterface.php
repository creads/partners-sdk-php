<?php

namespace Creads\Partners;

interface AuthenticationInterface
{
    public function __construct($params = []);

    public function getConfig();
}
