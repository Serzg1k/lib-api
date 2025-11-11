<?php
namespace common\components;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Yii;

class Jwt
{
    public function issueToken(int $userId): string
    {
        $cfg = Yii::$app->params['jwt'];
        $now = time();
        $payload = [
            'iss' => $cfg['issuer'],
            'aud' => $cfg['audience'],
            'iat' => $now, 'nbf' => $now, 'exp' => $now + $cfg['ttl'],
            'uid' => $userId,
        ];
        return FirebaseJWT::encode($payload, $cfg['key'], 'HS256');
    }

    public function validate(string $token): ?object
    {
        try {
            $cfg = Yii::$app->params['jwt'];
            return FirebaseJWT::decode($token, new Key($cfg['key'], 'HS256'));
        } catch (\Throwable $e) {
            return null;
        }
    }
}
