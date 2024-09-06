<?php

declare(strict_types=1);

namespace App\Console\Commands\DB\Health\Check;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Console\Commands\DB\Health\DBHealthCommand;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Symfony\Component\Console\Output\OutputInterface;
use LaravelUtils\Database\Eloquent\Concerns\AdvancedSoftDeletes;

class SoftDeleteCascadeCheckCommand extends DBHealthCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:health:soft-delete-cascade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure soft deleted models have also deleted
dependent children.';

    protected array $badChildren = [];

    protected function check(OutputInterface $output): int
    {
        /** @var string[] $modelFiles */
        $modelFiles = scandir(app_path('Models'));
        foreach ($modelFiles as $file) {
            /** @var class-string<\App\Models\Model>|string $class */
            $class = '\App\Models\\'.str_replace('.php', '', $file);
            if (! class_exists($class) || ! \in_array(AdvancedSoftDeletes::class, class_uses_recursive($class), true)) {
                continue;
            }
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = (new $class);
            /** @var string[] $relationships */
            $relationships = $this->getDeleteCascadeRelationships($model);
            $class::query()
                ->withCount(collect($relationships)->mapWithKeys(function ($relationship) use ($model) {
                    return [
                        $relationship => fn (Builder $query) => $query->whereColumn(
                            $query->qualifyColumn('base_id'),
                            $model->qualifyColumn('base_id')
                        ),
                    ];
                })->all())
                ->onlyTrashed()
                ->eachById(function (Model $model) use ($relationships) {
                    foreach ($relationships as $relationship) {
                        if ($model->{$relationship.'_count'}) {
                            $this->badChildren[] = [class_basename($model).':'.$model->getKey(), $relationship, $model->{$relationship.'_count'}];
                        }
                    }
                });
        }

        if (! empty($this->badChildren)) {
            $message = 'Found '.$this->numberToFix().' models that should be deleted.';
            $this->error($message);
            $this->table(
                ['Parent class', 'Relationship', 'Count'],
                $this->badChildren
            );

            $this->report($message);
        } else {
            $this->info('All soft deleted models have deleted children.');
        }

        return 0;
    }

    protected function numberToFix(): int
    {
        return \count($this->badChildren);
    }

    protected function fix(bool $confirmFixes, OutputInterface $output): int
    {
        if (empty($this->badChildren)) {
            $this->info('No fixes required.');
        } elseif (! $confirmFixes || $this->confirm('Would you like to delete the children of all soft deleted models?')) {
            foreach ($this->badChildren as [$class, $relationship]) {
                $this->warn("Deleting relationship [[$relationship]] for [[$class]].");
                [$class, $id] = explode(':', $class);
                $fullClass = "\App\Models\\$class";
                $parent = $fullClass::query()
                    ->onlyTrashed()
                    ->findOrFail($id);

                $relation = $parent->{$relationship}();

                $foreignColumn = $relation instanceof BelongsTo
                    ? $relation->getQualifiedOwnerKeyName()
                    : $relation->getQualifiedForeignKeyName();

                $morphColumn = $relation instanceof MorphMany ? $relation->getMorphType() : null;

                $relation->withoutTrashed()
                    ->eachById(function (Model $child) use ($foreignColumn, $morphColumn) {
                        /** @phpstan-ignore-next-line We know child must use AdvancedSoftDelete */
                        $child->deleteBy(
                            $foreignColumn,
                            $morphColumn
                        );
                    });
            }
        }

        return 0;
    }

    protected function getDeleteCascadeRelationships(Model $model): array
    {
        $relationships = [];
        if (property_exists($model, 'deleteCascadeRelationships')) {
            foreach ($model->deleteCascadeRelationships as $key => $value) {
                $relationships[] = \is_int($key) ? $value : $key;
            }
        }

        return $relationships;
    }
}
