<?php

declare(strict_types=1);

namespace AccountIntegrations\Core;

use Google\Client;
use Illuminate\Support\Carbon;
use Google\Service\Calendar\Event;
use AccountIntegrations\Models\IntegrationAccount;
use AccountIntegrations\Exceptions\InvalidGrantException;
use AccountIntegrations\Exceptions\ResourceNotFoundException;

abstract class GoogleRepository
{
    protected Client $client;

    public function __construct(protected IntegrationAccount $account)
    {
        $this->client = $this->account->getGoogleClient();
    }

    /**
     * @template T
     *
     * @param  \Closure(): T  $requestClosure
     * @return T
     *
     * @throws \AccountIntegrations\Exceptions\InvalidGrantException
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function makeRequest(\Closure $requestClosure, ?string $resourceClass = null, string|int|null $resourceId = null): mixed
    {
        try {
            return $requestClosure();
        } catch (\Exception $e) {
            if (method_exists($e, 'getCode')) {
                $code = $e->getCode();

                if (in_array($code, [400, 401], true)) {
                    throw new InvalidGrantException($this->account);
                }

                if (in_array($code, [404, 410], true)) {
                    if (is_string($resourceClass) && class_exists($resourceClass)) {
                        $exception = new ResourceNotFoundException;
                        $exception->setIntegration($this->account, $resourceClass, (string) $resourceId);
                        throw $exception;
                    } else {
                        throw new \InvalidArgumentException('Resource class must be a valid class string for error codes 404 and 410');
                    }
                }
            }

            throw $e;
        }
    }

    protected function checkEventCancelled(Event $event, string $eventId): void
    {
        if ($event->getStatus() === 'cancelled') {
            $exception = new ResourceNotFoundException;
            $exception->setIntegration($this->account, Event::class, $eventId);
            throw $exception;
        }
    }

    /**
     * If the client refreshed the token then we should save the new token.
     */
    public function __destruct()
    {
        $token = $this->client->getAccessToken();

        if ($token['access_token'] !== $this->account->token) {
            $this->account->token = $token['access_token'];
            if (isset($token['refresh_token'])) {
                $this->account->refresh_token = $token['refresh_token'];
            }
            /** @var int $expiresAt */
            $expiresAt = $token['created'] + $token['expires_in'];
            $this->account->expires_at = Carbon::createFromTimestamp($expiresAt);

            $this->account->save();
        }
    }
}
