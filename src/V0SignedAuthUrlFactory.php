<?php

namespace Creads\Partners;

class V0SignedAuthUrlFactory
{
    const SIGNED_AUTH_PATH = 'signed-auth/{organizationName}/{email}';

    static public function create(
        \ArrayAccess $configuration,
        $organizationName,
        $email
    ) {

        if (!isset($configuration['api_base_uri'])) {
            throw new \InvalidArgumentException('Missing "base_uri" parameter in configuration');
        }

        if (!isset($configuration['client_id'])) {
            throw new \InvalidArgumentException('Missing "client_id" parameter in configuration');
        }

        if (!isset($configuration['client_secret'])) {
            throw new \InvalidArgumentException('Missing "client_secret" parameter in configuration');
        }

        $url =  $configuration['api_base_uri'] . self::SIGNED_AUTH_PATH;
        $url = str_replace('{organizationName}', base64_encode($organizationName), $url);
        $url = str_replace('{email}', base64_encode($email), $url);

        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone('UTC'));
        $expires = ($now->modify('+5 minutes'))->format('Ymd\THis\Z');

        $dateKey = hash_hmac('sha256', $expires, $configuration['client_secret']);
        $organizationKey = hash_hmac('sha256', $organizationName, $dateKey);
        $emailKey = hash_hmac('sha256', $email, $organizationKey);

        $signature = hash_hmac('sha256', $url, $emailKey);

        $query = http_build_query([
            'expires' => $expires,
            'signature' => $signature,
            'accessKeyId' => $configuration['client_id']
        ]);

        return $url . '?' . $query;
    }
}