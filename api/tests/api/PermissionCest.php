<?php

declare(strict_types=1);


namespace apiTests\Api;

use apiTests\ApiTester;

final class PermissionCest
{
    private function registerAndLogin(ApiTester $I, string $login): string
    {
        $email = $login.'@test.local';
        $I->sendPost('/users', ['login'=>$login, 'password'=>'secret123', 'email'=>$email]);
        $I->seeResponseCodeIs(200);

        $I->sendPost('/auth/login', ['login'=>$login, 'password'=>'secret123']);
        $I->seeResponseCodeIs(200);
        return $I->grabDataFromResponseByJsonPath('$.token')[0];
    }

    public function forbidEditingForeignBook(ApiTester $I)
    {
        // Пользователь А создаёт книгу
        $tokenA = $this->registerAndLogin($I, 'owner_'.uniqid());
        $I->haveHttpHeader('Authorization', "Bearer {$tokenA}");
        $I->sendPost('/books', ['title'=>'TDD', 'author'=>'K. Beck', 'published_year'=>2003]);
        $I->seeResponseCodeIs(200);
        $bookId = $I->grabDataFromResponseByJsonPath('$.id')[0];

        // Пользователь B пытается править/удалять
        $tokenB = $this->registerAndLogin($I, 'intruder_'.uniqid());
        $I->haveHttpHeader('Authorization', "Bearer {$tokenB}");

        // Обновление — должно быть 403
        $I->sendPut("/books/{$bookId}", ['description'=>'hacked']);
        $I->seeResponseCodeIs(403);

        // Удаление — тоже 403
        $I->sendDelete("/books/{$bookId}");
        $I->seeResponseCodeIs(403);

        // Просмотр чужой книги разрешён (200)
        $I->sendGet("/books/{$bookId}");
        $I->seeResponseCodeIs(200);
    }
}
