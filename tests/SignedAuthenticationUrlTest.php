<?php

use Creads\Partners\SignedAuthenticationUrl;
use PHPUnit\Framework\TestCase;

final class SignedAuthenticationUrlTest extends TestCase
{

    //Client APP_ID: 15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0
    //Client SECRET_KEY: 27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk
    //bin/partners signed-auth-url  "My Organization" j.doe@creads.org THIS-IS-MY-REMOTE-ID_000 John Doe -v

    public function testValidate()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/THIS-IS-MY-REMOTE-ID_000/TXkgT3JnYW5pemF0aW9u/ai5kb2VAY3JlYWRzLm9yZw==/Sm9obg==/RG9l?expires=20181121T124026Z&signature=a47cd5c88f2d8fb1d4bcdd25397062c7769cc60992de0a34c57f84458fbe980f&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertTrue($result);
    }

    public function testValidateWithNoQueryParameters()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/THIS-IS-MY-REMOTE-ID_000/TXkgT3JnYW5pemF0aW9u/ai5kb2VAY3JlYWRzLm9yZw==/Sm9obg==/RG9l';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

    public function testValidateWitWrongUrl()
    {
        $signedUrl = 'http://127.0.0.1:8000/';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

    public function testValidateWithHackedSignature()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/THIS-IS-MY-REMOTE-ID_000/TXkgT3JnYW5pemF0aW9u/ai5kb2VAY3JlYWRzLm9yZw==/Sm9obg==/RG9l?expires=20181121T124026Z&signature=b47cd5c88f2d8fb1d4bcdd25397062c7769cc60992de0a34c57f84458fbe980f&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

    public function testValidateWithHackedOrganizationRid()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/THIS-IS-NOT_MY_ORIGINAL-ID_666/TXkgT3JnYW5pemF0aW9u/ai5kb2VAY3JlYWRzLm9yZw==/Sm9obg==/RG9l?expires=20181121T124026Z&signature=a47cd5c88f2d8fb1d4bcdd25397062c7769cc60992de0a34c57f84458fbe980f&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

    public function testValidateWithHackedOrganizationName()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/THIS-IS-NOT_MY_ORIGINAL-ID_666/TXkgT3JnYW5pemF0aW9u/Tm90IE15IE9yZ2FuaXphdGlvbg==/Sm9obg==/RG9l?expires=20181121T124026Z&signature=a47cd5c88f2d8fb1d4bcdd25397062c7769cc60992de0a34c57f84458fbe980f&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

    public function testValidateWithHackedEmail()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/THIS-IS-NOT_MY_ORIGINAL-ID_666/bm90LmouZG9lQGNyZWFkcy5vcmc=/ai5kb2VAY3JlYWRzLm9yZw==/Sm9obg==/RG9l?expires=20181121T124026Z&signature=a47cd5c88f2d8fb1d4bcdd25397062c7769cc60992de0a34c57f84458fbe980f&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

    public function testValidateWithHackedFirstname()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/THIS-IS-NOT_MY_ORIGINAL-ID_666/bm90LmouZG9lQGNyZWFkcy5vcmc=/ai5kb2VAY3JlYWRzLm9yZw==/Qm9i/RG9l?expires=20181121T124026Z&signature=a47cd5c88f2d8fb1d4bcdd25397062c7769cc60992de0a34c57f84458fbe980f&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

    public function testValidateWithHackedLastname()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/THIS-IS-MY-REMOTE-ID_000/TXkgT3JnYW5pemF0aW9u/Sm9obg==/ai5kb2VAY3JlYWRzLm9yZw==/TW9yYW5l?expires=20181121T124026Z&signature=a47cd5c88f2d8fb1d4bcdd25397062c7769cc60992de0a34c57f84458fbe980f&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

    public function testValidateWithHackedExpires()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/THIS-IS-MY-REMOTE-ID_000/TXkgT3JnYW5pemF0aW9u/ai5kb2VAY3JlYWRzLm9yZw==/Sm9obg==/RG9l?expires=20191119T181919Z&signature=a47cd5c88f2d8fb1d4bcdd25397062c7769cc60992de0a34c57f84458fbe980f&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

}
