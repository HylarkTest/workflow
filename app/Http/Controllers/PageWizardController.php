<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Page;
use App\Models\Action;
use App\Core\Pages\PageType;
use LighthouseHelpers\Utils;
use Markers\Core\MarkerType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\PageWizardRequest;
use App\Core\Bootstrap\PageWizardRepository;
use Illuminate\Validation\ValidationException;
use Nuwave\Lighthouse\Execution\Utils\Subscription;
use LighthouseHelpers\Concerns\BuildsGraphQLResponses;

class PageWizardController extends Controller
{
    use BuildsGraphQLResponses;

    public function __invoke(PageWizardRequest $request): JsonResponse
    {
        $base = tenant();

        $data = $request->pageWizardData();

        $this->validateUsageLimits($base, $data);

        $repository = new PageWizardRepository($base);

        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Page> $newPages */
        $newPages = (new Page)->newCollection();

        DB::transaction(function () use ($data, $repository, &$newPages) {
            Action::batchRecord(function () use ($data, $repository, &$newPages) {
                if ($data['markerGroups'] ?? false) {
                    $repository->buildMarkerGroups($data['markerGroups']);
                }

                if ($data['categories'] ?? false) {
                    $repository->buildCategories($data['categories']);
                }

                if ($data['reusedMarkerGroups'] ?? false) {
                    $repository->reuseMarkerGroups($data['reusedMarkerGroups']);
                }
                if ($data['reusedCategories'] ?? false) {
                    $repository->reuseCategories($data['reusedCategories']);
                }

                if ($data['space'] ?? false) {
                    /** @var \App\Models\Space $space */
                    $space = Utils::resolveModelFromGlobalId($data['space']['id']);

                    if ($data['space']['reusedBlueprints'] ?? false) {
                        $repository->reuseBlueprints($data['space']['reusedBlueprints']);
                    }

                    $repository->buildLists($data['space']['lists'] ?? [], $space);
                    $pages = $data['space']['pages'] ?? [];
                    $mappingInfo = array_filter($pages, fn ($pageData) => ! isset($pageData['subset'])
                        && (PageType::ENTITIES->value === $pageData['pageType']
                        || PageType::ENTITY->value === $pageData['pageType']));

                    $repository->buildMappings($mappingInfo, $space);

                    $newPages = $repository->buildPages($pages, $space);
                    foreach ($newPages as $page) {
                        Subscription::broadcast('pageCreated', $this->mutationResponse(
                            200,
                            'Page created successfully',
                            ['page' => $page],
                        ));
                        Subscription::broadcast('nodeCreated', $this->mutationResponse(
                            200,
                            'Node created successfully',
                            [
                                'node' => $page,
                                'event' => 'pageCreated',
                            ],
                        ));
                    }
                }
            });
        }, 3);

        return new JsonResponse([
            'data' => [
                'pages' => $newPages->map(function (Page $page) {
                    return [
                        'id' => $page->global_id,
                        'name' => $page->name,
                        'symbol' => $page->symbol,
                        'type' => $page->type->value,
                    ];
                }),
            ],
        ], 201);
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
