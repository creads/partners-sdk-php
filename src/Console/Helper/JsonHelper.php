<?php

namespace Creads\Partners\Console\Helper;

use Symfony\Component\Console\Helper\Helper;

class JsonHelper extends Helper
{
    /**
     * Pretty print JSON data.
     *
     * @link https://github.com/ryanuber/projects/blob/master/PHP/JSON/jsonpp.php
     *
     * @param string|array $json The JSON data, pre-encoded
     * @param string       $tab  The indentation string
     *
     * @return string
     */
    public function format($json, $color = true)
    {
        if (!is_array($json) && !is_object($json)) {
            $json = json_decode($json, true);
        }
        $result = json_encode($json, JSON_PRETTY_PRINT);

        return $result;
    }

    public function getName()
    {
        return 'json';
    }
}
