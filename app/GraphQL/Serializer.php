<?php

declare(strict_types=1);

namespace App\GraphQL;

use App\Models\Base;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Nuwave\Lighthouse\Support\Contracts\SerializesContext;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;

class Serializer implements SerializesContext
{
    use SerializesAndRestoresModelIdentifiers;

    /**
     * @param  \App\GraphQL\AppContext  $context
     */
    public function serialize(GraphQLContext $context): string
    {
        return serialize([
            'user' => $this->getSerializedPropertyValue($context->user()),
            'base' => $this->getSerializedPropertyValue($context->base()),
        ]);
    }

    public function unserialize(string $context): GraphQLContext
    {
        $contextArr = unserialize($context);
        $rawUser = $contextArr['user'];
        /** @var \App\Models\User $user */
        $user = $this->getRestoredPropertyValue($rawUser);
        $rawBase = $contextArr['base'] ?? null;

        try {
            /** @var \App\Models\Base $base */
            $base = $rawBase ? $this->getRestoredPropertyValue($rawBase) : $user->firstPersonalBase();
        } catch (ModelNotFoundException $e) {
            // $rawBase will be null in the scenario where the base is deleted
            $base = (new Base)->forceFill(['id' => $rawBase->id]);
        }

        return new AppContext($user, $base);
    }
}
