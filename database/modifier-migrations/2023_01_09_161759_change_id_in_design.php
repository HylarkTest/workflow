<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use LaravelUtils\Database\KnowsConnection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use KnowsConnection;

    protected array $replacements = [
        'EVENTS_NEW_EVENT' => 'EVENTS_FEATURE_NEW',
        'TODOS_NEW_TODO' => 'TODOS_FEATURE_NEW',
        'NOTES_NEW_NOTE' => 'NOTES_FEATURE_NEW',
        'ATTACHMENTS_NEW_ATTACHMENT' => 'ATTACHMENTS_FEATURE_NEW',
        'PINBOARD_NEW_PIN' => 'PINBOARD_FEATURE_NEW',
        'LINKS_NEW_LINK' => 'LINKS_FEATURE_NEW',
        'EVENTS_NEW_FEATURE' => 'EVENTS_FEATURE_NEW',
        'TODOS_NEW_FEATURE' => 'TODOS_FEATURE_NEW',
        'NOTES_NEW_FEATURE' => 'NOTES_FEATURE_NEW',
        'ATTACHMENTS_NEW_FEATURE' => 'ATTACHMENTS_FEATURE_NEW',
        'PINBOARD_NEW_FEATURE' => 'PINBOARD_FEATURE_NEW',
        'LINKS_NEW_FEATURE' => 'LINKS_FEATURE_NEW',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('pages')
            ->eachById(function (stdClass $row) {
                if (! $row->design) {
                    return;
                }
                $newDesign = str_replace(array_keys($this->replacements), array_values($this->replacements), $row->design);
                if ($newDesign && $newDesign !== $row->design) {
                    DB::table('pages')
                        ->where('id', $row->id)
                        ->update(['design' => $newDesign]);
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
