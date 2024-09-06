<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasImage
{
    public function updateImage(?UploadedFile $image, string $column, string $path, string $disk = 'images'): void
    {
        if ($image) {
            $filename = $image->store($path, ['disk' => $disk]);
            if (! $filename) {
                throw new \Exception('Could not store avatar');
            }

            if ($this->$column) {
                Storage::disk($disk)->delete($this->$column);
            }
            $this->$column = $filename;
        } elseif ($this->$column) {
            Storage::disk($disk)->delete($this->$column);
            $this->$column = null;
        }
    }
}
