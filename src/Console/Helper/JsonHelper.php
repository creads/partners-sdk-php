<?php

namespace Creads\Partners\Console\Helper;

use Symfony\Component\Console\Helper\Helper;

class JsonHelper extends Helper
{
    /**
     * Pretty print JSON data
     *
     * @link https://github.com/ryanuber/projects/blob/master/PHP/JSON/jsonpp.php
     *
     * @param string $json  The JSON data, pre-encoded
     * @param string $tab  The indentation string
     * @return string
     */
    public function format($json, $color = true)
    {

        // $data = json_encode(json_decode($json), JSON_PRETTY_PRINT);

        $tab = '  ';
        $result = '';
        for ($p=$q=$i=0; isset($json[$p]); $p++) {
            $json[$p] == '"' && ($p>0?$json[$p-1]:'') != '\\' && $q=!$q;

            if (!$q && strstr(" \t\n", $json[$p])) {
                continue;
            }

            if (strstr('}]', $json[$p]) && !$q && $i--) {
                strstr('{[', $json[$p-1]) || $result .= "\n".str_repeat($tab, $i);
            }

            $result .= $json[$p];

            if (strstr(',{[', $json[$p]) && !$q) {
                $i += strstr('{[', $json[$p]) === false?0:1;
                strstr('}]', $json[$p+1]) || $result .= "\n".str_repeat($tab, $i);
            }
        }

        return $result;
    }

    public function getName()
    {
        return 'json';
    }
}