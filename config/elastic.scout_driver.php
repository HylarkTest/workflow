<?php

declare(strict_types=1);

return [
    'refresh_documents' => env('ELASTIC_SCOUT_DRIVER_REFRESH_DOCUMENTS', env('APP_ENV') === 'local' || env('APP_ENV') === 'testing'),
];
