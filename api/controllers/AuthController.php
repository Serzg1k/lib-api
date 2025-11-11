<?php
namespace api\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\VerbFilter;
use common\models\User;

class AuthController extends Controller
{
    public function behaviors()
    {
        $b = parent::behaviors();
        $b['verbs'] = ['class'=>VerbFilter::class,'actions'=>['login'=>['POST']]];
        return $b;
    }

    public function actionLogin(): array
    {
        $body = Yii::$app->request->bodyParams;
        $login = $body['login'] ?? $body['username'] ?? $body['email'] ?? null;
        $password = $body['password'] ?? null;

        if (!$login || !$password) {
            Yii::$app->response->statusCode=400;
            return ['error'=>'login and password are required'];
        }

        $user = User::find()->where(['or',['username'=>$login],['email'=>$login]])->one();
        if (!$user || !$user->validatePassword($password)) {
            Yii::$app->response->statusCode=401; return ['error'=>'Invalid credentials'];
        }

        return [
            'token' => Yii::$app->jwt->issueToken($user->id),
            'token_type' => 'Bearer',
            'expires_in' => Yii::$app->params['jwt']['ttl'],
        ];
    }
}
