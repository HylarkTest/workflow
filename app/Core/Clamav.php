<?php

declare(strict_types=1);

namespace App\Core;

use Illuminate\Support\Arr;
use Illuminate\Config\Repository;
use App\Exceptions\ClamavException;
use Socket\Raw\Factory as SocketFactory;
use Xenolope\Quahog\Client as QuahogClient;

class Clamav
{
    protected Repository $config;

    protected QuahogClient $scanner;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function startSession(): QuahogClient
    {
        $socket = $this->getClamavSocket();
        $this->scanner = $this->createQuahogScannerClient($socket);

        $this->scanner->startSession();

        return $this->scanner;
    }

    public function endSession(): void
    {
        if (isset($this->scanner)) {
            $this->scanner->endSession();
        }
    }

    /**
     * @param  \SplFileInfo|\SplFileInfo[]  $files
     * @return true
     *
     * @throws \App\Exceptions\ClamavException
     */
    public function check($files): bool
    {
        if (! $this->shouldValidate()) {
            return true;
        }

        try {
            $this->startSession();

            $files = Arr::wrap($files);

            foreach ($files as $file) {
                $filePath = $file->getPathname();
                if (! is_readable($filePath)) {
                    throw ClamavException::forNonReadableFile($filePath);
                }
                $resource = fopen($filePath, 'r');
                if ($resource !== false) {
                    $result = $this->scanner->scanResourceStream($resource);
                } else {
                    throw ClamavException::forNonReadableFile($filePath);
                }

                if ($result->isError()) {
                    throw ClamavException::forScanResult($result);
                }

                if (! $result->isOk()) {
                    throw ClamavException::forMalware($result);
                }
            }
        } catch (ClamavException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw ClamavException::forClientException($exception);
        } finally {
            $this->endSession();
        }

        return true;
    }

    protected function shouldValidate(): bool
    {
        return (bool) $this->config->get('clamav.enabled') === true;
    }

    protected function getClamavSocket(): string
    {
        $preferredSocket = $this->config->get('clamav.preferred_socket');

        if ($preferredSocket === 'unix_socket') {
            $unixSocket = $this->config->get('clamav.unix_socket');
            if (file_exists($unixSocket)) {
                return 'unix://'.$unixSocket;
            }
        }

        return $this->config->get('clamav.tcp_socket');
    }

    protected function createQuahogScannerClient(string $socket): QuahogClient
    {
        $client = (new SocketFactory)->createClient($socket);

        return new QuahogClient(
            $client,
            $this->config->get('clamav.socket_read_timeout'),
            \PHP_NORMAL_READ
        );
    }
}
