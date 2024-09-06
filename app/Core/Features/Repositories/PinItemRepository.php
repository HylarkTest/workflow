<?php

declare(strict_types=1);

namespace App\Core\Features\Repositories;

use App\Models\Base;
use App\Models\Image;
use Illuminate\Support\Arr;
use Illuminate\Http\UploadedFile;
use App\Core\Features\ImageCropper;
use Illuminate\Support\Facades\Http;
use App\Models\Contracts\FeatureList;
use App\Models\Contracts\FeatureListItem;
use Illuminate\Database\Eloquent\Builder;
use App\Core\Mappings\Features\MappingFeatureType;

/**
 * @extends FeatureItemRepository<\App\Models\Pin, \App\Models\Pinboard>
 */
class PinItemRepository extends FeatureItemRepository
{
    protected function getListOrderByField(): string
    {
        return 'pinboard';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Pin>
     */
    protected function getItemQuery(Base $base): Builder
    {
        return $base->pins()->getQuery();
    }

    /**
     * @param  \App\Models\Pinboard  $list
     * @return \App\Models\Pin
     *
     * @throws \Exception
     */
    protected function createFeatureItemFromAttributes(FeatureList $list, array $data): FeatureListItem
    {
        [$cropInfo, $image] = $this->getImageDetails($data['image']);

        $imageCropper = new ImageCropper($image, $cropInfo);
        $document = Image::createFromFile($imageCropper->isCroppable() ? $imageCropper->cropAndSave() : $image);
        if (! ($data['name'] ?? false)) {
            $data['name'] = $document->filename;
        }

        /** @var \App\Models\Pin $pin */
        $pin = $list->pins()->make($data);
        $pin->image()->associate($document);
        $pin->save();

        return $pin;
    }

    /**
     * @param  \App\Models\Pin  $item
     * @return \App\Models\Pin
     *
     * @throws \Exception
     */
    protected function updateFeatureItemFromAttributes(FeatureListItem $item, array $data): FeatureListItem
    {
        if (isset($data['image'])) {
            [$cropInfo, $image] = $this->getImageDetails($data['image']);
            if (! $item->image->isSame($image) || Arr::hasAny($cropInfo, ['xOffset', 'yOffset', 'width', 'height', 'rotate'])) {
                $imageCropper = new ImageCropper($image, $cropInfo);
                $document = Image::createFromFile($imageCropper->isCroppable() ? $imageCropper->cropAndSave() : $image);
                $item->image()->associate($document);
                unset($data['image']);
            }
        }

        if (isset($data['isFavorite'])) {
            $data['favorited_at'] = $data['isFavorite'] ? now() : null;
            unset($data['isFavorite']);
        }

        $item->fill($data);

        return $item;
    }

    /**
     * @param  \App\Models\Pin  $item
     * @return \App\Models\Pin
     */
    protected function duplicateFeatureItemFromAttributes(FeatureListItem $item, array $data): FeatureListItem
    {
        $document = Image::createFromItem($item->image);
        $item->image()->associate($document);
        $item->name = $item->name.' (Copy)';

        $itemDuplicated = $item->replicate();
        $itemDuplicated->save();

        return $itemDuplicated;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Pinboard>
     */
    protected function getListQuery(Base $base): Builder
    {
        return $base->pinboards()->getQuery();
    }

    protected function getFeatureType(): MappingFeatureType
    {
        return MappingFeatureType::PINBOARD;
    }

    /**
     * @throws \Exception
     */
    protected function getImageDetails(array $dataImage): array
    {
        $cropInfo = $dataImage;
        $image = $dataImage['image'];

        if ($image === null) {
            $image = $this->convertUrlToUploadedFile($cropInfo['url']);
        }
        unset($cropInfo['url']);

        return [$cropInfo, $image];
    }

    /**
     * @throws \Exception
     */
    protected function convertUrlToUploadedFile(string $url): UploadedFile
    {
        $response = Http::get($url);

        if ($response->successful()) {
            $imageContent = $response->body();

            $pathInfo = pathinfo($url);
            $filename = $pathInfo['basename'];

            $temporaryFilePath = sys_get_temp_dir().'/'.$filename;

            file_put_contents($temporaryFilePath, $imageContent);

            register_shutdown_function(function () use ($temporaryFilePath) {
                if (file_exists($temporaryFilePath)) {
                    unlink($temporaryFilePath);
                }
            });

            return new UploadedFile(
                $temporaryFilePath,
                $filename,
                $response->header('Content-Type'),
                null,
                true
            );
        } else {
            throw new \Exception("Could not fetch image from URL: $url");
        }
    }
}
