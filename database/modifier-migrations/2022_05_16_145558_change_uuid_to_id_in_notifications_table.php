<?php

declare(strict_types=1);

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
        if (Schema::getColumnType('notifications', 'id') === 'bigint') {
            return;
        }
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['base_id']);
            $table->dropForeign(['global_notification_id']);
        });
        Schema::rename('notifications', 'old_notifications');

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Base::class)->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->morphs('notifiable');
            $table->foreignIdFor(\App\Models\GlobalNotification::class)->nullable()->constrained()->cascadeOnDelete();
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->unique(['notifiable_type', 'notifiable_id', 'global_notification_id'], 'notifications_unique');
        });

        $columns = ['base_id', 'type', 'notifiable_id', 'notifiable_type', 'global_notification_id', 'data', 'read_at', 'created_at', 'updated_at'];
        \Illuminate\Support\Facades\DB::table('notifications')
            ->insertUsing(
                $columns,
                \Illuminate\Support\Facades\DB::table('old_notifications')
                    ->select($columns)
            );

        Schema::drop('old_notifications');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
