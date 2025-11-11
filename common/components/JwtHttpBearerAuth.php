<?php
namespace common\components;

use yii\filters\auth\AuthMethod;
use common\models\User;
use yii\web\IdentityInterface;

class JwtHttpBearerAuth extends AuthMethod
{
    public function authenticate($user, $request, $response): User|IdentityInterface|null
    {
        $auth = $request->getHeaders()->get('Authorization');
        if (!$auth || !preg_match('/^Bearer\\s+(.*?)$/', $auth, $m)) {
            return null;
        }
        $identity = User::findIdentityByAccessToken($m[1]);
        if (!$identity) {
            $this->handleFailure($response);
            return null;
        }
        $user->login($identity);
        return $identity;
    }
}
