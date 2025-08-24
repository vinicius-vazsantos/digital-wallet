<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\HttpServer\Router\Router;

// Rotas públicas
Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');
Router::get('/favicon.ico', function () { return ''; });

// Rotas de autenticação
Router::post('/auth/login', 'App\Controller\AuthController@login');
Router::post('/auth/logout', 'App\Controller\AuthController@logout');

// Rotas OPTIONS para CORS
Router::addRoute(['OPTIONS'], '/accounts/{any:.*}', function () { return ''; });
Router::addRoute(['OPTIONS'], '/auth/{any:.*}', function () { return ''; });

// Rotas protegidas por JWT
Router::addGroup('/accounts', function () {
    // Teste de envio de e-mail
    Router::post('/test-email', 'App\Controller\TestEmailController@send');
    
    // CRUD de contas
    Router::get('', 'App\Controller\AccountController@getAll');
    Router::post('', 'App\Controller\AccountController@create');
    Router::get('/{accountId}', 'App\Controller\AccountController@getById');
    Router::put('/{accountId}', 'App\Controller\AccountController@update');
    Router::delete('/{accountId}', 'App\Controller\AccountController@delete');

    // Rotas de saque
    Router::addGroup('/{accountId}/balance', function () {
        Router::post('/withdraws', 'App\Controller\AccountWithdrawController@create');
        Router::get('/withdraws', 'App\Controller\AccountWithdrawController@getAll');
        Router::get('/withdraws/{withdrawId}', 'App\Controller\AccountWithdrawController@getById');
    });
}, ['middleware' => [App\Middleware\JwtAuthMiddleware::class]]);
