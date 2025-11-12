<?php

declare(strict_types=1);


namespace apiTests\Api;

use apiTests\ApiTester;

final class AuthCest
{
    public function loginOk(ApiTester $I)
    {
        $login = 'alice_' . uniqid();
        $email = $login . '@test.local';

        // register user
        $I->sendPost('/users', ['login'=>$login,'password'=>'secret123','email'=>$email]);
        $I->seeResponseCodeIs(200);

        // login user
        $I->sendPost('/auth/login', ['login'=>$login,'password'=>'secret123']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('"token"');

        $token = $I->grabDataFromResponseByJsonPath('$.token')[0] ?? '';
        $I->assertNotEmpty($token);
    }
}
