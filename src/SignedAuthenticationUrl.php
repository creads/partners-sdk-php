<?php

namespace Creads\Partners;

/**
 * A signed authentication URL following RFC2 specs.
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
        $query = $this->buildQuery($iso8601Expires, $signature, $accessKey, $parameters);

        return $encodedUri . '?' . $query;
    }

    protected function buildQuery(
        string $iso8601Expires,
        string $signature,
        string $accessKey,
        array $parameters
    ) {
        return http_build_query([
            'expires' => $iso8601Expires,
            'signature' => $signature,
            'accessKeyId' => $accessKey,
            'firstname' => $parameters['firstname'] ? base64_encode($parameters['firstname']) : null,
            'lastname' => $parameters['lastname'] ? base64_encode($parameters['lastname']) : null,
            'username' => $parameters['username'] ? base64_encode($parameters['username']) : null,
            'organizationRid' => $parameters['organizationRid'],
            'organizationName' => $parameters['organizationName'] ? base64_encode($parameters['organizationName']) : null,
        ]);
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
