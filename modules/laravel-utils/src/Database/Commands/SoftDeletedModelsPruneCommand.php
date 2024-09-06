<?php

declare(strict_types=1);

namespace LaravelUtils\Database\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletedModelsPruneCommand extends Command
{
    protected $signature = 'soft-deleted:prune';

    protected $description = 'Prune soft deleted models';

    public function handle(): int
    {
        $this->info('Pruning soft deleted models...');

        $path = app_path().'/Models';

        $models = $this->getModels($path);

        foreach ($models as $model) {
            /** @var string|\DateInterval $pruneAfter */
            $pruneAfter = $model::$pruneAfter;
            if (\is_string($pruneAfter)) {
                $pruneAfter = new \DateInterval($pruneAfter);
            }
            $this->info("Pruning {$model}...");
            $query = $model::query()
                ->onlyTrashed()
                ->where('deleted_at', '<', now()->sub($pruneAfter));

            $bar = $this->output->createProgressBar($query->count());
            $bar->start();
            $query->eachById(function ($model) use ($bar) {
                $model->forceDelete();
                $bar->advance();
            });
            $bar->finish();
            $this->line("\n");
        }

        $this->info('Done!');

        return 0;
    }

    protected function getModels($path)
    {
        $out = [];
        $results = scandir($path);
        foreach ($results as $result) {
            if ($result === '.' || $result === '..') {
                continue;
            }
            $filename = $path.'/'.$result;
            if (is_dir($filename)) {
                $out = array_merge($out, $this->getModels($filename));
            } else {
                $className = str_replace('/', '\\', str_replace(app_path(), 'App', mb_substr($filename, 0, -4)));
                $uses = class_uses_recursive($className);
                if (\in_array(SoftDeletes::class, $uses, true) && property_exists($className, 'pruneAfter')) {
                    $out[] = $className;
                }
            }
        }

        return $out;
    }
}
