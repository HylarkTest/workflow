<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Support\Str;
use Illuminate\Contracts\Validation\Rule;

class Password implements Rule
{
    /**
     * The minimum length of the password.
     */
    protected int $length = 8;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $value = \is_scalar($value) ? (string) $value : '';

        if (Str::lower($value) === $value) {
            return false;
        }

        $hasNumeric = preg_match('/[0-9]/', $value);
        $hasSpecial = preg_match('/[\W_]/', $value);

        if (! $hasSpecial && ! $hasNumeric) {
            return false;
        }

        return Str::length($value) >= $this->length;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        /** @var string $message */
        $message = __('validation.password_rules', ['length' => $this->length]);

        return $message;
    }
}
