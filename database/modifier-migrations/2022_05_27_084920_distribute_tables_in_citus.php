<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use \CitusLaravel\CitusHelpers;
    use KnowsConnection;

    protected array $distributedTables = [];

    public function __construct()
    {
        $this->distributedTables = config('citus.distributed_tables');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First we drop all the foreign key constraints, so we can add the
        // distributed column.
        $this->dropForeign('mappings', ['space_id']);
        $this->dropForeign('todo_lists', ['space_id']);
        $this->dropForeign('pages', ['space_id']);
        $this->dropForeign('items', ['mapping_id']);
        $this->dropForeign('collaborators', ['item_id']);
        $this->dropForeign('pages', ['mapping_id']);
        $this->dropForeign('tags', ['tag_group_id']);
        $this->dropForeign('relationships', ['related_id']);
        $this->dropForeign('relationships', ['foreign_id']);
        $this->dropForeign('taggables', ['tag_id']);
        $this->dropForeign('category_items', ['category_id']);
        $this->dropForeign('todos', ['todo_list_id']);
        $this->dropForeign('todos', ['parent_id']);

        // Then we need to remove unique indexes that don't include the
        // distributed column.
        $this->dropUnique('relationships', ['relation_id', 'related_id', 'foreign_id']);
        $this->dropUnique('notifications', 'notifications_unique');

        // Next we need to replace the primary constraint with one that includes
        // the distributed column.
        foreach ($this->distributedTables as $table) {
            if ($this->usingPostgresConnection()) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropPrimary();
                    $blueprint->dropForeign(['base_id']);
                    $blueprint->primary(['base_id', 'id']);
                });
            } else {
                DB::unprepared("ALTER TABLE $table MODIFY COLUMN id BIGINT UNSIGNED");
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropForeign(['base_id']);
                    $blueprint->primary('id');
                });
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->primary(['base_id', 'id']);
                });
                DB::unprepared("ALTER TABLE $table MODIFY COLUMN id BIGINT UNSIGNED AUTO_INCREMENT");
            }
        }

        // Finally, we create the distributed tables
        if ($this->citusInstalled()) {
            $this->createDistributedTableFromEmpty('bases', 'id');
            $this->createReferenceTable('global_notifications');
            foreach ($this->distributedTables as $table) {
                $this->createDistributedTableFromEmpty($table, 'base_id');
            }
        }

        foreach ($this->distributedTables as $table) {
            // $this->createForeign($table, 'base_id', 'bases');
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->index(['base_id', 'id']);
            });
        }

        // Then we need to add the foreign keys back to reference the new
        // joined primary keys.
        $this->createForeignWithBase('mappings', 'space_id', 'spaces');
        $this->createForeignWithBase('todo_lists', 'space_id', 'spaces');
        $this->createForeignWithBase('pages', 'space_id', 'spaces');
        $this->createForeignWithBase('items', 'mapping_id', 'mappings');
        $this->createForeignWithBase('pages', 'mapping_id', 'mappings');
        $this->createForeignWithBase('tags', 'tag_group_id', 'tag_groups');
        $this->createForeignWithBase('relationships', 'related_id', 'items');
        $this->createForeignWithBase('relationships', 'foreign_id', 'items');
        $this->createForeignWithBase('taggables', 'tag_id', 'tags');
        $this->createForeignWithBase('category_items', 'category_id', 'categories');
        $this->createForeignWithBase('todos', 'todo_list_id', 'todo_lists');
        $this->createForeignWithBase('todos', 'parent_id', 'todos');
        // Foreign keys need to be re-added
        $this->createForeign('notifications', 'global_notification_id', 'global_notifications');

        // Add back the unique index
        $this->createUnique('relationships', ['base_id', 'relation_id', 'related_id', 'foreign_id'], 'relationships_unique');
        $this->createUnique('notifications', ['base_id', 'notifiable_type', 'notifiable_id', 'global_notification_id'], 'notifications_unique');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropUnique('relationships', 'relationships_unique');
        $this->dropUnique('notifications', 'notifications_unique');

        $this->dropForeign('mappings', ['base_id', 'space_id']);
        $this->dropForeign('todo_lists', ['base_id', 'space_id']);
        $this->dropForeign('pages', ['base_id', 'space_id']);
        $this->dropForeign('items', ['base_id', 'mapping_id']);
        $this->dropForeign('pages', ['base_id', 'mapping_id']);
        $this->dropForeign('tags', ['base_id', 'tag_group_id']);
        $this->dropForeign('relationships', ['base_id', 'related_id']);
        $this->dropForeign('relationships', ['base_id', 'foreign_id']);
        $this->dropForeign('taggables', ['base_id', 'tag_id']);
        $this->dropForeign('category_items', ['base_id', 'category_id']);
        $this->dropForeign('todos', ['base_id', 'todo_list_id']);
        $this->dropForeign('todos', ['base_id', 'parent_id']);
        $this->dropForeign('notifications', ['global_notification_id']);

        foreach ($this->distributedTables as $table) {
            $this->dropForeign($table, ['base_id']);
        }

        if ($this->citusInstalled()) {
            foreach ($this->distributedTables as $table) {
                $this->undistributeTable($table);
            }
            $this->undistributeTable('global_notifications');
            $this->undistributeTable('bases');
        }

        foreach ($this->distributedTables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropPrimary($blueprint->getTable().'_pkey1');
                $blueprint->foreign('base_id')->references('id')->on('bases')->cascadeOnDelete();
                $blueprint->primary('id');
            });
        }

        $this->createUnique('relationships', ['relation_id', 'related_id', 'foreign_id']);
        $this->createUnique('notifications', ['notifiable_type', 'notifiable_id', 'global_notification_id']);

        $this->createForeign('mappings', 'space_id', 'spaces');
        $this->createForeign('todo_lists', 'space_id', 'spaces');
        $this->createForeign('pages', 'space_id', 'spaces');
        $this->createForeign('items', 'mapping_id', 'mappings');
        $this->createForeign('collaborators', 'item_id', 'items');
        $this->createForeign('pages', 'mapping_id', 'mappings');
        $this->createForeign('tags', 'tag_group_id', 'tags');
        $this->createForeign('relationships', 'related_id', 'items');
        $this->createForeign('relationships', 'foreign_id', 'items');
        $this->createForeign('taggables', 'tag_id', 'tags');
        $this->createForeign('category_items', 'category_id', 'categories');
        $this->createForeign('todos', 'todo_list_id', 'todo_lists');
        $this->createForeign('todos', 'parent_id', 'todos');
        $this->createForeign('notifications', 'global_notification_id', 'global_notifications');
    }

    protected function createForeignWithBase(string $table, string $column, string $on): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($column, $on) {
            $blueprint->foreign(['base_id', $column])->references(['base_id', 'id'])->on($on)->cascadeOnDelete();
        });
    }

    protected function createForeign(string $table, string $column, string $on): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($column, $on) {
            $blueprint->foreign($column)->references('id')->on($on)->cascadeOnDelete();
        });
    }

    protected function dropForeign(string $table, string|array $index): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($index) {
            $blueprint->dropForeign($index);
        });
    }

    protected function dropUnique(string $table, string|array $index): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($index) {
            $blueprint->dropUnique($index);
        });
    }

    protected function createUnique(string $table, array $columns, ?string $name = null): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
            $blueprint->unique($columns, $name);
        });
    }
};
