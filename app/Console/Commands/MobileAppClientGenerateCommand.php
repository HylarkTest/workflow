<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Passport\Client;
use Illuminate\Console\Command;
use Laravel\Passport\Console\ClientCommand;

class MobileAppClientGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:app-client:generate
            {--force : Replace the existing client without asking}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a public OAuth client for use by the mobile app.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force')) {
            $existingClient = Client::query()->firstWhere('name', 'Mobile App');
            if ($existingClient) {
                if ($this->confirm('A client for the mobile app already exists. Do you want to replace it?') === false) {
                    return 0;
                }

                $existingClient->delete();
            }
        }
        $this->call(ClientCommand::class, [
            '--name' => 'Mobile App',
            '--no-interaction' => true,
            '--public' => true,
            '--redirect_uri' => 'http://localhost/callback',
            '--provider' => 'users',
            '--user_id' => null,
        ]);
        if (! $this->option('quiet')) {
            $this->ask('Please copy the client ID to the .env file as MOBILE_APP_CLIENT_ID. Press enter to continue.');
        }

        return 0;
    }
}
