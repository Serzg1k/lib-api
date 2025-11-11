<?php

declare(strict_types=1);


namespace apiTests\Api;

use apiTests\ApiTester;

final class BookCest
{
    private function auth(ApiTester $I): string
    {
        $I->sendPost('/users', ['login'=>'bob','password'=>'secret123','email'=>'b@b.b']);
        $I->sendPost('/auth/login', ['login'=>'bob','password'=>'secret123']);
        return $I->grabDataFromResponseByJsonPath('$.token')[0];
    }

    public function crud(ApiTester $I)
    {
        $token = $this->auth($I);
        $I->haveHttpHeader('Authorization', "Bearer {$token}");

        // create
        $I->sendPost('/books', ['title'=>'Clean Code','author'=>'R. Martin','published_year'=>2008]);
        $I->seeResponseCodeIs(200);
        $bookId = $I->grabDataFromResponseByJsonPath('$.id')[0];

        // view
        $I->sendGet("/books/{$bookId}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id'=>$bookId,'title'=>'Clean Code']);

        // update
        $I->sendPut("/books/{$bookId}", ['description'=>'Updated']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['description'=>'Updated']);

        // delete
        $I->sendDelete("/books/{$bookId}");
        $I->seeResponseCodeIs(200);

        // verify 404
        $I->sendGet("/books/{$bookId}");
        $I->seeResponseCodeIs(404);
    }

    public function createUnauthorized(ApiTester $I)
    {
        $I->sendPost('/books', ['title'=>'X','author'=>'Y']);
        $I->seeResponseCodeIs(401);
    }
}
