<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PasswordCheckController
{
    public function __invoke(Request $request): Response
    {
        if (! $request->user()) {
            abort(403);
        }

        $request->validate([
            'password' => 'required|current_password',
        ]);

        return response(status: 200);
    }
}
