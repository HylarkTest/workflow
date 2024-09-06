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
        try {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropUnique('new_notifications_pkey');
                $table->dropIndex('new_notifications_notifiable_type_notifiable_id_index');
                $table->dropUnique('new_notifications_notifiable_type_notifiable_id_global_notifica');
            });

            Schema::table('notifications', function (Blueprint $table) {
                $table->primary('id');
                $table->index(['notifiable_type', 'notifiable_id']);
                $table->unique(['notifiable_type', 'notifiable_id', 'global_notification_id'], 'notifications_unique');
            });
        } catch (Exception) {
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
