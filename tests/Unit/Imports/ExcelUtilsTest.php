<?php

declare(strict_types=1);

use App\Core\Imports\ExcelUtils;

test('Excel dates can be converted to Carbon', function () {
    expect(ExcelUtils::convertDateCellToCarbon(1))
        ->toDateTimeString()->toBe('1900-01-01 00:00:00');
    expect(ExcelUtils::convertDateCellToCarbon(59))
        ->toDateTimeString()->toBe('1900-02-28 00:00:00');
    expect(ExcelUtils::convertDateCellToCarbon(60))
        ->toDateTimeString()->toBe('1900-03-01 00:00:00');
    expect(ExcelUtils::convertDateCellToCarbon(61))
        ->toDateTimeString()->toBe('1900-03-01 00:00:00');
    expect(ExcelUtils::convertDateCellToCarbon(37634))
        ->toDateTimeString()->toBe('2003-01-13 00:00:00');
    expect(ExcelUtils::convertDateCellToCarbon(37634.541666667))
        ->toDateTimeString()->toBe('2003-01-13 13:00:00');
});
