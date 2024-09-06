<?php

declare(strict_types=1);

use App\Models\Base;
use CitusLaravel\CitusHelpers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use CitusHelpers;
    use KnowsConnection;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createTableForDistribution('assignee_groups', 'base_id', function (Blueprint $table) {
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        if (! $this->usingSqliteConnection()) {
            Base::query()
                ->each(fn (Base $base) => $base->run(fn () => $base->createDefaultAssigneeGroups()));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assignee_groups');
    }
};
