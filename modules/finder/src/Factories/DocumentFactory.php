<?php

declare(strict_types=1);

namespace Finder\Factories;

use Finder\GloballySearchable;
use Illuminate\Support\Collection;
use Elastic\Adapter\Documents\Document;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class DocumentFactory implements DocumentFactoryInterface
{
    /**
     * @param  \Illuminate\Support\Collection<int, \Finder\GloballySearchable&\Illuminate\Database\Eloquent\Model>  $models
     * @return \Illuminate\Support\Collection<int, \Elastic\Adapter\Documents\Document>
     */
    public function makeFromModels(Collection $models): Collection
    {
        $models->groupBy(fn (GloballySearchable $model): string => $model->finderTypename())
            ->each(function (Collection $models) {
                if ($searchableWith = $models->first()?->globallySearchableWith()) {
                    (new EloquentCollection($models))->loadMissing($searchableWith);
                }
            });

        return $models->map(static function (GloballySearchable $model) {
            if (
                \in_array(SoftDeletes::class, class_uses_recursive($model::class), true)
                && config('finder.soft_delete', false)
            ) {
                $model->pushSoftDeleteFinderMetadata();
            }
            $model->withFinderMetaData('__typename', $model->finderTypename());
            $model->withFinderMetaData('created_at', $model->{$model->getCreatedAtColumn()});
            $model->withFinderMetaData('updated_at', $model->{$model->getUpdatedAtColumn()});

            $documentId = $model->getFinderKey();
            $documentContent = array_merge($model->finderMetadata(), $model->toGloballySearchableArray());

            if (\array_key_exists('_id', $documentContent)) {
                throw new \UnexpectedValueException(sprintf('_id is not allowed in the document content. Please, make sure the field is not returned by the %1$s::toSearchableArray or %1$s::scoutMetadata methods.', class_basename($model)));
            }

            return new Document($documentId, $documentContent);
        });
    }
}
