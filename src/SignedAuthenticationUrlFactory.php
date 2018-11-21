<?php

namespace Creads\Partners;

class SignedAuthenticationUrlFactory
{
    const RFC0_SIGNATURE_PROTOCOL = 0;
    const RFC1_SIGNATURE_PROTOCOL = 1;

    /**
     * Create a signed authentication URL.
     *
     * @param \ArrayAccess $configuration   An array of following required parameters:
     *      [
     *          "api_base_uri" => "https://api.creads-partners.com/v1/"
     *          "client_id" => "N_XXX"
     *          "client_secret" => "XXX"
     *      ]
     * @param string $organizationRid       Remote ID matching the regular expression: [a-zA-Z0-9\-\_]+
     * @param string $organizationName
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     *
     * @return string
     */
    static public function create(
        \ArrayAccess $configuration,
        array $parameters,
        $protocol = self::RFC1_SIGNATURE_PROTOCOL
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

        if (self::RFC0_SIGNATURE_PROTOCOL == $protocol) {
            $signedUrl = new V0SignedAuthenticationUrl();
        } else {
            $signedUrl = new SignedAuthenticationUrl();
        }

        return $signedUrl->getSignedUri(
            $configuration['api_base_uri'],
            $configuration['client_id'],
            $configuration['client_secret'],
            $parameters
        );
    }

    static public function getAvailableProtocols()
    {
        return [
            self::RFC0_SIGNATURE_PROTOCOL,
            self::RFC1_SIGNATURE_PROTOCOL
        ];
    }
}