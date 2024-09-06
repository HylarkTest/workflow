<?php

declare(strict_types=1);

use App\Models\Base;
use App\Core\BaseType;
use App\Models\Action;
use Actions\Core\ActionType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('actions', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable();
        });

        Schema::table('actions', function (Blueprint $table) {
            if ($this->usingSqliteConnection()) {
                $table->foreign('parent_id')->references('id')->on('actions')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'parent_id'])->references(['base_id', 'id'])->on('actions')->cascadeOnDelete();
            }
        });

        Base::with('owners')->eachById(function (Base $base) {
            /** @var \App\Models\User $owner */
            $owner = $base->owners->first();
            if (! $owner) {
                return;
            }
            $createAction = $base->createAction;
            if (! $createAction) {
                $createAction = Action::createAction($base, $owner, ActionType::CREATE(), ['name' => $base->name], $base->created_at);
            }
            $accountCreateAction = $base->baseActions()->where('subject_type', 'users')->first();
            if (! $accountCreateAction && $base->type === BaseType::PERSONAL) {
                $base->run(fn () => Action::createAction(
                    $owner,
                    $owner,
                    ActionType::CREATE(),
                    null,
                    $owner->created_at
                ));
            }

            if ($owner->finishedRegistration()) {
                $childActions = $base->baseActions()
                    ->where('id', '!=', $createAction->id)
                    ->when($accountCreateAction)
                    ->where('id', '!=', $accountCreateAction?->id)
                    ->where('created_at', '<', $owner->finished_registration_at->addSeconds(5))
                    ->update(['parent_id' => $createAction->id]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('actions', function (Blueprint $table) {
            if ($this->usingSqliteConnection()) {
                $table->dropForeign(['parent_id']);
            } else {
                $table->dropForeign(['base_id', 'parent_id']);
            }
        });

        Schema::table('actions', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
    }
};
