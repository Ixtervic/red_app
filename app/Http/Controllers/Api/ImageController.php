<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function destroy($id): JsonResponse
    {
        $image = Image::find($id);

        if (!$image) {
            return response()->json([
                'message' => 'Imagen no encontrada',
            ], 404);
        }

        Storage::disk('public')->delete($image->url);
        $image->delete();

        return response()->json(null, 204);
    }
}
