<?php

declare(strict_types=1);

namespace App\Core\Imports;

use Illuminate\Http\UploadedFile;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Contracts\Filesystem\Filesystem;

class ImportFileRepository
{
    public function __construct(protected FilesystemManager $storage) {}

    protected function fileSystem(): Filesystem
    {
        return $this->storage->disk($this->getImportsDisk());
    }

    public function storeTemporaryFile(UploadedFile $file): string
    {
        // Get the checksum of the file
        $id = md5_file($file->path());
        $id .= '.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('/imports', $id, ['disk' => $this->getImportsDisk()]);
        if ($path === false) {
            throw new \RuntimeException('Could not store file');
        }

        return $id;
    }

    public function getFile(string $id): ?string
    {
        return $this->fileSystem()->get($this->getFilePath($id));
    }

    public function getFilePath(string $id): string
    {
        return "imports/$id";
    }

    public function deleteFile(string $fileId): void
    {
        $path = $this->getFilePath($fileId);
        $this->fileSystem()->delete($path);
    }

    public function getImportsDisk(): string
    {
        return config('hylark.imports.disk');
    }
}
