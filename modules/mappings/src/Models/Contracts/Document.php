<?php

declare(strict_types=1);

namespace Mappings\Models\Contracts;

use Illuminate\Http\UploadedFile;

interface Document
{
    public function id(): int;

    public function url(): string;

    public function filename(): string;

    public function size(): int;

    public function extension(): string;

    public function isSame(UploadedFile $file): bool;
}
