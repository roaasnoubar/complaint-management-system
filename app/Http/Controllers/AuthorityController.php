<?php

namespace App\Http\Controllers;

use App\Models\Authority;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthorityController extends Controller
{
    public function index(): JsonResponse
    {
        $authorities = Authority::with('departments')
                                ->where('is_active', true)
                                ->get();

        return response()->json([
            'success' => true,
            'data'    => $authorities,
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $authority = Authority::with('departments')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $authority,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin only.',
            ], 403);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:authorities,name',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $authority = Authority::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active'   => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Authority created successfully.',
            'data'    => $authority,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin only.',
            ], 403);
        }

        $authority = Authority::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'nullable|string|max:255|unique:authorities,name,' . $id,
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $authority->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Authority updated successfully.',
            'data'    => $authority,
        ], 200);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin only.',
            ], 403);
        }

        $authority = Authority::findOrFail($id);
        $authority->delete();

        return response()->json([
            'success' => true,
            'message' => 'Authority deleted successfully.',
        ], 200);
    }
}