<?php

declare(strict_types=1);

use App\Core\BaseType;
use App\Core\Groups\Role;
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
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');

        DB::table('bases')->eachById(function (stdClass $row) {
            // Only user IDs at this point
            $userId = $row->owner_id;
            if (
                DB::table('users')->where('id', $userId)->exists()
                && DB::table('base_user')->where(['base_id' => $row->id, 'user_id' => $userId])->doesntExist()
            ) {
                DB::table('base_user')->insert([
                    'base_id' => $row->id,
                    'user_id' => $userId,
                    'role' => Role::OWNER->value,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        });

        Schema::table('bases', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('name');
            $table->string('type')->default(BaseType::PERSONAL->value)->after('id');
            $table->dropMorphs('owner');
        });

        Schema::table('bases', function (Blueprint $table) {
            $table->string('type')->default(null)->change();
        });

        Schema::rename('users_settings', 'user_settings');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('user_settings', 'users_settings');

        Schema::table('bases', function (Blueprint $table) {
            $table->dropColumn('logo');
            $table->dropColumn('type');
            $table->nullableMorphs('owner');
        });

        DB::table('base_user')->eachById(function (stdClass $row) {
            // Only user IDs at this point
            $userId = $row->user_id;
            if (DB::table('users')->where('id', $userId)->exists()) {
                DB::table('bases')->where('id', $row->base_id)->update([
                    'owner_id' => $row->user_id,
                    'owner_type' => 'users',
                ]);
            }
        });
    }
};
