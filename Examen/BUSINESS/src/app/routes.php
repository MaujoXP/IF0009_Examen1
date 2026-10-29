<?php
namespace app\controllers;

use Slim\Routing\RouteCollectorProxy;

$app->group('/api', function (RouteCollectorProxy $api) {
    $api->group('/ordenes_trabajo', function (RouteCollectorProxy $endpoint) {
        $endpoint->get('', OrdenTrabajo::class . ':read');
        $endpoint->get('/{id}', OrdenTrabajo::class . ':read');
        $endpoint->post('', OrdenTrabajo::class . ':create');
        $endpoint->put('/{id}', OrdenTrabajo::class . ':update');
        $endpoint->delete('/{id}', OrdenTrabajo::class . ':delete');
    });
});
