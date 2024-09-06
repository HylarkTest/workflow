<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
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
        foreach (['pages', 'mappings'] as $table) {
            DB::table($table)
                ->orderBy('id')
                ->each(function ($row) use ($table) {
                    $templateRefs = $row->template_refs ? implode(',', json_decode($row->template_refs, true)) : null;
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update([
                            'template_refs' => $templateRefs,
                        ]);
                });
        }
        Schema::table('marker_groups', function (Blueprint $table) {
            $table->string('template_refs')->nullable()->after('id');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->string('template_refs')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (['pages', 'mappings'] as $table) {
            DB::table($table)
                ->orderBy('id')
                ->each(function ($row) use ($table) {
                    $templateRefs = $row->template_refs ? json_encode(explode(',', $row->template_refs)) : null;
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update([
                            'template_refs' => $templateRefs,
                        ]);
                });
        }
        Schema::table('marker_groups', function (Blueprint $table) {
            $table->dropColumn('template_refs');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('template_refs');
        });
    }
};
