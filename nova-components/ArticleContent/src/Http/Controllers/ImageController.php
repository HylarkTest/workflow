<?php

declare(strict_types=1);

namespace Hylark\ArticleContent\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageController
{
    public function upload(Request $request): JsonResponse
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()]);
        }

        $imageFolder = 'support';
        $file = $request->file('image');

        $name = $file->store($imageFolder, 'resources');

        if ($name) {
            return response()->json(['location' => Storage::disk('resources')->url($name)]);
        }

        return response()->json(['error' => 'Failed to move uploaded file.']);
    }

    public function validateRequest(Request $request): \Illuminate\Validation\Validator
    {
        $maxSize = 2048;
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:'.$maxSize,
        ]);

        return $validator;
    }
}
