<?php

declare(strict_types=1);

namespace LighthouseHelpers\Exceptions;

use LighthouseHelpers\Utils;
use Illuminate\Container\Container;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Illuminate\Database\Eloquent\ModelNotFoundException<TModel>
 */
class GlobalIdNotFoundException extends ModelNotFoundException
{
    public function setGlobalId(string $globalId, ?GlobalId $service = null): void
    {
        $service = $service ?: Container::getInstance()->make(GlobalId::class);
        try {
            [$type, $key] = $service->decode($globalId);
            /** @var class-string<TModel> $modelClass */
            $modelClass = Utils::namespaceModelClass($type);

            if ($modelClass) {
                $this->setModel($modelClass, $key);
            }
        } catch (\Exception $e) {
        }
    }
}
