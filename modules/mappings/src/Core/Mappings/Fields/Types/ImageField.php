<?php

declare(strict_types=1);

namespace Mappings\Core\Mappings\Fields\Types;

use App\Core\Clamav;
use App\Models\Image;
use GraphQL\Deferred;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;
use App\Core\Features\ImageCropper;
use App\Exceptions\ClamavException;
use Mappings\Models\Contracts\Document;
use Mappings\Core\Mappings\Fields\Field;
use Illuminate\Contracts\Translation\Translator;
use Mappings\Core\Documents\Contracts\ImageRepository;
use Mappings\Core\Mappings\Fields\Contracts\MultipleTypeField;

/**
 * @phpstan-type ImageValue = array{
 *     filename: string,
 *     url: string,
 *     size: int|null,
 *     extension: string|null,
 *     originalUrl?: string,
 *     width?: int|null,
 *     height?: int|null,
 *     xOffset?: int|null,
 *     yOffset?: int|null,
 * }
 *
 * @phpstan-import-type FieldOptions from \Mappings\Core\Mappings\Fields\Field
 */
class ImageField extends Field implements MultipleTypeField
{
    public const MAX_SIZE = 2000;

    public static string $type = 'IMAGE';

    /**
     * @param  FieldOptions  $field
     */
    public function __construct(array $field, Translator $translator, protected ImageRepository $documents)
    {
        if ($extensions = $field['options']['rules']['extensions'] ?? false) {
            $field['options']['rules']['extensions'] = array_map(static function ($extension) {
                return mb_strtolower(trim($extension, '.'));
            }, $extensions);
        }

        parent::__construct($field, $translator);
    }

    public static function possibleTypes(): array
    {
        return ['CroppedImage', 'ItemImage'];
    }

    public static function possibleInputTypes(): array
    {
        return ['CroppedImageInput'];
    }

    public function graphQLType(string $prefix): string
    {
        return $this->isCroppable() ? 'CroppedImage' : 'ItemImage';
    }

    public function graphQLInputType(string $prefix): string
    {
        return 'CroppedImageInput';
    }

    public function fieldValueSubRules(bool $isCreate): array
    {
        $imageRules = ['nullable', 'image'];

        $max = static::MAX_SIZE;

        if ($customMax = $this->rule('max')) {
            $max = $customMax < $max ? $customMax : $max;
        }

        $imageRules[] = ['max', $max];

        if ($this->rule('extensions')) {
            $imageRules[] = ['mimes', ...$this->rule('extensions')];
        }

        return [
            'image' => $imageRules,
            'url' => ['nullable'],
            'width' => ['nullable', 'integer'],
            'height' => ['nullable', 'integer'],
            'xOffset' => ['nullable', 'integer'],
            'yOffset' => ['nullable', 'integer'],
            'rotate' => ['integer', 'min:0', 'max:360'],
        ];
    }

    public function attributes(): array
    {
        $attributes = parent::attributes();
        $attributes['fieldValue.image'] = "\"$this->name\" file";
        $attributes['fieldValue.width'] = "\"$this->name\" width";
        $attributes['fieldValue.height'] = "\"$this->name\" height";
        $attributes['fieldValue.xOffset'] = "\"$this->name\" X offset";
        $attributes['fieldValue.yOffset'] = "\"$this->name\" y offset";
        $attributes['fieldValue.rotate'] = "\"$this->name\" rotation";

        return $attributes;
    }

    /**
     * @param  FieldOptions  $data
     * @return ValidationRules
     */
    public function optionRules(array $data): array
    {
        return array_merge(parent::optionRules($data), [
            'rules.max' => ['integer', ['max', static::MAX_SIZE]],
            'rules.extensions' => ['array', Rule::in(['jpeg', 'png', 'gif', 'bmp', 'webp'])],
            'croppable' => 'bool',
            'primary' => ['bool', Rule::excludeIf((bool) ($data['options']['list'] ?? false))],
        ]);
    }

