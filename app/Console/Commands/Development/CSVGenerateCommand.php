<?php

declare(strict_types=1);

namespace App\Console\Commands\Development;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\WithFaker;

class CSVGenerateCommand extends Command
{
    use WithFaker;

    public const RANDOM_USER_URL = 'https://randomuser.me/api';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:generate
                {--empty-cells=0 : Specify the frequency (percentage) of empty cells in the CSV}
                {--nonsense-cells=0 : Specify the frequency (percentage) of nonsense cells in the CSV}
                {--always-filled=* : Specify the columns that should always be filled}
                {--rows=1000 : Specify the number of rows in the CSV}
                {--columns=* : Specify the columns in the CSV}
                {--headers=* : Specify the headers in the CSV}
                {--filename= : Specify the filename of the CSV}
    ';

    protected array $availableColumns = [
        'Full name' => 'name',
        'Preferred name' => 'firstName',
        'Birthday' => 'date',
        'Address' => 'address',
        'Phone number' => 'phoneNumber',
        'Email' => 'email',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a CSV file with random data.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->setUpFaker();

        $filename = $this->option('filename') ?? 'random_'.now()->format('Y-m-d_H-i-s').'.csv';
        $emptyCells = $this->option('empty-cells');
        $nonSenseCells = $this->option('nonsense-cells');
        $alwaysFilled = $this->option('always-filled');
        $rows = $this->option('rows');
        /** @var null|string[] $columns */
        $columns = $this->option('columns');

        if (! $columns) {
            $options = array_keys($this->availableColumns);
            /** @var string[] $columns */
            $columns = $this->choice(
                'What columns should be in the CSV?',
                $options,
                null,
                null,
                true,
            );
        }
        if (empty($columns)) {
            $this->error('At least one column must be specified.');

            return 1;
        }
        foreach ($columns as $column) {
            if (! array_key_exists($column, $this->availableColumns)) {
                $this->error("The column '$column' is not available.");

                return 1;
            }
        }

        /** @var null|string[] $headers */
        $headers = $this->option('headers');
        if (! $headers) {
            $headers = $columns;
        }
        if (count($headers) !== count($columns)) {
            $this->error('The number of headers must match the number of columns.');

            return 1;
        }
        $columnInfo = array_map(fn ($column, $header) => [
            'header' => $header,
            'column' => $columns,
            'method' => $this->availableColumns[$column],
        ], $columns, $headers);

        $bar = $this->output->createProgressBar((int) $rows);

        $bar->start();

        $path = storage_path("app/fake/$filename");
        $file = fopen($path, 'wb');
        if (! $file) {
            $this->error("Could not open file at $path");

            return 1;
        }

        fputcsv($file, $headers);

        for ($i = 0; $i < $rows; $i++) {
            $row = [];
            foreach ($columnInfo as $info) {
                $header = $info['header'];
                $data = $this->faker->{$info['method']}();
                if (! in_array($header, $alwaysFilled, true)) {
                    if (random_int(0, 100) < $emptyCells) {
                        $data = '';
                    } elseif (random_int(0, 100) < $nonSenseCells) {
                        $data = Str::random(10);
                    }
                }
                $row[] = $data;
            }
            fputcsv($file, $row);
            $bar->advance();
        }

        fclose($file);

        $this->line("\n");
        $this->info("CSV file generated at $path");

        return 0;
    }
}
