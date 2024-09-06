<?php

declare(strict_types=1);

namespace LaravelUtils\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SoftDeleteCascadeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $childClass
     * @param  protectedint  $parentId
     * @param  protectedstring  $foreignColumn
     * @return void
     */
    public function __construct(
        protected int $parentId,
        protected string $childClass,
        protected string $foreignColumn,
        protected ?string $morphColumn = null
    ) {
        $this->onQueue(config('app.delete_cascade_queue'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $advancedDelete = method_exists($this->childClass, 'deleteBy');
        $this->childClass::query()->where($this->foreignColumn, $this->parentId)
            ->orderBy((new $this->childClass)->getQualifiedKeyName())
            ->eachById(function (Model $child) use ($advancedDelete) {
                if ($advancedDelete) {
                    $child->deleteBy($this->foreignColumn, $this->morphColumn);
                } else {
                    $child->delete();
                }
            });
    }
}
