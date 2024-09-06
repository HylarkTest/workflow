<?php

declare(strict_types=1);

namespace Mappings\Core\Documents\Contracts;

use GraphQL\Deferred;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Mappings\Models\Contracts\Document;

interface DocumentRepository
{
    public function store(File|UploadedFile $document): Document;

    public function find(int $id): Document;

    public function findFromUrl(string $url): ?Document;

    public function remove(int $id): bool;

    public function removeByUrl(string $url): bool;

    public function batchLoad(int $id, ?\Closure $cb = null): Deferred;
}
