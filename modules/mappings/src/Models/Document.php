<?php

declare(strict_types=1);

namespace Mappings\Models;

use Illuminate\Http\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use LighthouseHelpers\Concerns\HasGlobalId;
use Illuminate\Contracts\Filesystem\Filesystem;
use Mappings\Models\Contracts\Document as DocumentInterface;

/**
 * Class Document
 *
 * @property int $id
 * @property string $filename
 * @property int $size
 * @property string $url
 * @property string $extension
 *
 * @method bool exists()
 * @method string|null get()
 * @method \Symfony\Component\HttpFoundation\StreamedResponse download(string|null $name = null, array $headers = [])
 * @method string|false checksum()
 * @method string|false mimeType()
 * @method resource|false readStream()
 */
class Document extends Model implements DocumentInterface
{
    use HasGlobalId;

    /**
     * @var string[]
     */
    protected static array $proxyMethods = [
        'exists',
        'get',
        'download',
        'checksum',
        'mimeType',
        'readStream',
    ];

    protected $table = 'documents';

    protected $casts = [
        'size' => 'int',
    ];

    private static function cloneFile(Model $file): array
    {
        $extension = $file->extension ?? '';
        $filename = Str::random(40);
        $url = static::directory().'/'.$filename.'.'.$extension;
        static::fileSystem()->copy(($file->url ?? ''), $url);

        return [$extension, $url];
    }

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillable(): array
    {
        $fillable = parent::getFillable();

        return array_merge($fillable, [
            'url',
            'size',
            'filename',
            'extension',
            'mime_type',
        ]);
    }

    public function id(): int
    {
        return $this->getKey();
    }

    public function url(): string
    {
        $fileSystem = static::fileSystem();
        try {
            return $fileSystem->temporaryUrl($this->url, now()->addMinutes(5));
        } catch (\RuntimeException) {
            return $fileSystem->url($this->url);
        }
    }

    public function downloadUrl(): string
    {
        return route('download', [static::disk(), $this->url]);
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function isSame(\SplFileInfo $file): bool
    {
        if ($file->getSize() !== $this->size) {
            return false;
        }
        $originalChecksum = $this->checksum();
        $newChecksum = md5_file($file->getRealPath());

        return $originalChecksum === $newChecksum;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    /**
     * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile  $file
     * @param  array<string, mixed>  $attributes
     */
    public static function createFromFile($file, array $attributes = []): self
    {
        $url = static::fileSystem()->putFile(static::directory(), $file);
        $extension = $file->extension();

        if (! $extension) {
            /** @phpstan-ignore-next-line It does exist */
            $extension = pathinfo($file->getClientOriginalName(), \PATHINFO_EXTENSION);
        }
        /** @var \Mappings\Models\Document $document */
        $document = static::query()->forceCreate(array_merge([
            'filename' => $file instanceof File ? $file->getFilename() : $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'extension' => $extension,
            'mime_type' => $file->getMimeType(),
            'url' => $url,
        ], $attributes));

        return $document;
    }

    public static function createFromItem(Model $file, array $attributes = []): self
    {
        [$extension, $url] = self::cloneFile($file);

        /** @var \Mappings\Models\Document $document */
        $document = static::query()->forceCreate(array_merge([
            'filename' => preg_replace('/(\.\w+)$/', '(copy)$1', $file->filename ?? ''),
            'size' => $file->size ?? 0,
            'extension' => $extension,
            'mime_type' => $file->mime_type ?? '',
            'url' => $url,
        ], $attributes));

        return $document;
    }

    /**
     * @param  string  $method
     * @param  array<int, mixed>  $parameters
     */
    public function __call($method, $parameters)
    {
        if (\in_array($method, static::$proxyMethods, true)) {
            return static::fileSystem()->$method($this->url, ...$parameters);
        }

        return parent::__call($method, $parameters);
    }

    protected static function directory(): string
    {
        return 'item-documents';
    }

    protected static function disk(): string
    {
        return Config::get('mappings.filesystems.documents');
    }

    protected static function fileSystem(): Filesystem
    {
        return Storage::disk(static::disk());
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::deleting(static function (self $document) {
            if (! method_exists($document, 'isForceDeleting') || $document->isForceDeleting()) {
                static::fileSystem()->delete($document->url);
            }
        });
    }
}
