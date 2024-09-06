<?php

declare(strict_types=1);

use App\Models\GlobalNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use \CitusLaravel\CitusHelpers;
    use \LaravelUtils\Database\KnowsConnection;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->morphs('notifiable');
            if ($this->usingSqliteConnection()) {
                $table->foreignIdFor(GlobalNotification::class)->nullable();
            } else {
                $table->foreignIdFor(GlobalNotification::class)->constrained()->cascadeOnDelete()->nullable();
            }
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->unique(['notifiable_type', 'notifiable_id', 'global_notification_id'], 'notifications_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
