<?php

declare(strict_types=1);

namespace App\Console\Commands\Development;

use Illuminate\Console\Command;

class TimezonesGenerate extends Command
{
    /**
     * This is a file that contains all the links from legacy timezones to their
     * correct modern key. The tzdb directory is symlinked to the latest
     * information, so it should simply be a case of rerunning this command if
     * there are any changes.
     */
    public const TIMEZONE_LINK_URL = 'ftp://ftp.iana.org/tz/tzdb/backward';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timezones:generate
                {--f|format=plain : Specify the format to export (currently only plaint text and json supported)}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a list of timezones supported by PHP';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $timezones = timezone_identifiers_list();
        $links = [];
        $stream = fopen(self::TIMEZONE_LINK_URL, 'r');
        if (! $stream) {
            $this->error('Could not load data from '.self::TIMEZONE_LINK_URL);

            return 1;
        }
        while (($line = fgets($stream)) !== false) {
            $line = trim($line);
            if (! $line || str_starts_with($line, '#')) {
                continue;
            }

            /** @phpstan-ignore-next-line preg_split always returns a string */
            [
                $action,
                $target,
                $link,
            ] = preg_split('/\t+/', $line);
            $links[$link] = $target;
        }

        $format = $this->option('format');

        if ($format === 'plain') {
            $this->table(['Timezones'], array_map(fn (string $timezone) => [$timezone], $timezones));
            $this->table(['Link', 'Target'], array_map(fn (string $link) => [$link, $links[$link]], array_keys($links)));
        } elseif ($format === 'json') {
            $output = json_encode(compact('timezones', 'links'), \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
            $this->info($output);
        }

        return 0;
    }
}
