<?php

declare(strict_types=1);

namespace Mappings\Core\Documents;

use App\Models\Image;
use GraphQL\Deferred;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Config\Repository;
use LighthouseHelpers\Core\ModelBatchLoader;
use Mappings\Core\Documents\Contracts\ImageRepository;
use Mappings\Models\Contracts\Document as DocumentInterface;

class EloquentImageRepository implements ImageRepository
{
    protected Repository $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function store(File|UploadedFile $document): DocumentInterface
    {
        return config('mappings.models.image')::createFromFile($document);
    }

    /**
     * @return \Mappings\Models\Document
     */
    public function find(int $id): DocumentInterface
    {
        /** @var \Mappings\Models\Document $document */
        $document = config('mappings.models.image')::query()->findOrFail($id);

        return $document;
    }

    public function remove(int $id): bool
    {
        $document = $this->find($id);

        try {
            return $document->forceDelete() ?: false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function batchLoad(int $id, ?\Closure $cb = null): Deferred
    {
        return ModelBatchLoader::instanceFromModel(config('mappings.models.image'))
            ->loadAndResolve($id, [], $cb);
    }

    public function removeByUrl(string $url): bool
    {
        $document = $this->findFromUrl($url);

        try {
            return $document?->forceDelete() ?: false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return \Mappings\Models\Document|null
     */
    public function findFromUrl(string $url): ?DocumentInterface
    {
        $directory = preg_quote(Image::directory(), '/');
        preg_match("/\/($directory\/.*)/", $url, $matches);
        $path = $matches[1] ?? '';
        /** @var \Mappings\Models\Image|null $document */
        $document = config('mappings.models.image')::query()->firstWhere('url', $path);

        return $document;
    }
}
