<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Laravel\Forge\Forge;
use Illuminate\Console\Command;
use Laravel\Forge\Resources\Daemon;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Concerns\CallsCommands;

/**
 * A command to restart octane with no downtime by running a new daemon and swapping the nginx config.
 * https://gist.github.com/pascalbaljet/ccc2c3c5b8cda91fa20b0ced588067e4
 */
class SwapOctaneServerCommand extends Command
{
    use CallsCommands;

    public const BLUE = 'blue';

    public const GREEN = 'green';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'octane:swap-server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a new Octane daemon and swap the port in the nginx config';

    protected Forge $forgeApi;

    protected int $serverId;

    protected int $siteId;

    protected string $site;

    protected int $bluePort;

    protected int $greenPort;

    /**
     * Execute the console command.
     */
    public function handle(Forge $forge): int
    {
        $this->forgeApi = $forge;
        $forge->setApiKey(config('services.forge.api_key'));
        $this->serverId = (int) config('services.forge.server_id');
        $this->siteId = (int) config('services.forge.site_id');
        $this->site = config('services.forge.site');
        $this->bluePort = (int) config('services.forge.blue_port'); // 8001
        $this->greenPort = (int) config('services.forge.green_port'); // 8002

        $nginxPort = $this->getNginxPort();

        $this->info('Current nginx port: '.$nginxPort);

        $blueIsActive = $nginxPort === $this->bluePort;

        $this->start($blueIsActive ? static::GREEN : static::BLUE);
        $this->stop($blueIsActive ? static::BLUE : static::GREEN);

        return 0;
    }

    protected function getNginxPort(): int
    {
        $config = $this->forgeApi->siteNginxFile($this->serverId, $this->siteId);

        preg_match('/(proxy_pass http:\/\/127.0.0.1:)(\d+)\$/', $config, $matches);

        return (int) $matches[2];
    }

    protected function start(string $server): void
    {
        $this->info('Start server: '.$server);

        // make sure there's no dangling daemon
        $this->stop($server);

        $port = $server === static::BLUE ? $this->bluePort : $this->greenPort;

        $this->forgeApi->createDaemon($this->serverId, [
            'command' => $this->getCommandForPort($port),
            'user' => 'forge',
            'directory' => "/home/forge/$this->site/current",   // symbolic link
        ]);

        $this->info('Polling server on port: '.$server);

        retry(
            300,
            fn () => Http::timeout(1)
                ->connectTimeout(1)
                ->get("127.0.0.1:$port"),
            100
        );

        $this->updateNginxConfig($port);
    }

    protected function updateNginxConfig(int $newPort): void
    {
        $this->info('Updating nginx config to port: '.$newPort);

        file_put_contents('/home/forge/octane_port', $newPort);
        file_put_contents('/home/forge/octane_site', $this->site);
        $this->forgeApi->runRecipe(
            config('services.forge.nginx_switch_recipe_id'),
            [
                'servers' => [$this->serverId],
                'notify' => false,
            ]
        );
    }

    protected function stop(string $server): void
    {
        $forge = $this->forgeApi;

        $port = $server === static::BLUE ? $this->bluePort : $this->greenPort;

        /** @var Daemon|null $daemon */
        $daemon = collect($forge->daemons($this->serverId))
            ->firstWhere('command', $this->getCommandForPort($port));

        if ($daemon) {
            $this->info('Wait for pending requests on server: '.$server);

            usleep(config('octane.max_execution_time') * 1000 * 1000);

            // Check if the nginx file has been updated
            $nginxConfig = $forge->siteNginxFile($this->serverId, $this->siteId);
            if (str_contains($nginxConfig, "proxy_pass http://127.0.0.1:$port")) {
                $this->error('Nginx config has not been updated, leave the old process running and exit.');
                exit(1);
            }

            $this->info('Stop server: '.$server);

            rescue(fn () => $forge->deleteDaemon($daemon->serverId, $daemon->id));
        }
    }

    protected function getCommandForPort(int $port): string
    {
        return "bash ./scripts/start-octane-server.sh \"$this->site\" \"$port\"";
    }
}
