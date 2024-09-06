<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use AccountIntegrations\Core\Scope;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;
use AccountIntegrations\Models\IntegrationAccount;
use App\GraphQL\Queries\Concerns\InteractsWithIntegratedData;

class ExternalTodoListQuery extends Mutation
{
    /**
     * @use \App\GraphQL\Queries\Concerns\InteractsWithIntegratedData<\AccountIntegrations\Core\Todos\TodoList>
     */
    use InteractsWithIntegratedData {
        getSource as baseGetSource;
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function index($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $perPage = $args['first'] ?? 100;

        $source = $this->getSource($context, $args);

        $lists = $source->getTodoLists();

        $count = $lists->count();
        $currentPage = $args['page'] ?? 1;
        $firstItem = $count > 0 ? ($currentPage - 1) * $perPage + 1 : null;
        $lastItem = $count > 0 ? $firstItem + $count - 1 : null;

        return [
            'data' => $lists,
            'paginatorInfo' => [
                'count' => $count,
                'currentPage' => $currentPage,
                'firstItem' => $firstItem,
                'lastItem' => $lastItem,
                'perPage' => $perPage,
            ],
        ];
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], ['name']);

        $source = $this->getSource($context, $args);

        $list = $source->createTodoList($data['name']);

        return $this->mutationResponse(200, 'External todo list was created successfully', [
            'todoList' => $list,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $source = $this->getSource($context, $args);

        $list = $source->updateTodoList($args['input']['id'], $args['input']['name']);

        return $this->mutationResponse(200, 'External todo list was updated successfully', [
            'todoList' => $list,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function destroy($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $source = $this->getSource($context, $args);

        $source->deleteTodoList($args['input']['id']);

        return $this->mutationResponse(200, 'External todo list was deleted successfully');
    }

    protected function getSource(AppContext $context, array $args): IntegrationAccount
    {
        return $this->baseGetSource($context, $args, Scope::TODOS);
    }
}
