<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(): UserCollection
    {
        $users = User::addSelect(['thumbnail_url' => Image::select('url')
            ->whereColumn('imageable_id', 'users.id')
            ->where('imageable_type', User::class)
            ->oldest()
            ->limit(1)
        ])->get();

        return new UserCollection($users);
    }

    public function show($id): JsonResponse
    {
        $user = User::with('images')->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Contacto no encontrado',
            ], 404);
        }

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'images' => 'nullable|array|max:4',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('contacts', 'public');
                $user->images()->create(['url' => $path]);
            }
            $user->load('images');
        }

        return response()->json([
            'data' => new UserResource($user),
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $user = User::withCount('images')->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Contacto no encontrado',
            ], 404);
        }

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'phone' => 'sometimes|required|string|max:20',
        ];

        if ($request->hasFile('images')) {
            $currentCount = $user->images_count;
            $incomingCount = count($request->file('images'));

            if (($currentCount + $incomingCount) > 4) {
                return response()->json([
                    'message' => "Límite de imágenes excedido. El contacto ya tiene {$currentCount} imagen(es) y solo puede tener hasta 4.",
                ], 422);
            }

            $rules['images'] = 'nullable|array|max:4';
            $rules['images.*'] = 'image|mimes:jpg,jpeg,png,webp|max:2048';
        }

        $validated = $request->validate($rules);

        $user->fill($validated);
        $user->save();

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('contacts', 'public');
                $user->images()->create(['url' => $path]);
            }
            $user->load('images');
        }

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $user = User::with('images')->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Contacto no encontrado',
            ], 404);
        }

        foreach ($user->images as $image) {
            Storage::disk('public')->delete($image->url);
            $image->delete();
        }

        $user->delete();

        return response()->json(null, 204);
    }

    public function addImages(Request $request, $id): JsonResponse
    {
        $user = User::withCount('images')->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Contacto no encontrado',
            ], 404);
        }

        $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $currentCount = $user->images_count;
        $incomingCount = count($request->file('images'));

        if (($currentCount + $incomingCount) > 4) {
            return response()->json([
                'message' => "Límite de imágenes excedido. El contacto ya tiene {$currentCount} imagen(es) y solo puede agregar " . (4 - $currentCount) . " más.",
            ], 422);
        }

        foreach ($request->file('images') as $image) {
            $path = $image->store('contacts', 'public');
            $user->images()->create(['url' => $path]);
        }

        $user->load('images');

        return response()->json([
            'data' => new UserResource($user),
        ], 201);
    }
}
