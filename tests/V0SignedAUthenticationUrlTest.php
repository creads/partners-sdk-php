<?php

use Creads\Partners\V0SignedAuthenticationUrl;
use PHPUnit\Framework\TestCase;

final class V0SignedAuthenticationUrlTest extends TestCase
{
    //Client APP_ID: 15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0
    //Client SECRET_KEY: 27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk
    //bin/partners signed-auth-url  "My Organization" j.doe@creads.org --protocol=0

    public function testValidate()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/TXkgT3JnYW5pemF0aW9u/ai5kb2VAY3JlYWRzLm9yZw==?expires=20181121T124553Z&signature=d15d9d79c0c978ddda51293cbf229d8b04e9fa44037641cc0b33e31e84c64ed7&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new V0SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertTrue($result);
    }

    public function testValidateWithNoQueryParameters()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/TXkgT3JnYW5pemF0aW9u/ai5kb2VAY3JlYWRzLm9yZw==';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new V0SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

    public function testValidateWitWrongUrl()
    {
        $signedUrl = 'http://127.0.0.1:8000/';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new V0SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

    public function testValidateWithHackedSignature()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/TXkgT3JnYW5pemF0aW9u/ai5kb2VAY3JlYWRzLm9yZw==?expires=20181121T124553Z&signature=e15d9d79c0c978ddda51293cbf229d8b04e9fa44037641cc0b33e31e84c64ed7&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new V0SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }


    public function testValidateWithHackedOrganizationName()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/TXkgT3JnYW5pemF0aW9u/fi5kb2VAY3JlYWRzLm9yZw==?expires=20181121T124553ZZ&signature=d15d9d79c0c978ddda51293cbf229d8b04e9fa44037641cc0b33e31e84c64ed7&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new V0SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

    public function testValidateWithHackedEmail()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/XXkgT3JnYW5pemF0aW9u/ai5kb2VAY3JlYWRzLm9yZw==?expires=20181121T124553ZZ&signature=d15d9d79c0c978ddda51293cbf229d8b04e9fa44037641cc0b33e31e84c64ed7&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new V0SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

    public function testValidateWithHackedExpires()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/TXkgT3JnYW5pemF0aW9u/ai5kb2VAY3JlYWRzLm9yZw==?expires=20191119T184807Z&signature=d15d9d79c0c978ddda51293cbf229d8b04e9fa44037641cc0b33e31e84c64ed7&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new V0SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }
}
