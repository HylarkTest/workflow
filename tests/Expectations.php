<?php

declare(strict_types=1);

use BenSampo\Enum\Enum;
use Illuminate\Database\Eloquent\Model;

expect()->intercept('toBe', Enum::class, function (Enum $expected) {
    return expect($this->value->is($expected))->toBeTrue(
        'Expected value to be enum '.$expected::class.'::'.$expected->value.', got '.$this->value::class.'::'.$this->value->value
    );
});

expect()->intercept('toBe', Model::class, function (Model $expected) {
    return expect($this->value->is($expected))->toBeTrue(
        'Expected value to be model '.$expected::class.'::'.$expected->getKey().', got '.$this->value::class.'::'.$this->value->getKey()
    );
});
