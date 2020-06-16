<?php

use PHPUnit\Framework\TestCase;
use Creads\Partners\Webhook;

final class WebhookTest extends TestCase
{
    public function testInvalidSignatureIsInvalid()
    {
        $webhook = new Webhook('my_secret');

        $this->assertFalse($webhook->isSignatureValid('wrong_signature', '{}'));
    }

    public function testValidSignatureIsValid()
    {
        $webhook = new Webhook('my_secret');
        $signature = hash_hmac('sha256', '{}', 'my_secret');

        $this->assertTrue($webhook->isSignatureValid($signature, '{}'));
    }

    public function testThrowsIfBodyIsNotAString()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect bodyString given, please provide a json string of the webhook body.');
        (new Webhook('my_secret'))->isSignatureValid('signature', []);
    }
}
