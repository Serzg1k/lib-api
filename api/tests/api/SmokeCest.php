<?php

declare(strict_types=1);


namespace apiTests\Api;

use apiTests\ApiTester;

final class SmokeCest
{
    public function booksList(ApiTester $I)
    {
        $I->sendGet('/books?page=1&per-page=1');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'items' => 'array',
            '_meta' => ['currentPage'=>'integer','perPage'=>'integer'],
        ]);
    }
}
