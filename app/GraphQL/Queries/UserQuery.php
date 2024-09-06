<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\GraphQL\AppContext;
use LighthouseHelpers\Core\Mutation;
use GraphQL\Type\Definition\ResolveInfo;

class UserQuery extends Mutation
{
    /**
     * @param  null  $rootValue
     * @param  array{input: array{name?: string|null, email?: string|null, avatar?: \Illuminate\Http\UploadedFile|null}}  $args
     */
    public function update($rootValue, array $args, AppContext $context, ResolveInfo $resolveInfo): array
    {
        $user = $context->user();

        $data = $args['input'];

        if ($name = $data['name'] ?? null) {
            $user->name = $name;
        }

        if ($email = $data['email'] ?? null) {
            $user->email = $email;
        }

        if (\array_key_exists('avatar', $data)) {
            $user->firstPersonalBase()->run(fn () => $user->updateImage($data['avatar'], 'avatar', 'avatars'));
        }

        $user->save();

        return $this->mutationResponse(200, 'User was updated successfully', [
            'user' => $user,
        ]);
    }
}
