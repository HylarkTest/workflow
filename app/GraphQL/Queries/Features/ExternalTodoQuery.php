<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Features;

use App\Models\Item;
use App\Models\User;
use GraphQL\Deferred;
use App\GraphQL\AppContext;
use Illuminate\Support\Arr;
use LighthouseHelpers\Utils;
use Illuminate\Support\Carbon;
use App\Models\ExternalTodoable;
use AccountIntegrations\Core\Scope;
use LighthouseHelpers\Core\Mutation;
use AccountIntegrations\Core\Todos\Todo;
use GraphQL\Type\Definition\ResolveInfo;
use App\GraphQL\ExternalAssociationBatchLoader;
use Illuminate\Database\RecordsNotFoundException;
use AccountIntegrations\Models\IntegrationAccount;
use App\Core\Mappings\Features\MappingFeatureType;
use App\GraphQL\Queries\Concerns\AddsAssociations;
use App\GraphQL\Queries\Concerns\InteractsWithIntegratedData;

class ExternalTodoQuery extends Mutation
{
    use AddsAssociations;

    /**
     * @use \App\GraphQL\Queries\Concerns\InteractsWithIntegratedData<\AccountIntegrations\Core\Todos\Todo>
     */
    use InteractsWithIntegratedData {
        getSource as baseGetSource;
    }

    /**
     * @throws \Exception
     */
    public function index(?Item $rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $user = $context->user();

        $node = $rootValue ?? $args['forNode'] ?? null;
        if ($node) {
            return $this->getTodosForNode($node, $args, $user);
        }

        if (! isset($args['todoListId'])) {
            $this->throwValidationException('todoListId', ['The todoListId field is required.']);
        }

        $perPage = $args['first'] ?? 100;
        $args['first'] = $perPage + 1;

        $source = $this->getSource($context, $args);

        $todos = $source->getTodos($args['todoListId'], $args);

        $count = $todos->take($perPage)->count();
        $currentPage = $args['page'] ?? 1;
        $hasMorePages = $count < $todos->count();

        return $this->buildExternalPaginator($todos->take($args['first']), $count, $hasMorePages, $currentPage, $perPage);
    }

