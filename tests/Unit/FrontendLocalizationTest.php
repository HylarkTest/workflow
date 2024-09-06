<?php

declare(strict_types=1);

test('the front end localization files can be used', function () {
    static::assertSame(
        'All',
        __('*.common.all')
    );
});
