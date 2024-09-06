<?php

declare(strict_types=1);

use CitusLaravel\CitusHelpers;
use Illuminate\Support\Facades\DB;
use App\Models\DatabaseNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use CitusHelpers;
    use KnowsConnection;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($this->citusInstalled()) {
            try {
                $this->undistributeTable('global_notifications');
                $this->undistributeTable('notifications');
            } catch (\Exception) {
                // Assume already undistributed
            }
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropUnique('notifications_unique');
        });

        if ($this->usingPostgresConnection()) {
            $db = DB::connection();
            $prefix = $db->getTablePrefix();
            $table = $db->getTablePrefix().'notifications';
            $db->statement("ALTER TABLE {$table} DROP CONSTRAINT {$table}_pkey");
        }

        Schema::table('notifications', function (Blueprint $table) {
            if ($this->usingPostgresConnection()) {
                $table->primary('id');
            } else {
                $table->dropIndex(['base_id', 'id']);
            }
            $table->dropColumn('base_id');
            $table->foreign('global_notification_id')->references('id')->on('global_notifications')->cascadeOnDelete();
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
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['global_notification_id']);
            if ($this->usingPostgresConnection()) {
                $table->dropPrimary();
            }
            $table->dropUnique('notifications_unique');
            $table->foreignId('base_id')->after('id')->nullable();
        });

        DatabaseNotification::with('notifiable.bases')
            ->each(function (DatabaseNotification $notification) {
                $notification->update(['base_id' => $notification->notifiable->firstPersonalBase()->id]);
            });

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('base_id')->nullable(false)->change();
            if ($this->usingPostgresConnection()) {
                $table->primary(['base_id', 'id']);
                $table->unsignedBigInteger('id')->default("nextval('notifications_id_seq'::regclass)")->change();
            } else {
                $table->index(['base_id', 'id']);
            }
            $table->unique(['base_id', 'notifiable_type', 'notifiable_id', 'global_notification_id'], 'notifications_unique');
        });

        if ($this->citusInstalled()) {
            try {
                $this->createReferenceTable('global_notifications');
                $this->createDistributedTable('notifications', 'base_id');
            } catch (\Exception) {
                // Assume already distributed
            }
        }
    }
};
