<?php

declare(strict_types=1);

namespace AccountIntegrations\Core;

use Closure;
use Exception;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Entity;
use Microsoft\Graph\Http\GraphRequest;
use Microsoft\Graph\Http\GraphResponse;
use GuzzleHttp\Promise\PromiseInterface;
use Microsoft\Graph\Http\GraphCollectionRequest;
use AccountIntegrations\Models\IntegrationAccount;
use AccountIntegrations\Exceptions\ResourceNotFoundException;

class MicrosoftGraphGateway
{
    protected Graph $graph;

    public function __construct(protected IntegrationAccount $account)
    {
        $this->graph = new Graph;

        $this->graph->setBaseUrl('https://graph.microsoft.com');
    }

    /**
     * @template TEntity of \Microsoft\Graph\Model\Entity
     *
     * @param  class-string<TEntity>|null  $returnType
     */
    public function getCollectionAsync(string $url, ?string $returnType = null): PromiseInterface
    {
        $request = $this->createCollectionRequest($url, $returnType);

        /** @var \GuzzleHttp\Promise\PromiseInterface $promise */
        $promise = $request->executeAsync();

        return $promise->then(function ($response) use ($request) {
            $responseObject = $request->processPageCallReturn($response);

            if (! \is_array($responseObject)) {
                throw new Exception('Invalid response from Microsoft Graph: '.$response->getRawBody());
            }

            return $responseObject;
        });
    }

    /**
     * @template TEntity of \Microsoft\Graph\Model\Entity
     *
     * @param  class-string<TEntity>|null  $returnType
     * @param  class-string  $resourceClass
     * @return array<TEntity>|array
     */
    public function getCollection(string $url, string $resourceClass, string $id, ?string $returnType = null): array
    {
        return $this->handleWaitPromise(
            $this->getCollectionAsync($url, $returnType),
            $resourceClass,
            $id
        );
    }

    /**
     * @template TEntity of \Microsoft\Graph\Model\Entity
     *
     * @param  class-string<TEntity>|null  $returnType
     *
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function getItemAsync(string $url, ?string $returnType = null): PromiseInterface
    {
        return $this->createRequest('GET', $url, [], $returnType)->executeAsync();
    }

    /**
     * @template TEntity of \Microsoft\Graph\Model\Entity
     *
     * @param  class-string<TEntity>|null  $returnType
     * @param  class-string  $resourceClass
     * @return array|TEntity
     */
    public function getItem(string $url, string $resourceClass, string $id, ?string $returnType = null)
    {
        return $this->handleWaitPromise(
            $this->getItemAsync($url, $returnType),
            $resourceClass,
            $id
        );
    }

    /**
     * @template TEntity of \Microsoft\Graph\Model\Entity
     *
     * @param  array|TEntity  $body
     * @param  class-string  $resourceClass
     * @param  class-string<TEntity>|null  $returnType
     * @return TEntity
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function createItem(string $url, $body, string $resourceClass, string $id, ?string $returnType = null)
    {
        return $this->handleExecuteRequest(
            $this->createRequest('POST', $url, $body),
            $resourceClass,
            $id
        );
    }

    /**
     * @param  class-string  $resourceClass
     *
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function sendRequest(string $url, string $resourceClass, string $id, array $body = []): GraphResponse
    {
        return $this->handleExecuteRequest(
            $this->createRequest('POST', $url, $body),
            $resourceClass,
            $id
        );
    }

    /**
     * @template TEntity of \Microsoft\Graph\Model\Entity
     *
     * @param  array|TEntity  $body
     * @param  class-string  $resourceClass
     * @param  class-string<TEntity>|null  $returnType
     * @return TEntity
     *
     * @throws \Microsoft\Graph\Exception\GraphException
     * @throws \Exception
     */
    public function updateItem(string $url, $body, string $resourceClass, string $id, ?string $returnType = null)
    {
        return $this->handleExecuteRequest(
            $this->createRequest('PATCH', $url, $body),
            $resourceClass,
            $id
        );
    }

    /**
     * @param  class-string  $resourceClass
     *
     * @throws \Exception
     */
    public function deleteItem(string $url, string $resourceClass, string $id): bool
    {
        $this->handleExecuteRequest(
            $this->createRequest('DELETE', $url),
            $resourceClass,
            $id
        );

        return true;
    }

    protected function refreshTokenIfNecessary(): void
    {
        $token = $this->account->refreshToken();
        $this->graph->setAccessToken($token);
    }

    /**
     * @template TEntity of \Microsoft\Graph\Model\Entity
     *
     * @param  class-string<TEntity>|null  $type
     */
    public function createCollectionRequest(string $url, ?string $type = null): GraphCollectionRequest
    {
        /** @var \Microsoft\Graph\Http\GraphCollectionRequest $request */
        $request = $this->createRequest('GET', $url, null, $type, true);

        return $request;
    }

    /**
     * @template TEntity of \Microsoft\Graph\Model\Entity
     *
     * @param  HttpMethod  $method
     * @param  array|TEntity|null  $body
     * @param  class-string<TEntity>|null  $type
     *
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    protected function createRequest(
        string $method,
        string $url,
        null|array|Entity $body = null,
        ?string $type = null,
        bool $isCollectionRequest = false
    ): GraphRequest {
        $this->refreshTokenIfNecessary();

        $request = $isCollectionRequest
            ? $this->graph->createCollectionRequest($method, $url)
            : $this->graph->createRequest($method, $url);

        $request->addHeaders([
            'Prefer' => 'IdType="ImmutableId"',
        ]);

        if ($body) {
            $request->attachBody($body);
            if (! $type && $body instanceof Entity) {
                $type = $body::class;
            }
        }

        if ($type) {
            $request->setReturnType($type);
        }

        if ($request instanceof GraphCollectionRequest) {
            $request->setPageCallInfo();
        }

        return $request;
    }

    /**
     * @param  class-string  $resourceClass
     *
     * @throws \Exception
     */
    protected function tryAndCatchMissing(string $resourceClass, string $id, Closure $cb): mixed
    {
        try {
            return $cb();
        } catch (Exception $e) {
            if (preg_match('/resulted in a `40\d|not contain a Task/', $e->getMessage()) || (method_exists($e, 'getCode') && in_array($e->getCode(), [404]))) {
                throw (new ResourceNotFoundException)->setIntegration($this->account, $resourceClass, $id);
            }
            throw $e;
        }
    }

    /**
     * @param  class-string  $resourceClass
     *
     * @throws \Exception
     */
    protected function handleExecuteRequest(GraphRequest $request, string $resourceClass, string $id): mixed
    {
        return $this->tryAndCatchMissing($resourceClass, $id, function () use (&$request) {
            return $request->execute();
        });
    }

    /**
     * @param  class-string  $resourceClass
     *
     * @throws \Exception
     */
    public function handleWaitPromise(PromiseInterface $promise, string $resourceClass, string $id): mixed
    {
        return $this->tryAndCatchMissing($resourceClass, $id, function () use ($promise) {
            return $promise->wait();
        });
    }
}
