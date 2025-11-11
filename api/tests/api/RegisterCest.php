<?php
namespace apiTests\Api;

use apiTests\ApiTester;

class RegisterCest
{
    public function registerSuccess(ApiTester $I)
    {
        $login = 'newuser_' . uniqid();
        $email = $login . '@test.local';

        $I->sendPost('/users', ['login'=>$login,'password'=>'pass123','email'=>$email]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['username'=>$login, 'email'=>$email]);
    }
}
