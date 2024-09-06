<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array
     */
    protected function passwordRules()
    {
        return [
            'required',
            'string',
            new Password,
        ];
    }
}
