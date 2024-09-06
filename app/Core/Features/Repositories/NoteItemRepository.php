<?php

declare(strict_types=1);

namespace App\Core\Features\Repositories;

use App\Models\Base;
use Illuminate\Support\Arr;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use App\Models\Contracts\FeatureList;
use Intervention\Image\Facades\Image;
use App\Models\Contracts\FeatureListItem;
use Illuminate\Database\Eloquent\Builder;
use App\Core\Mappings\Features\MappingFeatureType;
use Mappings\Core\Documents\Contracts\ImageRepository;

/**
 * @extends FeatureItemRepository<\App\Models\Note, \App\Models\Notebook>
 */
class NoteItemRepository extends FeatureItemRepository
{
    protected function getListOrderByField(): string
    {
        return 'notebook';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Note>
     */
    protected function getItemQuery(Base $base): Builder
    {
        return $base->notes()->getQuery();
    }

    protected function getSearchFields(): array
    {
        return ['name'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Notebook>
     */
    protected function getListQuery(Base $base): Builder
    {
        return $base->notebooks()->getQuery();
    }

    protected function getFeatureType(): MappingFeatureType
    {
        return MappingFeatureType::NOTES;
    }

    /**
     * @param  \App\Models\Notebook  $list
     * @return \App\Models\Note
     */
    protected function createFeatureItemFromAttributes(FeatureList $list, array $data): FeatureListItem
    {
        $contentKey = Arr::first(['tiptap', 'delta', 'html', 'markdown', 'plaintext'], fn ($key) => Arr::has($data, $key));
        /** @var array<string, mixed>|string $content */
        $content = $data[$contentKey];
        $newImages = $this->getImagesFromContent($content);
        $data[$contentKey] = $this->createImagesFromNote($newImages, $content);
        try {
            return parent::createFeatureItemFromAttributes($list, $data);
        } catch (\Exception $e) {
            $this->removeImages($newImages);
            throw $e;
        }
    }

    /**
     * @param  \App\Models\Note  $item
     * @return \App\Models\Note
     */
    protected function updateFeatureItemFromAttributes(FeatureListItem $item, array $data): FeatureListItem
    {
        $contentKey = Arr::first(['tiptap', 'delta', 'html', 'markdown', 'plaintext'], fn ($key) => Arr::has($data, $key));
        if ($contentKey) {
            $oldImages = $this->getImagesFromContent($item->tiptap->tiptap);
            $newImages = $this->getImagesFromContent($data[$contentKey]);
            $removedImages = $oldImages->diff($newImages);
            $addedImages = $newImages->diff($oldImages);
            /** @var array<string, mixed>|string $content */
            $content = $data[$contentKey];
            $data[$contentKey] = $this->createImagesFromNote($addedImages, $content);
        } else {
            $removedImages = collect();
            $addedImages = collect();
        }
        try {
            $result = parent::updateFeatureItemFromAttributes($item, $data);
        } catch (\Exception $e) {
            $this->removeImages($addedImages);
            throw $e;
        }
        $this->removeImages($removedImages);

        return $result;
    }

    /**
     * @param  string|array<array-key, mixed>  $content
     * @return \Illuminate\Support\Collection<array-key, string>
     */
    protected function getImagesFromContent($content): Collection
    {
        $imageUrl = preg_quote(config('filesystems.disks.images.url'), '/');
        if (is_string($content)) {
            preg_match_all("/(data:image\/[^;]+;base64,|$imageUrl)/", $content, $matches);

            return collect($matches[0]);
        }

        return collect($content)
            ->dot()
            ->filter(function ($value) use ($imageUrl) {
                return is_string($value)
                    && preg_match("/^(data:image\/[^;]+;base64,|$imageUrl)/", $value);
            });
    }

    /**
     * @template T of array<string, mixed>|string
     *
     * @param  \Illuminate\Support\Collection<array-key, string>  $images
     * @param  T  $content
     * @return T
     */
    protected function createImagesFromNote($images, $content)
    {
        foreach ($images as $key => $value) {
            if (is_string($value) && preg_match('/^data:image\/[^;]+;base64,/', $value)) {
                $image = Image::make($value);
                $image->save(sys_get_temp_dir().'/'.uniqid());
                $file = new UploadedFile($image->dirname.'/'.$image->basename, $image->basename ?: '', $image->mime);
                $imageModel = resolve(ImageRepository::class)->store($file);
                $image->destroy();
                if (is_string($content)) {
                    $content = preg_replace("/$value/", $imageModel->url(), $content);
                } else {
                    Arr::set($content, $key, $imageModel->url());
                }
            }
        }

        return $content;
    }

    /**
     * @param  \Illuminate\Support\Collection<array-key, string>  $images
     */
    protected function removeImages(Collection $images): void
    {
        $images->each(function (string $url) {
            resolve(ImageRepository::class)->removeByUrl($url);
        });
    }
}
