<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->group(['prefix' => 'api/v1/'], function () use ($router) {
    // Route::apiResource('projects', CertificatesController::class);
    $router->group(['prefix' => 'certificates'], function () use ($router) {
        $router->post('draft', ['as' => 'api.v1.certificates.draft', 'uses' => 'CertificatesController@listDraft']);
        $router->post('published', ['as' => 'api.v1.certificates.published', 'uses' => 'CertificatesController@listPublished']);
        $router->post('deleted', ['as' => 'api.v1.certificates.deleted', 'uses' => 'CertificatesController@listDeleted']);
        $router->post('approve', ['as' => 'api.v1.certificates.approve', 'uses' => 'CertificatesController@listApprove']);
        $router->post('create', ['as' => 'api.v1.certificates.create', 'uses' => 'CertificatesController@create']);

        $router->put('update-common-step/{id:[0-9]+}', ['as' => 'api.v1.certificates.update_common_step', 'uses' => 'CertificatesController@updateCommonStep']);

        $router->delete('delete/{id:[0-9]+}', ['as' => 'api.v1.certificates.delete', 'uses' => 'CertificatesController@delete']);


        //listApprove
    });


  //  $router->get('projects', 'ProjectController@index');
  //  $router->post('projects', 'ProjectController@store');
  //  $router->get('projects/{id}', 'ProjectController@show');
  //  $router->patch('projects/{id}', 'ProjectController@update');
  //  $router->delete('projects/{id}', 'ProjectController@destroy');
});

