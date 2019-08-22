<?php

namespace Creads\Partners;

/**
 * A signed authentication URL following RFC2 specs.
 */
class SignedAuthenticationUrl
{
    public function getSignedUri($baseUri, $accessKey, $secretKey, array $parameters, $expirationTime = '+5 minutes')
    {
        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone('UTC'));
        $expires = $now->modify($expirationTime);
        $iso8601Expires = $expires->format('Ymd\THis\Z');
        $encodedUri = $baseUri . $this->encodePath($parameters);
        $signature = $this->getSignature($secretKey, $iso8601Expires, $encodedUri, $parameters);
        $query = $this->buildQuery($iso8601Expires, $signature, $accessKey, $parameters);

        return $encodedUri . '?' . $query;
    }

    protected function buildQuery(
        string $iso8601Expires,
        string $signature,
        string $accessKey,
        array $parameters
    ) {
        array_walk($parameters, function (&$value, $key) {
            if (in_array($key, ['firstname', 'lastname', 'username', 'organizationName'])) {
                $value = base64_encode($value);
            }
        });

        return http_build_query(array_merge(
            [
                'expires' => $iso8601Expires,
                'signature' => $signature,
                'accessKeyId' => $accessKey,
            ],
            $parameters
        ));
    }

    protected function getSignature($secretKey, $iso8601Expires, $encodedUri, array $parameters = [])
    {
        $dateKey = hash_hmac('sha256', $iso8601Expires, $secretKey);
        $signature = hash_hmac('sha256', $encodedUri, $dateKey);

        return $signature;
    }

    public function validate($secretKey, $signedUri)
    {
        $parsed = parse_url($signedUri);

        if (!isset($parsed['query']) || !isset($parsed['path'])) {
            return false;
        }

        $query = $parsed['query'];
        parse_str($query, $parsedQuery);
        $iso8601Expires = $parsedQuery['expires'];
        $signature = $parsedQuery['signature'];
        $encodedUri = strtok($signedUri, '?');
        $path = $parsed['path'];
        /*
         * $parameters will be empty, unless decodePath is overriden to allow multi-pass
         * encryption based on parameters (and validation).
         */
        $parameters = $this->decodePath($path);
        $controlSignature = $this->getSignature($secretKey, $iso8601Expires, $encodedUri, $parameters);

        return ($controlSignature === $signature);
    }

    protected function encodePath(array $parameters)
    {
        $path = 'signed-auth/rfc2/{userRid}/{email}';
        $parameters['userRid'] = $parameters['userRid'];
        $parameters['email'] = base64_encode($parameters['email']);

        foreach ($parameters as $parameter => $value) {
            $path = preg_replace('/\{' . preg_quote($parameter) . '\}/', $value, $path);
        }

        return $path;
    }

    protected function decodePath($path)
    {
        // Not used.
        return [];
    }
}
