<?php

declare(strict_types=1);

namespace App\Console\Commands\Once;

use Illuminate\Console\Command;
use App\Models\Support\SupportArticle;

class UpdateLinksInResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-links-in-resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        SupportArticle::withoutGlobalScopes()->each(function (SupportArticle $article) {
            $pattern = '/<a\s+(.*?)href="http:\/\/app\.hylark\.test(.*?)"(.*?)>/i';

            // Replacement pattern
            $replacement = '<a $1href="https://app.hylark.com"$3 class="article-link" data-ref="internal-link">';

            // Perform the replacement
            $article->content = (string) preg_replace($pattern, $replacement, (string) $article->content);

            // Save the updated article
            SupportArticle::withoutEvents(fn () => $article->save());
        });

        return 0;
    }
}
