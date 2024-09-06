<?php

declare(strict_types=1);

use App\Core\Groups\Role;
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
        Schema::create('base_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Base::class);
            $table->foreignIdFor(\App\Models\User::class);
            $table->string('role')->default(Role::MEMBER->value);
            $table->string('name')->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('use_account_avatar')->default(false);
            $this->nullableJsonOrStringColumn($table, 'settings');
            $table->timestamps();
            $table->unique(['base_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('base_user');
    }
};
