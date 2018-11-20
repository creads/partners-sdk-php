<?php

namespace Creads\Partners;

/**
 * A signed authentication URL following RFC0 specs (for backward compatibility).
 */
class V0SignedAuthenticationUrl extends SignedAuthenticationUrl
{
    public function getSignature($secretKey, $iso8601Expires, $encodedUri, array $parameters = [])
    {
        $dateKey = hash_hmac('sha256', $iso8601Expires, $secretKey);
        $organizationKey = hash_hmac('sha256', $parameters['organizationName'], $dateKey);
        $emailKey = hash_hmac('sha256', $parameters['email'], $organizationKey);
        $signature = hash_hmac('sha256', $encodedUri, $emailKey);

        return $signature;
    }

    protected function getRoute()
    {
        return 'signed-auth/{organizationName}/{email}';
    }

    protected function encodePath(array $parameters)
    {
        $path = $this->getRoute();

        $parameters['organizationName'] = base64_encode($parameters['organizationName']);
        $parameters['email'] = base64_encode($parameters['email']);
        foreach ($parameters as $parameter => $value) {
            $path = preg_replace('/\{' . preg_quote($parameter) . '\}/', $value, $path);
        }

        return $path;
    }

    protected function decodePath($path)
    {
        $template = $this->getRoute();

        $pattern = preg_quote($template, '/');
        $pattern = '/' . preg_replace('/\\\{([a-zA-Z]+)\\\}/', '(?<$1>[^\/]+)', $pattern) . '$/';

        if (preg_match($pattern, $path, $matches)) {
            foreach ($matches as $key => $value) {
                if (is_int($key)) {
                    unset($matches[$key]);
                } else {
                    $matches[$key] = base64_decode($value);
                }

            }
        };

        return $matches;
    }
}