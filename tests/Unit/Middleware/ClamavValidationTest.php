<?php

declare(strict_types=1);

use App\Core\Clamav;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Exceptions\ClamavException;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    if (! config('clamav.enabled')) {
        static::markTestSkipped('ClamAV is disabled, edit your config to enable it');
    }

    $clamav = resolve(Clamav::class);
    try {
        $session = $clamav->startSession();
        $session->ping();
    } catch (\Exception $e) {
        static::markTestSkipped('ClamAV is not running');
    }
    $clamav->endSession();
});

test('clam av validates all requests that upload files', function () {
    $this->withoutExceptionHandling();
    Route::post('test-route', function (Request $request) {
        $this->fail('Entered a request with a corrupt file.');
    });

    $testFile = UploadedFile::fake()->create('test.txt');

    // cspell:disable-next-line
    file_put_contents($testFile->path(), 'X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*');

    $this->expectException(ClamavException::class);
    // cspell:disable-next-line
    $this->expectExceptionMessage('ClamAV scanner found a possible virus in "stream" with error "Win.Test.EICAR_HDB-1"');

    $this->post('test-route', [
        'file' => $testFile,
    ]);
});

test('clam av ignores requests with no files', function () {
    $this->withoutExceptionHandling();
    Route::post('test-route', fn (Request $request) => true);

    $this->post('test-route')->assertSuccessful();
});

test('clam av ignores safe files', function () {
    $this->withoutExceptionHandling();
    Route::post('test-route', fn (Request $request) => true);

    $testFile = UploadedFile::fake()->create('test.txt');

    $this->post('test-route', [
        'file' => $testFile,
    ])->assertSuccessful();
});
