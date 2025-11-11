<?php
use yii\web\Response;

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php'
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => [],
    'components' => [
        'request' => [
            'parsers' => ['application/json' => 'yii\web\JsonParser'],
            'cookieValidationKey' => 'change-me',
            'enableCsrfValidation' => false, // для чистого API
        ],
        'response' => [
            'format' => Response::FORMAT_JSON,
        ],
        'user' => [
            'identityClass' => common\models\User::class,
            'enableSession' => false, // без сессий
            'loginUrl' => null,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // books
                ['class' => 'yii\rest\UrlRule', 'controller' => ['book'], 'pluralize' => true, 'extraPatterns' => [
                    'GET' => 'index',
                    'POST' => 'create',
                    'GET {id}' => 'view',
                    'PUT {id}' => 'update',
                    'DELETE {id}' => 'delete',
                ]],
                // users
                'POST users' => 'user/register',
                'GET users/<id:\d+>' => 'user/view',
                // auth
                'POST auth/login' => 'auth/login',
            ],
        ],
        'jwt' => ['class' => common\components\Jwt::class],
    ],
    'params' => $params,
];
