<?php

declare(strict_types=1);

namespace LaravelUtils\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RestoreCascadeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  protectedModel  $parent
     * @param  protectedstring  $relation
     * @return void
     */
    public function __construct(
        protected Model $parent,
        protected string $relation,
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var \Illuminate\Database\Eloquent\Relations\HasMany $relation */
        $relation = $this->parent->{$this->relation}();
        $deletedBy = $relation->getQualifiedForeignKeyName();
        if ($relation instanceof MorphMany) {
            $deletedBy = $relation->getMorphType()."::$deletedBy";
        }
        $relation->withTrashed()
            ->orderBy($relation->getRelated()->getQualifiedKeyName())
            ->where('deleted_by', $deletedBy)
            ->eachById(function (Model $model) {
                $model->restore();
            });
    }
}
