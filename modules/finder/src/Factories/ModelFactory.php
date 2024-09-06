<?php

declare(strict_types=1);

namespace Finder\Factories;

use Finder\Finder;
use Finder\Builder;
use Illuminate\Support\Str;
use Finder\GloballySearchable;
use Elastic\Adapter\Search\Hit;
use Illuminate\Support\Collection;
use Elastic\Adapter\Search\SearchResult;
use Finder\Core\FinderKeyResolverInterface;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ModelFactory implements ModelFactoryInterface
{
    public function __construct(protected FinderKeyResolverInterface $keyResolver) {}

    /**
     * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>
     */
    public function makeFromSearchResponse(
        SearchResult $searchResult,
        Builder $builder
    ): Collection {
        if (! $searchResult->total()) {
            return Finder::newCollection();
        }

        $documentIds = $this->pluckDocumentIdsFromSearchResponse($searchResult);
        $cursors = $this->pluckDocumentCursorsFromSearchResponse($searchResult);
        $highlights = $this->pluckDocumentHighlightsFromSearchResponse($searchResult);

        $models = $this->getFinderModelsByIds($documentIds, $builder);

        $filteredModels = $this->filterModels($models, $documentIds);

        $sortedModels = $this->sortModels($filteredModels, $documentIds);

        foreach ($sortedModels as $model) {
            $key = $model->getFinderKey();
            if ($cursor = $cursors[$key]) {
                $model->setAttribute('cursor', $cursor);
            }
            if ($highlight = $highlights[$key]) {
                $model->setAttribute('pivot', new Pivot(['highlights' => $highlight]));
            }
        }

        return $sortedModels;
    }

    /**
     * @param  array<int, string>  $ids
     * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>
     */
    protected function getFinderModelsByIds(array $ids, Builder $builder): Collection
    {
        /** @var array<class-string<\Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>, array<int, string|int>> $groupedIds */
        $groupedIds = [];

        foreach ($ids as $id) {
            [$class, $id] = $this->keyResolver->extractClassAndIdFromKey($id, $builder->index);
            if (isset($groupedIds[$class])) {
                $groupedIds[$class][] = $id;
            } else {
                $groupedIds[$class] = [$id];
            }
        }

        $fetchedModels = Finder::newCollection();

        /**
         * @var class-string<\Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable> $class
         * @var array<int, int|string> $primaryKeys
         */
        foreach ($groupedIds as $class => $primaryKeys) {
            $model = new $class;
            $query = $class::usesSoftDelete()
                /** @phpstan-ignore-next-line We know it has the method if the above check is true */
                ? $model->withTrashed() : $model->newQuery();

            if (isset($builder->queryCallback)) {
                \call_user_func($builder->queryCallback, $query);
            }

            $whereIn = \in_array($model->getKeyType(), ['int', 'integer'], true) ?
                'whereIntegerInRaw' :
                'whereIn';

            $fetchedModels = $fetchedModels->merge($query->{$whereIn}(
                $model->getFinderKeyName(), $primaryKeys
            )->get());
        }

        return $fetchedModels;
    }

    private function pluckDocumentIdsFromSearchResponse(SearchResult $searchResponse): array
    {
        return $searchResponse->hits()->map(static function (Hit $hit) {
            return $hit->document()->id();
        })->all();
    }

    private function pluckDocumentCursorsFromSearchResponse(SearchResult $searchResponse): array
    {
        /** @var \Illuminate\Support\Collection<int, \Elastic\Adapter\Search\Hit> $hits */
        $hits = $searchResponse->hits();

        return $hits->flatMap(static function (Hit $hit) {
            $id = $hit->document()->id();
            $sort = $hit->raw()['sort'] ?? null;

            return [$id => $sort];
        })->all();
    }

    private function pluckDocumentHighlightsFromSearchResponse(SearchResult $searchResponse): array
    {
        /** @var \Illuminate\Support\Collection<int, \Elastic\Adapter\Search\Hit> $hits */
        $hits = $searchResponse->hits();

        return $hits->flatMap(static function (Hit $hit) {
            $document = $hit->document();
            $id = $document->id();
            $raw = $hit->raw();
            $highlights = $raw['highlight'] ?? null;
            $mappedHighlights = collect([]);
            foreach ($highlights ?? [] as $field => $subHighlights) {
                foreach ($subHighlights as $highlight) {
                    $value = htmlspecialchars_decode(strip_tags($highlight));
                    $field = str_replace('.text', '', $field);
                    $content = $document->content($field);
                    if (! array_is_list($content)) {
                        $content = [$content];
                    }

                    $items = [];
                    foreach ($content as $item) {
                        if (\is_array($item['text'])) {
                            foreach ($item['text'] as $index => $text) {
                                if (Str::contains($text, $value)) {
                                    $items[] = [
                                        'text' => $text,
                                        'map' => $item['map'].'.'.$index,
                                    ];
                                }
                            }
                        } elseif (Str::contains($item['text'], $value)) {
                            $items[] = $item;
                        }
                    }

                    foreach ($items as $item) {
                        $mappedHighlight = $mappedHighlights[$item['map']] ?? null;
                        $startOfHighlight = mb_strpos($item['text'], $value, $mappedHighlight['end'] ?? 0);
                        $endOfHighlight = $startOfHighlight + mb_strlen($value);
                        if (! $mappedHighlight) {
                            $mappedHighlights[$item['map']] = [
                                'highlight' => [($startOfHighlight === 0 ? '' : '...').$highlight],
                                'path' => $item['map'],
                                'end' => $endOfHighlight,
                                'original' => $item['text'],
                            ];
                        }
                        // This commented out code could be useful in the future.
                        // If you wanted to include all the highlights in the
                        // search results, this adds them to the array. At the
                        // moment, that is a lot of highlights, and makes the
                        // field quite long, so we are just sticking with the
                        // first highlight.
                        /*
                        else {
                            if ($mappedHighlight['end'] === $startOfHighlight || $mappedHighlight['end'] + 1 === $startOfHighlight) {
                                $lastHighlight = array_pop($mappedHighlight['highlight']);
                                $delimiter = $mappedHighlight['end'] === $startOfHighlight ? '' : $item['text'][$mappedHighlight['end']];
                                $mappedHighlight['highlight'][] = $lastHighlight.$delimiter.$highlight;
                            } else {
                                $mappedHighlight['highlight'][] = $highlight;
                            }
                            $mappedHighlight['end'] = $endOfHighlight;
                            $mappedHighlights[$item['map']] = $mappedHighlight;
                        }
                        */
                    }
                }
            }

            $joinedHighlights = $mappedHighlights->values()
                ->map(function (array $highlight) {
                    return [
                        'path' => $highlight['path'],
                        'highlight' => implode('...', $highlight['highlight']).($highlight['end'] < mb_strlen($highlight['original']) ? '...' : ''),
                    ];
                })->all();

            return [$id => $joinedHighlights];
        })->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>  $models
     * @param  array<int, string>  $documentIds
     * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>
     */
    private function filterModels($models, array $documentIds)
    {
        return $models->filter(static function (GloballySearchable $model) use ($documentIds): bool {
            return \in_array($model->getFinderKey(), $documentIds, true);
        })->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>  $models
     * @param  array<int, string>  $documentIds
     * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>
     */
    private function sortModels($models, array $documentIds)
    {
        $documentIdPositions = array_flip($documentIds);

        return $models->sortBy(static function (GloballySearchable $model) use ($documentIdPositions) {
            return $documentIdPositions[$model->getFinderKey()];
        })->values();
    }
}
