<?php

declare(strict_types=1);

namespace KeyValueStore;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class KeyValueStoreController extends Controller
{
    public function __construct(protected KeyValueStore $store) {}

    public function index(Request $request): JsonResponse
    {
        $scope = $this->getScope($request);

        $keys = $this->store->getKeysForScope($scope);

        return new JsonResponse([
            'data' => $keys,
        ]);
    }

    public function show(string $key, Request $request): JsonResponse
    {
        $scope = $this->getScope($request);

        $value = $this->store->getValue($key, $scope);

        return new JsonResponse([
            'data' => $value,
        ]);
    }

    public function store(string $key, Request $request): JsonResponse
    {
        $request->validate([
            'value' => 'required',
        ]);

        $scope = $this->getScope($request);
        $ttl = $request->query('ttl', null);

        try {
            $this->store->storeValue($key, $scope, $request->input('value'), $ttl ? (int) $ttl : null);
        } catch (ValueTooBigException) {
            abort(422, 'The value is too big');
        } catch (TooManyKeysException) {
            abort(422, 'There are too many keys stored for this user');
        }

        return new JsonResponse([], Response::HTTP_CREATED);
    }

    public function destroy(string $key, Request $request): JsonResponse
    {
        $scope = $this->getScope($request);
        $this->store->deleteValue($key, $scope);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    protected function getScope(Request $request): string
    {
        $user = $request->user();
        abort_if(! $user, 401, 'A user must be logged in to use the store.');

        return (string) $user->getKey();
    }
}
