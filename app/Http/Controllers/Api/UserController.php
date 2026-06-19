<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('image')->get();

        return response()->json([
            'data' => UserResource::collection($users),
        ]);
    }

    public function show($id): JsonResponse
    {
        $user = User::with('image')->find($id);

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
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $user->image()->create(['url' => $path]);
            $user->load('image');
        }

        return response()->json([
            'data' => new UserResource($user),
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Contacto no encontrado',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|nullable|email|unique:users,email,' . $id,
            'phone' => 'sometimes|required|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $user->fill($validated);
        $user->save();

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image->url);
                $user->image()->delete();
            }

            $path = $request->file('image')->store('images', 'public');
            $user->image()->create(['url' => $path]);
            $user->load('image');
        }

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Contacto no encontrado',
            ], 404);
        }

        if ($user->image) {
            Storage::disk('public')->delete($user->image->url);
            $user->image()->delete();
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
