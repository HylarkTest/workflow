<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Support\SupportArticle;
use Illuminate\Support\Facades\Storage;

class SupportArticlesCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'support:articles:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check that links and images in support articles are valid';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $imageDisk = Storage::disk('resources');

        $articleLinks = [];
        $imageLinks = [];

        SupportArticle::query()
            ->withoutGlobalScopes()
            ->each(function (SupportArticle $article) use (&$articleLinks, &$imageLinks) {
                $matches = [];
                preg_match_all('/(?<=src=")([^"]+)/', $article->content, $matches);
                $imageLinks = array_merge($imageLinks, $matches[0]);

                $matches = [];
                preg_match_all('/(?<=href=")([^"]+)/', $article->content, $matches);
                $articleLinks[$article->id] = $matches[0];
            }, 10);

        $this->info('Checking unused images...');

        $allImages = $imageDisk->files('support');

        foreach ($allImages as $image) {
            if (! \in_array($imageDisk->url($image), $imageLinks, true)) {
                $this->info('Unused image: '.$imageDisk->url($image));
                $imageDisk->delete($image);
            }
        }

        return 0;
    }
}