    public function resolveAssociations(Todo $todo, array $args, AppContext $context, ResolveInfo $resolveInfo): ?Deferred
    {
        $id = $todo->id;

        return $id ? ExternalAssociationBatchLoader::instanceFromExternal(
            Item::class, ExternalTodoable::class, $todo->account->id
        )->loadAndResolve($id) : null;
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function show($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): Todo
    {
        return $this->getSource($context, $args)->getTodo($args['todoListId'], $args['id']);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function store($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], [
            'name',
            'startAt',
            'dueBy',
            'recurrence',
            'description',
            'location',
            'priority',
            'markers',
        ]);

        $items = $this->getAssociatedItems($context->base(), $args, MappingFeatureType::TODOS);

        $source = $this->getSource($context, $args);

        $todo = $source->createTodo($args['input']['todoListId'], $data);

        foreach ($items as $item) {
            $item->externalTodoables()->updateOrCreate([
                'todo_list_id' => $todo->list->id,
                'todo_id' => $todo->id,
                'integration_account_id' => $source->id,
            ]);
        }

        return $this->mutationResponse(200, 'External todo was created successfully', [
            'todo' => $todo,
            'todoList' => $todo->list,
        ]);
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $data = Arr::only($args['input'], ['id', 'name', 'completedAt', 'startAt', 'dueBy', 'recurrence', 'description', 'location', 'priority', 'markers']);

        $source = $this->getSource($context, $args);

        $todo = $source->updateTodo($args['input']['todoListId'], $data);

        return $this->mutationResponse(200, 'External todo was updated successfully', [
            'todo' => $todo,
            'todoList' => $todo->list,
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

        $source->deleteTodo($args['input']['todoListId'], $args['input']['id']);

        ExternalTodoable::query()->where([
            'integration_account_id' => $source->id,
            'todo_id' => $args['input']['id'],
        ])->delete();

        return $this->mutationResponse(200, 'External todo was deleted successfully');
    }

    /**
     * @param  null  $rootValue
     *
     * @throws \Exception
     */
    public function move($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $source = $this->getSource($context, $args);

        $todo = $source->moveTodo($args['input']['id'], $args['input']['todoListId'], $args['input']['previousId']);

        return $this->mutationResponse(200, 'External todo was updated successfully', [
            'todo' => $todo,
            'todoList' => $todo->list,
        ]);
    }

    /**
     * @param  null  $root
     *
     * @throws \Exception
     */
    public function associate($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        /** @var \App\Models\Item $node */
        $node = Utils::resolveModelFromGlobalId($args['input']['nodeId']);

        $source = $this->getSource($context, $args);

        $todo = $source->getTodo($args['input']['todoListId'], $args['input']['id']);

        $node->externalTodoables()->updateOrCreate([
            'todo_list_id' => $todo->list->id,
            'todo_id' => $todo->id,
            'integration_account_id' => $source->id,
        ]);

        return $this->mutationResponse(200, 'Todo was associated successfully', [
            'todo' => $todo,
        ]);
    }

    /**
     * @param  null  $root
     *
     * @throws \Exception
     */
    public function dissociate($root, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        /** @var \App\Models\Item $node */
        $node = Utils::resolveModelFromGlobalId($args['input']['nodeId']);

        $source = $this->getSource($context, $args);

        $todo = $source->getTodo($args['input']['todoListId'], $args['input']['id']);

        $node->externalTodoables()->where([
            'todo_list_id' => $todo->list->id,
            'todo_id' => $todo->id,
            'integration_account_id' => $source->id,
        ])->delete();

        return $this->mutationResponse(200, 'Todo was dissociated successfully', [
            'todo' => $todo,
        ]);
    }

    public function getTodosForNode(string|Item $node, array $args, User $user): array
    {
        $perPage = $args['first'] ?? 100;
        /** @var \App\Models\Item $node */
        $node = \is_string($node) ? Utils::resolveModelFromGlobalId($node) : $node;
        $todoablesQuery = $node->externalTodoables();
        $total = $todoablesQuery->count();
        $page = $args['page'] ?? 1;

        $externalTodos = null;
        $fetchExternalTodos = function () use ($todoablesQuery, $user, $args, $perPage, $page, &$externalTodos) {
            if ($externalTodos !== null) {
                $count = $externalTodos->take($perPage)->count();
                $hasMorePages = $count < $externalTodos->count();

                return [$count, $hasMorePages, $externalTodos->take($perPage)];
            }
            $externalTodos = collect([]);
            do {
                $todos = $todoablesQuery->offset(($page - 1) * $perPage)->limit($perPage + 1)
                    ->get()
                    ->map(function (ExternalTodoable $todoable) use ($user, $args) {
                        /** @var \AccountIntegrations\Models\IntegrationAccount $source */
                        $source = $user->integrationAccounts->find($todoable->integration_account_id);
                        try {
                            $todo = $source->getTodo($todoable->todo_list_id, $todoable->todo_id);
                        } catch (RecordsNotFoundException) {
                            $todoable->delete();

                            return null;
                        }
                        if (! $this->matchesFilters($todo, $args)) {
                            return null;
                        }

                        return $todo;
                    })->filter();
                $externalTodos = $externalTodos->merge($todos);
                $page++;
            } while ($externalTodos->count() <= $perPage + 1 && $todos->isNotEmpty());

            $count = $externalTodos->take($perPage)->count();
            $hasMorePages = $count < $externalTodos->count();

            return [$count, $hasMorePages, $externalTodos->take($perPage)];
        };

        return $this->buildExternalPaginator(
            fn () => $fetchExternalTodos()[2],
            fn () => $fetchExternalTodos()[0],
            fn () => $fetchExternalTodos()[1],
            $page,
            $perPage,
            $total
        );
    }

    protected function getSource(AppContext $context, array $args): IntegrationAccount
    {
        return $this->baseGetSource($context, $args, Scope::TODOS);
    }

    protected function matchesFilters(Todo $todo, array $args): bool
    {
        if ($args['filter'] === 'ONLY_COMPLETED' && ! $todo->completedAt) {
            return false;
        }
        if ($args['filter'] === 'ONLY_INCOMPLETE' && $todo->completedAt) {
            return false;
        }
        if (isset($args['dueBefore'])) {
            $dueBefore = Carbon::parse($args['dueBefore']);
            if ($todo->dueBy && $todo->dueBy->greaterThan($dueBefore)) {
                return false;
            }
        }
        if (isset($args['dueAfter'])) {
            $dueAfter = Carbon::parse($args['dueAfter']);
            if ($todo->dueBy && $todo->dueBy->lessThan($dueAfter)) {
                return false;
            }
        }

        return true;
    }
}
