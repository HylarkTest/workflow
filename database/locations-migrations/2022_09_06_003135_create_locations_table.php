<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function getConnection()
    {
        return config('mappings.locations.database');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->unsignedInteger('geoname_id')->unique();
            $table->unsignedTinyInteger('level')->index();
            $table->unsignedInteger('geoname_parent_id')->nullable()->index();
            $table->unsignedInteger('country_geoname_id')->nullable()->index();
            $table->string('country_code', 3)->nullable();
            $table->double('longitude')->nullable();
            $table->double('latitude')->nullable();
            $table->unsignedInteger('population')->nullable();
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations');
    }
};
