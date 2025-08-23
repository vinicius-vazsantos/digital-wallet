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

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

Router::get('/favicon.ico', function () {
    return '';
});

Router::addGroup('/account', function () {
    
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
});
