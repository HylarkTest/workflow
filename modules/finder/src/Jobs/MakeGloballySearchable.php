<?php

declare(strict_types=1);

namespace Finder\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class MakeGloballySearchable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model&\Finder\GloballySearchable>  $models
     */
    public function __construct(public Collection $models) {}

    /**
     * Handle the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->models->isEmpty()) {
            return;
        }

        $this->models->first()?->globallySearchableUsing()->update($this->models);
    }
}
