<?php

declare(strict_types=1);

namespace App\Console\Commands\TextMigration;

use App\Models\Action;
use MarkupUtils\Delta;
use Illuminate\Console\Command;

class MigrateActionsToTipTap extends Command
{
    protected $signature = 'actions:notes:migrate';

    protected $description = 'Migrate existing Delta text actions to TipTap markup format.';

    public function handle(): void
    {
        $this->info('Migrating action payloads in chunks...');

        Action::chunkById(1000, function ($actions) {
            foreach ($actions as $action) {
                $this->info('Action ID: '.$action->id);
                $action->timestamps = false;
                $action->updatePayloadChanges(function (array $payload) {
                    return $this->convertActionText($payload);
                });
            }
        });
        $this->info('Migrating action completed');
    }

    private function convertActionText(array $payload): array
    {
        if (isset($payload['text'])) {
            $textArr = json_decode($payload['text'], true);

            if (isset($textArr['ops'])) {
                if (count($textArr['ops'])) {
                    $payload['text'] = (new Delta($textArr))->convertToTipTap()->__toString();
                } else {
                    $payload['text'] = (new Delta(['ops' => [['insert' => '\n']]]))->convertToTipTap()->__toString();
                }
            }

        }

        return $payload;
    }
}
