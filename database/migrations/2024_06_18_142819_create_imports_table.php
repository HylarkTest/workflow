<?php

declare(strict_types=1);

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
        $this->createTableForDistribution('imports', 'base_id', function (Blueprint $table) {
            $table->string('name');
            $table->string('filename');
            $table->string('file_id')->nullable();
            $table->string('status');
            $table->unsignedBigInteger('processed_rows')->default(0);
            $table->unsignedBigInteger('total_rows')->nullable();
            $table->unsignedBigInteger('member_id');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('reverted_at')->nullable();
            $table->timestamp('revert_finished_at')->nullable();
            $table->timestamps();

            if ($this->usingSqliteConnection()) {
                $table->index('member_id');
            } else {
                $table->index(['base_id', 'member_id']);
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
        Schema::dropIfExists('imports');
    }
};
