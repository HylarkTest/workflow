<?php

declare(strict_types=1);

namespace App\Console\Commands\TextMigration;

use App\Models\Note;
use MarkupUtils\MarkupType;
use Illuminate\Console\Command;

class MigrateNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notes:format:migrate {CURRENT_FORMAT_MARKUP}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Migrate existing notes to a new markup format. Before running this command, ensure that you have updated `notes.format` with your new markup specification. Additionally, set the `CURRENT_FORMAT_MARKUP` to the existing format, such as 'DELTA'.";

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $currentFormatMarkupInDB = MarkupType::from($this->argument('CURRENT_FORMAT_MARKUP'));
        $currentFormatString = 'MarkupType::'.$currentFormatMarkupInDB->name;

        $newMarkup = config('notes.format');

        $configPath = config_path('notes.php');
        $configContent = file_get_contents($configPath);

        if ($configContent === false) {
            $this->error('Failed to read the configuration file.');

            return;
        }

        if ($this->isMarkupUnchanged($configContent, $currentFormatString)) {
            $this->info("Nothing to change. New format is already set to {$currentFormatString}");

            return;
        }

        // Process notes in chunks to handle large datasets
        $this->info('Migrating text notes in chunks...');
        Note::withTrashed()->with('base')->each(function ($note) use ($newMarkup, $currentFormatMarkupInDB) {
            $this->migrateTextNote($note, $newMarkup, $currentFormatMarkupInDB);
        });

        $this->info('All text notes have been migrated.');
    }

    /**
     * Check if the markup is unchanged.
     */
    protected function isMarkupUnchanged(string $configContent, string $currentFormatString): bool
    {
        return str_contains($configContent, $currentFormatString);
    }

    /**
     * Migrate a single note to the new markup.
     */
    protected function migrateTextNote(Note $note, MarkupType $newMarkup, MarkupType $oldMarkup): void
    {
        $this->info("Note ID {$note->id} migration started...");

        $note->timestamps = false;

        config(['notes.format' => $oldMarkup]);

        if ($this->shouldSkipMigration($note, $newMarkup)) {
            $this->info("Note ID {$note->id} is already in the new format. Skipping...");

            return;
        }

        $tiptap = $note->text->convertTo($newMarkup);
        config(['notes.format' => $newMarkup]);
        $note->text = $tiptap;

        // Initializing tenant for ES indexing
        tenancy()->initialize($note->base);
        $note->save();
        $note->timestamps = true;

        $this->info("Note ID {$note->id} migrated successfully.");
    }

    /**
     * Determine if the migration should be skipped.
     */
    protected function shouldSkipMigration(Note $note, MarkupType $newMarkup): bool
    {
        $pattern = match ($newMarkup->name) {
            'TIPTAP' => '{"type":"doc"',
            'DELTA' => '{"ops":',
            default => null,
        };

        return $pattern !== null && str_contains($note->getRawOriginal('text'), $pattern);
    }
}
