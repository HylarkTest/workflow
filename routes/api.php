<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Models\Base;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth:web,api', 'throttle:'.config('fortify.limiters.verification', '6,1')])
    ->name('verification.send');

Route::middleware(['auth:web,api', 'tenancy'])->group(function () {
    Route::post('bootstrap', \App\Http\Controllers\BootstrapController::class)
        ->name('bootstrap');

    Route::post('page-wizard', \App\Http\Controllers\PageWizardController::class)
        ->name('page-wizard');

    Route::post('base', [\App\Http\Controllers\BaseController::class, 'store'])
        ->name('base.store');

    Route::get('preferences', [\App\Http\Controllers\PreferencesController::class, 'index'])
        ->name('preferences.index');

    Route::post('preferences', [\App\Http\Controllers\PreferencesController::class, 'update'])
        ->name('preferences.update');

    Route::delete('account', [\App\Http\Controllers\AccountController::class, 'destroy'])
        ->name('delete-account');

    Route::get('login-history', [\App\Http\Controllers\LoginHistoryController::class, 'index'])
        ->name('login-history.index');

    Route::get('billing/intent', [\App\Http\Controllers\SubscriptionController::class, 'intent']);
    Route::get('billing/subscription', [\App\Http\Controllers\SubscriptionController::class, 'index']);
    Route::post('billing/subscription', [\App\Http\Controllers\SubscriptionController::class, 'store']);
    Route::put('billing/subscription', [\App\Http\Controllers\SubscriptionController::class, 'update']);
    Route::delete('billing/subscription', [\App\Http\Controllers\SubscriptionController::class, 'destroy']);
    Route::post('billing/subscription/renew', [\App\Http\Controllers\SubscriptionController::class, 'renew']);
    Route::get('billing/coupon/{plan}/{code}', [\App\Http\Controllers\CouponController::class, 'show']);

    Route::post('member-invite', [\App\Http\Controllers\MemberController::class, 'store'])
        ->name('member-invite.store')
        ->can('invite', Base::class);
    Route::put('member-invite/{email}', [\App\Http\Controllers\MemberController::class, 'update'])
        ->name('member-invite.update')
        ->can('invite', Base::class);
    Route::delete('member-invite/{email}', [\App\Http\Controllers\MemberController::class, 'destroy'])
        ->name('member-invite.destroy')
        ->can('invite', Base::class);

    Route::get('image-search', [\App\Http\Controllers\ImageSearchController::class, 'index'])
        ->middleware('throttle:60,1')
        ->name('image-search.index');

    Route::get('/download/{disk}/{url}', function (string $disk, string $url) {
        return Storage::disk($disk)->download($url);
    })->where([
        'disk' => 'documents|images',
        'url' => '.*',
    ])->name('download');
});
