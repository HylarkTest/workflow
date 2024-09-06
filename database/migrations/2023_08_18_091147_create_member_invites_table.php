<?php

declare(strict_types=1);

use App\Models\Base;
use App\Models\User;
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
        Schema::create('member_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Base::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'inviter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'invitee_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->string('role');
            $table->string('token');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_invites');
    }
};
