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
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/ai5kb2VAY3JlYWRzLm9yZw==/TXkgT3JnYW5pemF0aW9u?expires=20181119T184807Z&signature=27fff830f92c063bff89dbf34636e2bf594b12310402d92fb4c3ce5babe487ec&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new V0SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertTrue($result);
    }

    public function testValidateWithNoQueryParameters()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/ai5kb2VAY3JlYWRzLm9yZw==/TXkgT3JnYW5pemF0aW9u';
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
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/ai5kb2VAY3JlYWRzLm9yZw==/TXkgT3JnYW5pemF0aW9u?expires=20181119T184807Z&signature=f7fff830f92c063bff89dbf34636e2bf594b12310402d92fb4c3ce5babe487ec&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new V0SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }


    public function testValidateWithHackedOrganizationName()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/fi5kb2VAY3JlYWRzLm9yZw==/TXkgT3JnYW5pemF0aW9u?expires=20181119T184807Z&signature=27fff830f92c063bff89dbf34636e2bf594b12310402d92fb4c3ce5babe487ec&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new V0SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

    public function testValidateWithHackedEmail()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/ai5kb2VAY3JlYWRzLm9yZw==/XXkgT3JnYW5pemF0aW9u?expires=20181119T184807Z&signature=27fff830f92c063bff89dbf34636e2bf594b12310402d92fb4c3ce5babe487ec&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new V0SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }

    public function testValidateWithHackedExpires()
    {
        $signedUrl = 'http://127.0.0.1:8000/v1/signed-auth/ai5kb2VAY3JlYWRzLm9yZw==/TXkgT3JnYW5pemF0aW9u?expires=20191119T184807Z&signature=27fff830f92c063bff89dbf34636e2bf594b12310402d92fb4c3ce5babe487ec&accessKeyId=15_64d081gwqckkwwkc0s0occ4ckss8ww04o4ow80k0444kkkg4g0';
        $secretKey = '27ldyvaorh34coc00kggkogscgwc8s84go8ck8wks4ogsgg8kk';

        $result = (new V0SignedAuthenticationUrl())->validate($secretKey, $signedUrl);

        $this->assertFalse($result);
    }
}
