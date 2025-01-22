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
        $router->get('restore/{id:[0-9]+}', ['as' => 'api.v1.certificates.restore', 'uses' => 'CertificatesController@restore']);
        $router->get('refund/{id:[0-9]+}', ['as' => 'api.v1.certificates.refund', 'uses' => 'CertificatesController@refund']);
        $router->get('download/{id:[0-9]+}', ['as' => 'api.v1.certificates.download', 'uses' => 'CertificatesController@download']);
        $router->get('all-fields/{id:[0-9]+}', ['as' => 'api.v1.certificates.all_fields', 'uses' => 'CertificatesController@allFields']);
        $router->get('non-destructive-test-step/{id:[0-9]+}', ['as' => 'api.v1.certificates.non_destructive_test_step', 'uses' => 'CertificatesController@nonDestructiveTestStep']);
        $router->post('update-non-destructive-test-step/{id:[0-9]+}', ['as' => 'api.v1.certificates.update_non_destructive_test_step', 'uses' => 'CertificatesController@updateNonDestructiveTestStep']);
        $router->post('create-meld/{id:[0-9]+}', ['as' => 'api.v1.certificates.create_meld', 'uses' => 'CertificatesController@createMeld']);
        $router->post('delete-meld', ['as' => 'api.v1.certificates.delete_meld', 'uses' => 'CertificatesController@deleteMeld']);
        $router->post('create-roll', ['as' => 'api.v1.certificates.create_roll', 'uses' => 'CertificatesController@createRoll']);
        $router->post('delete-roll', ['as' => 'api.v1.certificates.delete_roll', 'uses' => 'CertificatesController@deleteRoll']);
        $router->post('update-detail-tube-step', ['as' => 'api.v1.certificates.update_detail_tube_step', 'uses' => 'CertificatesController@updateDetailTubeStep']);
        $router->get('detail-tube-step/{id:[0-9]+}', ['as' => 'api.v1.certificates.detail_tube_step', 'uses' => 'CertificatesController@detailTubeStep']);
        $router->post('rolls-sort-step/{id:[0-9]+}', ['as' => 'api.v1.certificates.rolls_sort_step', 'uses' => 'CertificatesController@rollsSortStep']);
        $router->get('cylinder-step/{id:[0-9]+}', ['as' => 'api.v1.certificates.cylinder_step', 'uses' => 'CertificatesController@cylinderStep']);
        $router->post('cylinder-update-step', ['as' => 'api.v1.certificates.cylinder_update_step', 'uses' => 'CertificatesController@cylinderUpdateStep']);
        $router->post('create-note', ['as' => 'api.v1.certificates.create_note', 'uses' => 'CertificatesController@createNote']);
        $router->post('delete-note', ['as' => 'api.v1.certificates.delete_note', 'uses' => 'CertificatesController@deleteNote']);
        $router->get('note-step', ['as' => 'api.v1.certificates.note_step', 'uses' => 'CertificatesController@noteStep']);
        $router->post('create-signature', ['as' => 'api.v1.certificates.create_signature', 'uses' => 'CertificatesController@createSignature']);
        $router->post('delete-signature', ['as' => 'api.v1.certificates.delete_signature', 'uses' => 'CertificatesController@deleteSignature']);
        $router->get('signature-step/{id:[0-9]+}', ['as' => 'api.v1.certificates.signature_step', 'uses' => 'CertificatesController@signatureStep']);
        $router->post('update-signature-step', ['as' => 'api.v1.certificates.update_signature_step', 'uses' => 'CertificatesController@updateSignatureStep']);
        $router->post('copy', ['as' => 'api.v1.certificates.copy', 'uses' => 'CertificatesController@copy']);
        $router->get('approve/{id:[0-9]+}', ['as' => 'api.v1.certificates.approve', 'uses' => 'CertificatesController@approve']);
        $router->get('publish/{id:[0-9]+}', ['as' => 'api.v1.certificates.publish', 'uses' => 'CertificatesController@publish']);
    });

    $router->group(['prefix' => 'references'], function () use ($router) {
        $router->post('standard-list', ['as' => 'api.v1.references.standard_list', 'uses' => 'ReferencesController@standardList']);
        $router->post('hardness-list', ['as' => 'api.v1.references.hardness_list', 'uses' => 'ReferencesController@hardnessList']);
        $router->post('outerdiameter-list', ['as' => 'api.v1.references.outerdiameter_list', 'uses' => 'ReferencesController@outerDiameterList']);
        $router->post('customer-list', ['as' => 'api.v1.references.customer_List', 'uses' => 'ReferencesController@customerList']);
        $router->post('control-method-list', ['as' => 'api.v1.references.control_method_list', 'uses' => 'ReferencesController@controlMethodList']);
        $router->post('wall-thickness-list', ['as' => 'api.v1.references.wall_thickness_list', 'uses' => 'ReferencesController@wallThicknessList']);
        $router->get('standard/{id:[0-9]+}', ['as' => 'api.v1.references.standard', 'uses' => 'ReferencesController@standard']);
        $router->get('hardness/{id:[0-9]+}', ['as' => 'api.v1.references.hardness', 'uses' => 'ReferencesController@hardness']);
        $router->get('outer-diameter/{id:[0-9]+}', ['as' => 'api.v1.references.outer_diameter', 'uses' => 'ReferencesController@outerDiameter']);
    });
});

