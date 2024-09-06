<?php

declare(strict_types=1);

use Maatwebsite\Excel\Facades\Excel;
use App\Core\Imports\Importers\RowCounter;

test('the number of rows in an import can be counted', function () {
    $file = __DIR__.'/../../resources/imports/dummy_imports.csv';
    $import = new RowCounter(false);
    Excel::import($import, $file);

    expect($import->getCount())->toBe(4);
});
