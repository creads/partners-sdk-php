<?php

use Creads\Partners\AuthenticationInterface;
use Creads\Partners\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    protected $authentication;

    /**
     * @before
     */
    public function setUpAuthentication()
    {
        $this->authentication = $this->getMockBuilder(AuthenticationInterface::class)->disableOriginalConstructor()->getMock();
        $this->authentication
            ->method('getConfig')
            ->willReturn(['headers' => ['Authorization' => 'Bearer access_token']])
        ;
    }

    public function testConstruct()
    {
        $this->authentication
            ->expects($this->once())
            ->method('getConfig')
        ;

        $client = new Client($this->authentication, []);
    }
}
