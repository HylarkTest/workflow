<?php

declare(strict_types=1);

namespace Database\Providers;

use Faker\Provider\Base;
use Illuminate\Support\Facades\Storage;

class LogoProvider extends Base
{
    /**
     * Download a remote random image to disk and return its location
     *
     * Requires curl, or allow_url_fopen to be on in php.ini.
     *
     * @param  mixed|null  $dir
     * @param  mixed  $fullPath
     * @return bool|\RuntimeException|string
     *
     * @example '/path/to/dir/13b73edae8443990be1aa8f1a483bc27.jpg'
     */
    public static function logo($dir = null, $fullPath = true)
    {
        $dir = $dir ?? sys_get_temp_dir(); // GNU/Linux / OS X / Windows compatible
        // Validate directory path
        if (! is_dir($dir) || ! is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('Cannot write to directory "%s"', $dir));
        }

        // Generate a random filename. Use the server address so that a file
        // generated at the same time on a different server won't have a collision.
        $name = md5(uniqid(empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'], true));
        $filename = $name.'.png';
        $filepath = $dir.\DIRECTORY_SEPARATOR.$filename;

        $files = glob(storage_path('test/logos/*.png'));
        $path = static::randomElement($files);

        $success = copy($path, $filepath);

        return $fullPath ? $filepath : $filename;
    }

    public static function storedLogo($directory = 'logos'): string
    {
        $disk = Storage::disk('public');

        return $disk->url($disk->putFile($directory, static::logo()));
    }
}
