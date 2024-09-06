<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Mappings\Core\Mappings\MappingType;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use \CitusLaravel\CitusHelpers;
    use KnowsConnection;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createTableForDistribution('mappings', 'base_id', function (Blueprint $table) {
            $table->string('template_refs')->nullable();
            $table->unique(['base_id', 'api_name']);
            $table->unique(['base_id', 'api_singular_name']);

            $table->unsignedBigInteger('space_id')->nullable();
            if ($this->usingSqliteConnection()) {
                $table->foreign('space_id')->references('id')->on('spaces')->cascadeOnDelete();
            } else {
                $table->foreign(['base_id', 'space_id'])->references(['base_id', 'id'])->on('spaces')->cascadeOnDelete();
            }

            $table->string('name', 50);
            $table->string('singular_name', 50);
            $table->string('api_name', 50);
            $table->string('api_singular_name', 50);
            $table->text('description')->nullable();
            $table->string('type')->default(MappingType::ITEM->value);

            $this->nullableJsonOrStringColumn($table, 'fields');
            $this->nullableJsonOrStringColumn($table, 'relationships');
            $this->nullableJsonOrStringColumn($table, 'sections');

            $this->nullableJsonOrStringColumn($table, 'features');
            $this->nullableJsonOrStringColumn($table, 'marker_groups');

            $table->timestamps();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();

            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('mappings');
    }
};
