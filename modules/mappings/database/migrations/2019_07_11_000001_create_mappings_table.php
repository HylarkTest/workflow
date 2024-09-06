<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Mappings\Core\Mappings\MappingType;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mappings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('singular_name', 50);
            $table->string('api_name', 50);
            $table->string('api_singular_name', 50);
            $table->text('description')->nullable();
            $table->string('type')->default(MappingType::ITEM->value);
            $this->nullableJsonOrStringColumn($table, 'fields');
            $this->nullableJsonOrStringColumn($table, 'relationships');
            $this->nullableJsonOrStringColumn($table, 'sections');
            $table->timestamps();

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
