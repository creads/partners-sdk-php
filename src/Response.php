<?php

namespace Creads\Partners;

use Creads\Partners\Exception\InvalidDataException;
use GuzzleHttp\Psr7\Response as BaseResponse;

class Response extends BaseResponse
{
    /**
     * Get decode JSON data embed in body
     *
     * @param  boolean $assoc When TRUE, returned objects will be converted into associative arrays.
     * @return array|\StdClass
     */
    public function getData($assoc = true)
    {
        //@todo test Content-Type

        $data = json_decode((string)$this->getBody(), $assoc);
        if (!$data) {
            throw new InvalidDataException('Unable to decode response.');
        }

        return $data;
    }
}