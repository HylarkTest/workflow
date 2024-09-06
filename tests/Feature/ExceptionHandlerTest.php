<?php

declare(strict_types=1);

test('maintenance mode shows an appropriate message', function () {
    $this->withExceptionHandling();
    config(['app.maintenance.driver' => 'cache']);
    app()->maintenanceMode()->activate([]);
    $this->get('/')->assertSee('Hylark is down for maintenance.');
});
