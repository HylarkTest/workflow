<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Base;
use App\Models\Space;
use App\Models\Action;
use App\Models\Marker;
use Illuminate\Support\Arr;
use App\Core\Pages\PageType;
use Markers\Core\MarkerType;
use Actions\Core\ActionRecorder;
use App\Core\Bootstrap\PageWizardRepository;
use Illuminate\Validation\ValidationException;

class BaseRepository
{
    protected PageWizardRepository $pageWizardRepository;

    protected Base $base;

    public function bootstrapBase(Base $base, array $baseData): void
    {
        $this->base = $base;
        $this->pageWizardRepository = new PageWizardRepository($this->base);

        Action::batchRecord(function () use ($baseData) {
            $baseCreateAction = $this->base->createAction;
            Action::withParent($baseCreateAction, function () use ($baseData) {
                $adminTag = $this->base->createDefaultTags();
                if ($baseData['markerGroups'] ?? false) {
                    $this->pageWizardRepository->buildMarkerGroups($baseData['markerGroups']);
                }

                if ($baseData['categories'] ?? false) {
                    $this->pageWizardRepository->buildCategories($baseData['categories']);
                }

                if ($baseData['spaces'] ?? false) {
                    $this->buildSpaces($baseData['spaces'], $adminTag);
                } else {
                    $this->base->createDefaultEntries($adminTag);
                }
            });
        });
    }

    protected function buildSpaces(array $spaces, Marker $adminTag): void
    {
        /** @var \App\Models\Space|null $defaultSpace */
        $defaultSpace = $this->base->spaces->first();
        $replaceDefaultSpace = $defaultSpace && $defaultSpace->updated_at->eq($defaultSpace->created_at);
        if ($replaceDefaultSpace) {
            ActionRecorder::withoutRecording(fn () => $defaultSpace->forceDelete());
        }
        foreach ($spaces as &$data) {
            /** @var \App\Models\Space $space */
            $space = $this->base->spaces()->create(
                Arr::only($data, ['name', 'description']),
            );

            $data['space'] = $space;
        }
        unset($data);

        $this->base->createDefaultEntries($adminTag);

        foreach ($spaces as $data) {
            /** @var \App\Models\Space $space */
            $space = $data['space'];

            $this->pageWizardRepository->buildLists($data['lists'] ?? [], $space);

            if ($data['pages'] ?? false) {
                $this->buildPages($space, $data['pages']);
            }
        }
    }

    protected function buildPages(Space $space, array $pageInfo): void
    {
        $mappingInfo = array_filter($pageInfo, fn ($pageData) => ! isset($pageData['subset'])
            && (PageType::ENTITIES->value === $pageData['pageType']
            || PageType::ENTITY->value === $pageData['pageType']));

        $this->pageWizardRepository->buildMappings($mappingInfo, $space);

        $this->pageWizardRepository->buildPages($pageInfo, $space);
    }

    protected function validateUsageLimits(Base $base, array $requestData): void
    {
        $limits = $base->accountLimits();

        $validationErrors = [];

        /** @phpstan-ignore-next-line */
        $markerGroupsToCreate = collect($requestData['markerGroups'] ?? []);
        foreach (MarkerType::cases() as $type) {
            $markersOfType = $markerGroupsToCreate->where('type', $type->value);

            if (! $limits->canCreateMarkers($type, $markersOfType->count())) {
                $validationErrors[] = 'You have reached the limit of markers you can create.';
                break;
            }
        }

        $categoriesToCreate = $requestData['categories'] ?? [];
        if (! $limits->canCreateCategories(\count($categoriesToCreate))) {
            $validationErrors[] = 'You have reached the limit of categories you can create.';
        }

        $pagesToCreate = $requestData['space']['pages'] ?? [];
        if (! $limits->canCreatePages(\count($pagesToCreate))) {
            $validationErrors[] = 'You have reached the limit of pages you can create.';
        }

        if ($validationErrors) {
            throw ValidationException::withMessages(['limit' => $validationErrors]);
        }
    }
}
