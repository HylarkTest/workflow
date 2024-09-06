<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Nuwave\Lighthouse\GlobalId\GlobalId;
use Nuwave\Lighthouse\GlobalId\GlobalIdException;

class SwitchBaseController extends Controller
{
    public function __invoke(string $baseId, Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (is_numeric($baseId)) {
            $id = (int) $baseId;
        } else {
            try {
                [$type, $id] = resolve(GlobalId::class)->decode($baseId);
            } catch (GlobalIdException) {
                abort(404);
            }

            abort_if($type !== 'Base', 404);
        }

        $baseToSwitch = $user->bases()->findOrFail($id);

        $user->setActiveBase($baseToSwitch);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
