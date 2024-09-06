<?php

declare(strict_types=1);

use App\Models\MemberInvite;
use Illuminate\Support\Facades\Hash;

test('an invite can generate a token', function () {
    $token = MemberInvite::generateToken();
    expect($token)->toBeString()->toHaveLength(64);
});

test('setting the token hashes it', function () {
    $invite = new MemberInvite;
    $token = 'abc';

    $invite->token = $token;
    expect($invite->token)->not->toBe('abc')
        ->and(Hash::check('abc', $invite->token))->toBeTrue();
});