    public function resolveSingleValue($value, array $args): array|Deferred
    {
        $image = $value['image'];

        $isUrl = \is_string($image);

        if ($isUrl) {
            return [
                'filename' => $image,
                'url' => $image,
                'size' => null,
                'extension' => null,
                ...($this->isCroppable() ? [
                    'originalUrl' => $image,
                    'width' => $value['width'] ?? null,
                    'height' => $value['height'] ?? null,
                    'xOffset' => $value['xOffset'] ?? 0,
                    'yOffset' => $value['yOffset'] ?? 0,
                ] : []),
            ];
        }

        $originalImageLoad = $this->documents->batchLoad($value['originalImage'] ?? $image, fn (?Document $document) => $document?->url());

        return $this->documents->batchLoad($image, function (?Document $document) use ($value, $originalImageLoad) {
            if (! $document) {
                return null;
            }

            return [
                'filename' => $document->filename(),
                'url' => $document->url(),
                'size' => $document->size(),
                'extension' => $document->extension(),
                ...($this->isCroppable() ? [
                    'originalUrl' => $originalImageLoad,
                    'width' => $value['width'] ?? null,
                    'height' => $value['height'] ?? null,
                    'xOffset' => $value['xOffset'] ?? 0,
                    'yOffset' => $value['yOffset'] ?? 0,
                ] : []),
            ];
        });
    }

    public function canBeSorted(): bool
    {
        return false;
    }

    /**
     * @param  mixed  $value
     * @param  mixed|null  $originalValue
     * @return array|int
     */
    public function prepareForSerialization($value, $originalValue = null)
    {
        $tenant = tenant();
        if ($this->isCroppable()) {
            if (($value['url'] ?? null) && ! ($value['image'] ?? null)) {
                $url = $value['url'];
                $info = pathinfo((string) strtok($url, '?')); // Remove query string
                $contents = $this->getFileContentsWithHeader($url);
                $path = '/tmp/'.$info['basename'];
                file_put_contents($path, $contents);
                $uploadedFile = new UploadedFile($path, $info['basename']);
                try {
                    resolve(Clamav::class)->check($uploadedFile);
                } catch (ClamavException $e) {
                    unlink($path);
                    throw $e;
                }
            } else {
                $uploadedFile = $value['image'];
            }
            if ($originalValue) {
                $originalImage = $this->documents->find($originalValue['originalImage']);
                if ($originalImage->isSame($uploadedFile)) {
                    $original = $originalImage;
                    $uploadedFile = new UploadedFile(
                        $uploadedFile->getPathname(),
                        $originalImage->filename(),
                        $uploadedFile->getClientMimeType(),
                    );
                    if ($originalValue['width'] === $value['width'] && $originalValue['height'] === $value['height'] && $originalValue['xOffset'] === $value['xOffset'] && $originalValue['yOffset'] === $value['yOffset']) {
                        return $originalValue;
                    }
                    $document = $this->cropAndSave($uploadedFile, $value);

                    return [
                        'image' => $document->id(),
                        'originalImage' => $original->id(),
                        'width' => $value['width'],
                        'height' => $value['height'],
                        'xOffset' => $value['xOffset'],
                        'yOffset' => $value['yOffset'],
                    ];
                }
                // Because this is sent after the response there is no tenant
                // initialized in the job.
                $tenantForJob = $tenant->withoutRelations();
                dispatch(fn () => $tenantForJob->run(function () use ($originalValue) {
                    $repository = resolve(ImageRepository::class);
                    $repository->remove($originalValue['image']);
                    $repository->remove($originalValue['originalImage']);
                }))->afterResponse();
            }

            $original = $this->documents->store($uploadedFile);
            $document = $this->cropAndSave($uploadedFile, $value);

            return [
                'image' => $document->id(),
                'originalImage' => $original->id(),
                'width' => $value['width'],
                'height' => $value['height'],
                'xOffset' => $value['xOffset'],
                'yOffset' => $value['yOffset'],
            ];
        }

        $uploadedFile = $value['image'];
        if ($originalValue) {
            $originalImage = $this->documents->find($originalValue['image']);
            // If they upload the same image then we don't change anything.
            if ($originalImage->isSame($uploadedFile)) {
                return $originalValue;
            }
            dispatch(fn () => $tenant->run(fn () => resolve(ImageRepository::class)->remove($originalValue['image'])))->afterResponse();
        }
        $document = $this->cropAndSave($uploadedFile, $value);

        return ['image' => $document->id()];
    }

    protected function cropAndSave(UploadedFile $uploadedFile, array $cropInfo): Document
    {
        $imageCropper = new ImageCropper($uploadedFile, $cropInfo);
        $imageFile = $imageCropper->isCroppable() ? $imageCropper->cropAndSave() : $uploadedFile;

        return $this->documents->store($imageFile);
    }

    protected function isCroppable(): bool
    {
        return $this->option('croppable', false);
    }

    protected function getFileContentsWithHeader(string $url): false|string
    {
        $context = stream_context_create([
            'http' => [
                'Referer' => config('app.url'),
                'user_agent' => config('app.user_agent'),
            ],
        ]);

        return file_get_contents($url, false, $context);
    }
}
