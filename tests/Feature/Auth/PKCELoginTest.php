<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Support\Str;
use App\Models\Passport\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Console\Commands\MobileAppClientGenerateCommand;

uses(RefreshDatabase::class);

test('a user can get a PKCE authorization code', function (): void {
    $this->withExceptionHandling();

    $this->artisan(MobileAppClientGenerateCommand::class, ['--quiet' => true])
        ->expectsQuestion('Which user ID should the client be assigned to? (Optional)', '');

    /** @var \App\Models\Passport\Client $client */
    $client = Client::query()->latest()->first();
    config(['hylark.mobile.client_id' => $client->getKey()]);

    $user = createUser(['email' => 'test@example.com']);

    $state = Str::random(40);
    $codeVerifier = Str::random(128);
    $codeChallenge = strtr(rtrim(
        base64_encode(hash('sha256', $codeVerifier, true)), '='
    ), '+/', '-_');

    $query = http_build_query([
        'client_id' => $client->getKey(),
        'code_challenge' => $codeChallenge,
        'code_challenge_method' => 'S256',
        'prompt' => 'login',
        'redirect_uri' => 'http://localhost/callback',
        'response_type' => 'code',
        'scope' => '',
        'state' => $state,
    ]);

    $url = "/oauth/authorize?$query";

    $this->get($url)
        ->assertRedirect('/login');

    $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ])->assertJson(['redirect' => config('app.url').$url]);

    $response = $this->get($url)
        ->assertRedirectContains('http://localhost/callback?code');

    $url = $response->headers->get('Location');
    $query = parse_url($url, PHP_URL_QUERY);
    $query = collect(explode('&', $query))
        ->mapWithKeys(function (string $pair): array {
            [$key, $value] = explode('=', $pair);

            return [$key => $value];
        })
        ->toArray();

    expect($query['state'])->toBe($state);

    $response = $this->post('/oauth/token', [
        'client_id' => $client->getKey(),
        'code' => $query['code'],
        'code_verifier' => $codeVerifier,
        'grant_type' => 'authorization_code',
        'redirect_uri' => 'http://localhost/callback',
    ])->assertJsonStructure([
        'token_type',
        'expires_in',
        'access_token',
        'refresh_token',
    ]);

    $accessToken = $response->json('access_token');

    auth()->logout();

    $this->withoutExceptionHandling();

    $this->post(
        'graphql',
        ['query' => '{ me { id } }'],
        ['HTTP_Authorization' => "Bearer $accessToken"]
    )->assertJson([
        'data' => [
            'me' => [
                'id' => $user->globalId(),
            ],
        ],
    ]);
});
