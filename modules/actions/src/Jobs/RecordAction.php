<?php

declare(strict_types=1);

namespace Actions\Jobs;

use Actions\Models\Action;
use Actions\Core\ActionType;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

class RecordAction
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected Model $model,
        protected ?Model $performer,
        protected ActionType $type,
        protected ?array $payload,
        protected Carbon $time,
    ) {}

    /**
     * Handle the job.
     *
     * @return void
     */
    public function handle()
    {
        Action::createAction(
            $this->model,
            $this->performer,
            $this->type,
            $this->payload,
            $this->time
        );
    }
}
