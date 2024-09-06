<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use \CitusLaravel\CitusHelpers;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $migration = '2019_07_11_000001_create_tag_groups_table';
        if (
            DB::table('migrations')->where('migration', $migration)->exists()
        ) {
            DB::table('migrations')->where('migration', $migration)->delete();

            return;
        }
        $this->createTableForDistribution('marker_groups', 'base_id', function (Blueprint $table) {
            $table->string('template_refs')->nullable();
            $table->string('name', 255);
            $table->string('type')->default(\Markers\Core\MarkerType::TAG->name);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marker_groups');
    }
};
