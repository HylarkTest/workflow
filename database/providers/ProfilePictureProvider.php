<?php

declare(strict_types=1);

namespace Database\Providers;

use Faker\Provider\Base;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Mappings\Core\Documents\Contracts\DocumentRepository;

class ProfilePictureProvider extends Base
{
    protected static array $genders = ['male' => 'men', 'female' => 'women', 'lego' => 'lego'];

    /**
     * @param  bool  $isPerson
     */
    public static function gender($isPerson = false): string
    {
        if ($isPerson) {
            return static::numberBetween(0, 1) ? 'male' : 'female';
        }
        $isPerson = static::numberBetween(1, 210) <= 200;

        return $isPerson ? (static::numberBetween(0, 1) ? 'male' : 'female') : 'lego';
    }

    /**
     * Generate the URL that will return a random profile picture
     *
     * @param  mixed|null  $gender
     */
    public static function profilePictureUrl($gender = null): string
    {
        $baseUrl = 'https://randomuser.me/api/portraits/';

        if ($gender) {
            if (! isset(static::$genders[$gender])) {
                throw new \InvalidArgumentException(sprintf('Invalid gender "%s"', $gender));
            }
            $gender = static::$genders[$gender];
        } else {
            $gender = static::$genders[static::gender()];
        }

        $baseUrl .= "{$gender}/";

        $max = $gender === 'lego' ? 9 : 99;

        $baseUrl .= static::numberBetween(0, $max).'.jpg';

        return $baseUrl;
    }

    public static function croppedProfilePicture($gender = null): array
    {
        $documents = resolve(DocumentRepository::class);
        $originalPath = static::profilePicture(null, $gender, true);
        /** @var \Illuminate\Http\UploadedFile $uploadedFile */
        $uploadedFile = new UploadedFile($originalPath, 'profile.jpg', 'jpeg');
        $original = $documents->store($uploadedFile);
        $image = (new ImageManager)->make($uploadedFile)
            ->crop(30, 30, 5, 5);
        $imageFile = new UploadedFile(
            $image->basePath(),
            $uploadedFile->getClientOriginalName(),
            $uploadedFile->getClientMimeType(),
            $uploadedFile->getError(),
        );
        $document = $documents->store($imageFile);

        return [
            'image' => $document->id(),
            'originalImage' => $original->id(),
            'width' => 30,
            'height' => 30,
            'xOffset' => 5,
            'yOffset' => 5,
        ];
    }

    /**
     * Download a remote random image to disk and return its location
     *
     * Requires curl, or allow_url_fopen to be on in php.ini.
     *
     * @param  mixed|null  $dir
     * @param  null  $gender
     * @param  mixed  $fullPath
     * @return bool|\RuntimeException|string
     *
     * @example '/path/to/dir/13b73edae8443990be1aa8f1a483bc27.jpg'
     */
    public static function profilePicture($dir = null, $gender = null, $fullPath = true)
    {
        $dir = $dir ?? sys_get_temp_dir(); // GNU/Linux / OS X / Windows compatible
        // Validate directory path
        if (! is_dir($dir) || ! is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('Cannot write to directory "%s"', $dir));
        }

        // Generate a random filename. Use the server address so that a file
        // generated at the same time on a different server won't have a collision.
        $name = md5(uniqid(empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'], true));
        $filename = $name.'.jpg';
        $filepath = $dir.\DIRECTORY_SEPARATOR.$filename;

        $url = static::profilePictureUrl($gender);

        // save file
        if (\function_exists('curl_exec')) {
            // use cURL
            $fp = fopen($filepath, 'w');
            $ch = curl_init($url);
            curl_setopt($ch, \CURLOPT_FILE, $fp);
            $success = curl_exec($ch) && curl_getinfo($ch, \CURLINFO_HTTP_CODE) === 200;
            fclose($fp);
            curl_close($ch);

            if (! $success) {
                unlink($filepath);

                // could not contact the distant URL or HTTP error - fail silently.
                return false;
            }
        } elseif (\ini_get('allow_url_fopen')) {
            // use remote fopen() via copy()
            $success = copy($url, $filepath);
        } else {
            return new \RuntimeException('The image formatter downloads an image from a remote HTTP server. Therefore, it requires that PHP can request remote hosts, either via cURL or fopen()');
        }

        return $fullPath ? $filepath : $filename;
    }

    public static function storedProfilePicture($gender = null, $directory = 'avatars')
    {
        return Storage::disk('public')->putFile($directory, static::profilePicture(null, $gender));
    }
}
