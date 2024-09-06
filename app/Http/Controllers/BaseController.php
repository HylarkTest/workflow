<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Base;
use App\Core\BaseType;
use App\Models\Action;
use App\Core\Groups\Role;
use App\Core\BaseRepository;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BaseController extends Controller
{
    public function store(BaseRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        if ($user->bases->count() >= Base::MAX_BASES) {
            throw ValidationException::withMessages(['name' => ['You have reached the maximum number of bases.']]);
        }

        return DB::transaction(function () use ($request, $user) {
            $baseData = $request->baseData();

            $baseType = BaseType::COLLABORATIVE;
            $baseName = $baseData['name'] ?? null;

            $base = Base::create([
                'name' => $baseName,
                'type' => $baseType,
            ]);

            $user->bases()->attach($base, ['role' => Role::OWNER]);

            tenancy()->initialize($base);
            Action::withParent($base->createAction, function () use ($base) {
                $base->spaces()->create(['name' => 'Main']);
            });
            /** @var \Illuminate\Http\UploadedFile|null $image */
            $image = $baseData['image'];

            if ($image) {
                $base->updateImage($image, 'image', 'base-images');
                $base->save();
            }
            (new BaseRepository)->bootstrapBase($base, $baseData);

            $user->setActiveBase($base);

            return response()->json([
                'data' => [
                    'id' => $base->global_id,
                ],
            ]);
        }, 3);
    }
}
