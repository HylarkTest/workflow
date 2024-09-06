<?php

declare(strict_types=1);

namespace LighthouseHelpers\Exceptions;

use Illuminate\Support\Arr;
use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;
use LighthouseHelpers\Concerns\HasGlobalId;
use Illuminate\Database\Eloquent\ModelNotFoundException as Exception;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Illuminate\Database\Eloquent\ModelNotFoundException<TModel>
 */
class ModelNotFoundException extends Exception implements ClientAware, ProvidesExtensions
{
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'missing';
    }

    /**
     * @param  class-string<TModel>  $model
     * @param  array<int|string>  $ids
     * @return $this<TModel>
     */
    public function setModel($model, $ids = []): self
    {
        $this->model = $model;
        $this->ids = Arr::wrap($ids);

        $this->message = 'No results for the requested node(s)';

        if (\count($this->ids) > 0 && \in_array(HasGlobalId::class, class_uses_recursive($model), true)) {
            /** @phpstan-ignore-next-line Confirmed in previous line */
            $this->message .= ' ['.collect($ids)->map(fn ($id) => $model::convertToGlobalId($id))
                ->implode(', ').']';
        }
        $this->message .= '.';

        return $this;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\ModelNotFoundException<TModel>  $exception
     * @return self<TModel>
     */
    public static function fromLaravel(Exception $exception): self
    {
        /** @var self<TModel> $error */
        $error = (new self(
            $exception->getMessage(),
            $exception->getCode(),
            $exception
        ))
            ->setModel($exception->getModel(), $exception->getIds());

        return $error;
    }

    public function getExtensions(): ?array
    {
        return ['category' => 'missing'];
    }
}
