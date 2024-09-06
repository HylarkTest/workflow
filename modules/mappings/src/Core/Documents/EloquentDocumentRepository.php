<?php

declare(strict_types=1);

namespace Mappings\Core\Documents;

use GraphQL\Deferred;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Config\Repository;
use LighthouseHelpers\Core\ModelBatchLoader;
use Mappings\Core\Documents\Contracts\DocumentRepository;
use Mappings\Models\Contracts\Document as DocumentInterface;

class EloquentDocumentRepository implements DocumentRepository
{
    protected Repository $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function store(File|UploadedFile $document): DocumentInterface
    {
        return config('mappings.models.document')::createFromFile($document);
    }

    /**
     * @return \Mappings\Models\Document
     */
    public function find(int $id): DocumentInterface
    {
        /** @var \Mappings\Models\Document $document */
        $document = config('mappings.models.document')::query()->findOrFail($id);

        return $document;
    }

    public function remove(int $id): bool
    {
        $document = $this->find($id);

        try {
            return $document->delete() ?: false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function batchLoad(int $id, ?\Closure $cb = null): Deferred
    {
        return ModelBatchLoader::instanceFromModel(config('mappings.models.document'))
            ->loadAndResolve($id, [], $cb);
    }

    public function removeByUrl(string $url): bool
    {
        $document = $this->findFromUrl($url);

        try {
            return $document?->delete() ?: false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return \Mappings\Models\Document|null
     */
    public function findFromUrl(string $url): ?DocumentInterface
    {
        $path = parse_url($url, PHP_URL_PATH);
        /** @var \Mappings\Models\Document|null $document */
        $document = config('mappings.models.document')::query()->firstWhere('url', $path);

        return $document;
    }
}
