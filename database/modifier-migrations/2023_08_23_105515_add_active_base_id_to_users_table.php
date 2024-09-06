<?php

declare(strict_types=1);

use App\Models\Base;
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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignIdFor(Base::class, 'active_base_id')->after('id')->nullable()->constrained('bases')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if ($this->usingMySqlConnection()) {
                $table->dropForeign(['active_base_id']);
            }
            $table->dropColumn('active_base_id');
        });
    }
};
