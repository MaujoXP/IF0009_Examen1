<?php
$container->set('config', function () {
    return (object) [
        'api_data' => rtrim($_ENV['API_DATA'], '/'),
        'timeout' => (float) ($_ENV['API_TIMEOUT'] ?? 5),
    ];
});

$container->set(\app\Services\DataService::class, function ($container) {
    return new \app\Services\DataService($container->get('config'));
});
