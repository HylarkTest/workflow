<?php

declare(strict_types=1);

test('a number can be incremented on the end of a string', function () {
    expect(increment_string_suffix('test'))->toBe('test2');
    expect(increment_string_suffix('test4'))->toBe('test5');
    expect(increment_string_suffix('test9'))->toBe('test10');
    expect(increment_string_suffix('test19'))->toBe('test20');
    expect(increment_string_suffix('test002'))->toBe('test003');
    expect(increment_string_suffix('test021'))->toBe('test022');
});

test('a variable can be discerned as string castable', function () {
    expect(is_string_castable('test'))->toBeTrue();
    expect(is_string_castable(123))->toBeTrue();
    expect(is_string_castable(true))->toBeTrue();
    static::assertTrue(is_string_castable(new class
    {
        public function __toString(): string
        {
            return 'test';
        }
    }));

    expect(is_string_castable([]))->toBeFalse();
    expect(is_string_castable(new \stdClass))->toBeFalse();
});

test('arrays can be recursively checked for differences', function () {
    static::assertSame(
        ['a' => ['b' => 'c']],
        array_diff_recursive(
            [
                'a' => ['b' => 'c'],
                'd' => ['e' => 'f'],
                'g' => 'h',
            ],
            [
                'a' => ['b' => 'a'],
                'd' => ['e' => 'f'],
                'g' => 'h',
            ],
        ),
    );
});

test('array keys can be intersected recursively', function () {
    static::assertSame(
        ['a' => ['b' => 'c']],
        array_intersect_key_recursive(
            [
                'a' => ['b' => 'c'],
                'd' => ['e' => 'f'],
                'g' => 'h',
            ],
            [
                'a' => ['b' => 'a'],
                'd' => ['d' => 'f'],
                'h' => 'h',
            ],
        ),
    );
});
