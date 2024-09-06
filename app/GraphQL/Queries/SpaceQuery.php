<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Space;
use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use LighthouseHelpers\Core\Mutation;
use Lampager\Laravel\PaginationResult;
use GraphQL\Type\Definition\ResolveInfo;
use LighthouseHelpers\Pagination\Cursor;
use LighthouseHelpers\Pagination\CursorProcessor;

class SpaceQuery extends Mutation
{
    /**
     * @param  null  $rootValue
     *
     * @throws \JsonException
     */
    public function index($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): PaginationResult
    {
        $base = $context->base();

        $query = $base->spaces();

        $first = $args['first'];

        $total = $query->toBase()->getCountForPagination();

        /** @var \Lampager\Laravel\Paginator $query */
        $query = $query->lampager();

        $query = $query->useProcessor(CursorProcessor::class)
            ->orderBy('id')
            ->forward()
            ->limit($first)
            ->seekable()
            ->exclusive();

        $paginator = $query->paginate(Cursor::decode($args));
        $paginator->total = $total;

        return $paginator;
    }

    /**
     * @param  null  $rootValue
     */
    public function show($rootValue, array $args, AppContext $context): Space
    {
        $base = $context->base();

        /** @var \App\Models\Space $space */
        $space = $base->spaces()->findOrFail($args['id']);

        return $space;
    }

    /**
     * @param  null  $rootValue
     */
    public function store($rootValue, array $args, AppContext $context): array
    {
        $base = $context->base();

        if (! $base->accountLimits()->canCreateSpaces()) {
            $this->throwValidationException('limit', trans('validation.exceeded'));
        }

        $data = Arr::only($args['input'], ['name']);

        /** @var \App\Models\Space $space */
        $space = $base->spaces()->create($data);

        $space->createDefaultNotebook();
        $space->createDefaultTodoList();
        $space->createDefaultDrives();
        $space->createDefaultCalendar();
        $space->createDefaultLinkList();
        $space->createDefaultPinboard();

        return $this->mutationResponse(200, 'Space created successfully', [
            'space' => $space,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function update($rootValue, array $args, AppContext $context): array
    {
        $base = $context->base();

        $data = Arr::only($args['input'], ['name']);

        /** @var \App\Models\Space $space */
        $space = $base->spaces()->findOrFail($args['input']['id']);

        $space->update($data);

        return $this->mutationResponse(200, 'Space updated successfully', [
            'space' => $space,
        ]);
    }

    /**
     * @param  null  $rootValue
     */
    public function destroy($rootValue, array $args, AppContext $context): array
    {
        $base = $context->base();

        /** @var \App\Models\Space $space */
        $space = $base->spaces()->findOrFail($args['input']['id']);

        if ($base->spaces()->count() <= 1) {
            $this->throwValidationException('input.id', ['The last space cannot be deleted.']);
        }

        $space->delete();

        return $this->mutationResponse(200, 'Space deleted successfully');
    }
}
