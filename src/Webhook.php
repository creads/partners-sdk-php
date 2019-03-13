<?php

namespace Creads\Partners;

class Webhook
{
    const SIGNATURE_HEADER_NAME = 'X-Partners-Signature';

    /**
     * The webhook secret to be used to authenticate Webhooks.
     *
     * @var string
     */
    protected $secret;

    /**
     * @param string $secret The webhook secret to be used to authenticate Webhooks
     */
    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    /**
     * Returns whether a webhook signature is valid, based on the request body and the webhook secret.
     *
     * @param string $signature
     * @param string $bodyString
     *
     * @return bool
     */
    public function isSignatureValid($signature, $bodyString)
    {
        if (!is_string($bodyString)) {
            throw new \InvalidArgumentException('Incorrect bodyString given, please provide a json string of the webhook body.');
        }
        $expectedSignature = hash_hmac('sha256', $bodyString, $this->secret);

        return $expectedSignature === $signature;
    }
}
