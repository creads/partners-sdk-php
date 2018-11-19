<?php

namespace Creads\Partners;

/**
 * A signed authentication URL following RFC1 specs.
 */
class SignedAuthenticationUrl
{
    public function getSignedUri($baseUri, $accessKey, $secretKey, array $parameters)
    {
        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone('UTC'));
        $expires = $now->modify('+5 minutes');
        $iso8601Expires = $expires->format('Ymd\THis\Z');

        $encodedUri = $baseUri . $this->encodePath($parameters);

        $signature = $this->getSignature($secretKey, $iso8601Expires, $encodedUri, $parameters);

        $query = http_build_query([
            'expires' => $iso8601Expires,
            'signature' => $signature,
            'accessKeyId' => $accessKey
        ]);

        return $encodedUri . '?' . $query;
    }

    public function getSignature($secretKey, $iso8601Expires, $encodedUri, array $parameters = [])
    {
        $dateKey = hash_hmac('sha256', $iso8601Expires, $secretKey);
        $signature = hash_hmac('sha256', $encodedUri, $dateKey);

        return $signature;
    }

    public function validate($secretKey, $signedUri)
    {
        $parsed = parse_url($signedUri);

        if (!isset($parsed['query'])) {
            return false;
        }
        if (!isset($parsed['path'])) {
            return false;
        }

        $query = $parsed['query'];


        parse_str($query, $parsedQuery);

        $iso8601Expires = $parsedQuery['expires'];
        $signature = $parsedQuery['signature'];

        $encodedUri = strtok($signedUri, '?');

        $path = $parsed['path'];
        //$parameters will be empty, unless decodePath is overriden to allow multi-pass
        //encryption based on parameters (and validation)
        $parameters = $this->decodePath($path);
        $controlSignature = $this->getSignature($secretKey, $iso8601Expires, $encodedUri, $parameters);

        return ($controlSignature === $signature);
    }

    protected function encodePath(array $parameters)
    {
        $path = 'signed-auth/{organizationRid}/{organizationName}/{email}/{firstname}/{lastname}';

        $parameters['organizationName'] = base64_encode($parameters['organizationName']);
        $parameters['email'] = base64_encode($parameters['email']);
        $parameters['firstname'] = base64_encode($parameters['firstname']);
        $parameters['lastname'] = base64_encode($parameters['lastname']);

        foreach ($parameters as $parameter => $value) {
            $path = preg_replace('/\{' . preg_quote($parameter) . '\}/', $value, $path);
        }

        return $path;
    }

    protected function decodePath($path)
    {
        //not used
        return [];
    }
}