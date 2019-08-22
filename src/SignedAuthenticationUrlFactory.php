<?php

namespace Creads\Partners;

class SignedAuthenticationUrlFactory
{
    const RFC0_SIGNATURE_PROTOCOL = 0;
    const RFC2_SIGNATURE_PROTOCOL = 2;

    /**
     * Create a signed authentication URL.
     *
     * @param \ArrayAccess $configuration An array of following required parameters:
     *  [
     *      "api_base_uri" => "https://api.creads-partners.com/v1/"
     *      "client_id" => "N_XXX"
     *      "client_secret" => "XXX"
     *  ]
     * @param array $parameters An array of following parameters:
     *  [
     *      "userRid" User remote ID matching the regular expression: [a-zA-Z0-9\-\_]+
     *      "email" User email
     *      "firstname" User firstname
     *      "lastname" User lastname
     *      "username" User nickname
     *      "organizationRid" Organization remote ID matching the regular expression: [a-zA-Z0-9\-\_]+
     *      "organizationName" Organization name
     *  ]
     * @param string|int $protocol
     * @param string $expirationTime
     *
     * @return string
     */
    public static function create(
        \ArrayAccess $configuration,
        array $parameters,
        $protocol = self::RFC2_SIGNATURE_PROTOCOL,
        string $expirationTime = '+5 minutes'
    ) {
        $signedUrl = null;

        if (!isset($configuration['api_base_uri'])) {
            throw new \InvalidArgumentException('Missing "base_uri" parameter in configuration');
        }

        if (!isset($configuration['client_id'])) {
            throw new \InvalidArgumentException('Missing "client_id" parameter in configuration');
        }

        if (!isset($configuration['client_secret'])) {
            throw new \InvalidArgumentException('Missing "client_secret" parameter in configuration');
        }

        switch ($protocol) {
            case self::RFC0_SIGNATURE_PROTOCOL:
                if (!array_key_exists('organizationName', $parameters) || !$parameters['organizationName']) {
                    throw new \InvalidArgumentException('Missing "organizationName" parameter in configuration');
                }
                $signedUrl = new V0SignedAuthenticationUrl();
                break;

            case self::RFC2_SIGNATURE_PROTOCOL:
                if (!array_key_exists('userRid', $parameters) || !$parameters['userRid']) {
                    throw new \InvalidArgumentException('Missing "userRid" parameter in configuration');
                }
                $signedUrl = new SignedAuthenticationUrl();
                break;
        }

        return $signedUrl->getSignedUri(
            $configuration['api_base_uri'],
            $configuration['client_id'],
            $configuration['client_secret'],
            $parameters,
            $expirationTime
        );
    }

    public static function getAvailableProtocols()
    {
        return [
            self::RFC0_SIGNATURE_PROTOCOL,
            self::RFC2_SIGNATURE_PROTOCOL
        ];
    }

    public static function getLogoutUrl(array $configuration, array $parameters = [])
    {
        return $configuration['api_base_uri'].'signed-auth/logout?'.http_build_query($parameters);
    }
}
