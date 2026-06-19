<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Image\StoreImageRequest;
use App\Http\Requests\Image\UpdateImageRequest;
use App\Http\Resources\Image\ImageCollection;
use App\Http\Resources\Image\ImageResource;
use App\Models\Image;
use Illuminate\Http\JsonResponse;

class ImageController extends Controller
{
    public function index(): ImageCollection
    {
        $images = Image::with('imageable')->paginate(20);

        return ImageCollection::make($images);
    }

    public function store(StoreImageRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        $validated['imageable_id'] = $user->id;
        $validated['imageable_type'] = $user->getMorphClass();

        $image = Image::create($validated);

        return response()->json([
            'message' => 'Image created successfully.',
            'data' => ImageResource::make($image),
        ], 201);
    }

    public function show(Image $image): JsonResponse
    {
        $image->load('imageable');

        return response()->json([
            'data' => ImageResource::make($image),
        ]);
    }

    public function update(UpdateImageRequest $request, Image $image): JsonResponse
    {
        $validated = $request->validated();

        $image->update($validated);

        return response()->json([
            'message' => 'Image updated successfully.',
            'data' => ImageResource::make($image),
        ]);
    }

    public function destroy(Image $image): JsonResponse
    {
        $image->delete();

        return response()->json(null, 204);
    }
}
