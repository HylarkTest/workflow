<?php

declare(strict_types=1);

namespace App\Models\Contracts;

use Illuminate\Http\Request;

interface CanUseOneTimePassword
{
    public function sendOneTimePassword(Request $request, null|int|\DateInterval $timeout): string;

    public function verifyOneTimePassword(string $password): bool;

    public function forgetOneTimePassword(): void;
}
