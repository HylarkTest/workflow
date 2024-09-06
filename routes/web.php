<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Session\SessionManager;
use App\Http\Middleware\EncryptCookies;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\Auth\UpdateEmailController;
use App\Http\Controllers\Auth\PasswordCheckController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::post('/one-time-password', [\App\Http\Controllers\Auth\OneTimePasswordAuthenticatedSessionController::class, 'store'])
    ->middleware(array_filter([
        'guest:'.config('fortify.guard'),
        'throttle:one-time-password',
    ]));

Route::post('/register-check', function (Request $request) {
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => [
            'required',
            'string',
            'email',
            'max:255',
            function ($attribute, $value, $fail) {
                if (User::query()->where('email', ilike(), $value)->exists()) {
                    $fail(trans('validation.unique', ['email' => 'email']));
                }
            },
        ],
        'permission' => 'accepted',
    ]);

    return response('', \Symfony\Component\HttpFoundation\Response::HTTP_NO_CONTENT);
})->middleware(array_filter([
    'guest:'.config('fortify.guard'),
]));

Route::get('invite/accept/{invite}', [\App\Http\Controllers\MemberController::class, 'accept'])
    ->name('member-invite.accept');

Route::get('invite/resend/{invite}', [\App\Http\Controllers\MemberController::class, 'resend'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('member-invite.resend');

Route::middleware(['auth:web'])->group(function () {
    Route::put('switch-base/{baseId}', \App\Http\Controllers\Auth\SwitchBaseController::class)
        ->name('switch-base');
});

// Here we replace the Fortify controller with our own because we want to show
// the user the code by itself, not the whole URL
Route::group(['middleware' => config('fortify.middleware', ['web'])], function () {
    Route::get('/user/two-factor-qr-code', [\App\Http\Controllers\Auth\TwoFactorQrCodeController::class, 'show'])
        ->name('two-factor.qr-code')
        ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard')]);

    Route::delete('/user/two-factor-authentication', [\Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController::class, 'destroy'])
        ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard'), 'password.confirm'])
        ->name('two-factor.disable');
});

$verificationLimiter = config('fortify.limiters.verification', '6,1');
Route::get('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard'), 'throttle:'.$verificationLimiter])
    ->name('verification.send.get');

if (! app()->environment('production')) {
    Route::view('/template-test', 'templates-index');
    Route::view('/cookie-banner-test', 'cookie-banner-index');
}

Route::get('/font-awesome-query/{query}', function (Request $request, string $query) {
    if (! ($token = cache('font-awesome-token'))) {
        $apiKey = env('FONT_AWESOME_API_KEY');

        $response = Http::withToken($apiKey)
            ->post('https://api.fontawesome.com/token');

        $token = $response->json('access_token');
        cache()->set('font-awesome-token', $token, $response->json('expires_in'));
    }

    $query = preg_replace('/[^a-zA-Z\s]/', '', $query);
    $response = Http::withToken($token)
        ->post('https://api.fontawesome.com', [
            'query' => '
            query Search($query: String!) {
                search(version: "6.0.0", query: $query, first: 60) {
                    id
                    styles
                }
            }
            ',
            'variables' => ['query' => $query],
        ]);

    $data = $response->json('data');

    $data['search'] = collect($data['search'])
        ->filter(fn ($icon) => in_array('light', $icon['styles'], true))
        ->pluck('id')
        ->take(40);

    return $data;
});

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware(['guest:'.config('fortify.guard')])
    ->name('password.email');

Route::get('/auth/check', function (SessionManager $manager, Request $request) {
    $session = $manager->driver();
    $session->setId($request->cookies->get($session->getName()));
    $session->setRequestOnHandler($request);
    $session->start();

    $authenticated = auth()->check();

    return response()->json([
        'authenticated' => $authenticated,
    ]);
})->middleware(EncryptCookies::class);

Route::middleware(['auth:web', 'throttle:60,1'])
    ->group(function () {
        Route::post('/api/support', [SupportController::class, 'store'])->name('support.ticket');
        Route::get('/api/support', [SupportController::class, 'index'])->name('support.index');
        Route::get('/api/support/categories', [SupportController::class, 'indexCategories'])->name('support.categories');
        Route::get('/api/support/topics', [SupportController::class, 'indexTopics'])->name('support.topics');
        Route::get('/api/support/folders/{id}', [SupportController::class, 'showFolder'])->name('support.showFolder');
        Route::get('/api/support/{id}', [SupportController::class, 'show'])->name('support.show');
        Route::put('/api/support/{id}/view', [SupportController::class, 'incrementView'])->name('support.view');
        Route::put('/api/support/{id}/thumbs-up', [SupportController::class, 'incrementThumbsUp'])->name('support.thumbs-up');
        Route::put('/api/support/{id}/thumbs-down', [SupportController::class, 'incrementThumbsDown'])->name('support.thumbs-down');
    });

Route::post('user/password/check', PasswordCheckController::class)->middleware('auth:web');
Route::post('user/email', [UpdateEmailController::class, 'store'])->middleware(['auth:web', 'throttle:one-time-password']);
Route::post('user/email/verify', [UpdateEmailController::class, 'verify'])->middleware('auth:web');

app()->booted(fn () => Route::get('/{any}', function (?string $any = null) {
    $patterns = [
        'bitkeeper',
        '^\.',
        '^%2e',
        '^\s',
        '\s/?$',
        '\?\??$',
        '\.\./app\.py$',
    ];
    if ($any && preg_match('#('.implode('|', $patterns).')#i', $any)) {
        abort(404);
    }

    return view('index');
})->where('any', '.*')->name('index'));
