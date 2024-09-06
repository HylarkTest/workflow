<?php

declare(strict_types=1);

use App\Models\Action;
use Actions\Core\ActionType;
use App\Core\ItemActionRecorder;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $recorder = resolve(ItemActionRecorder::class);
        Action::query()->where('subject_type', 'items')
            ->with('subject')
            ->eachById(function (Action $action) use ($recorder) {
                if (! $action->payload) {
                    $action->delete();

                    return;
                }
                /** @var \App\Models\Item $item */
                $item = $action->subject;
                $payload = null;
                if ($action->type->is(ActionType::CREATE())) {
                    $payload = $recorder->buildCreatePayloadFromAttributes($item);
                } elseif ($action->type->is(ActionType::UPDATE())) {
                    $payload = $recorder->buildUpdatePayloadFromAttributes($item);
                }
                if ($payload) {
                    if (isset($payload['data']) && ! $payload['data']) {
                        $action->delete();
                    } elseif (isset($payload['changes'], $payload['original']) && ! ($payload['changes']['data'] ?? false) && ! ($payload['original']['data'] ?? false)) {
                        $action->delete();
                    } else {
                        $action->payload = $payload;
                        $action->save();
                    }
                } else {
                    $action->delete();
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
